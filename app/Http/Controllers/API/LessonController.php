<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\CompletedLesson;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Option;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    public function addLesson(Request $request)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'set_id' => 'required|exists:sets,id',
            'contents' => 'required|array|min:1',
            'contents.*.type' => 'required|in:learn,quiz',
            'contents.*.content' => 'required|string',
            'contents.*.options' => 'required_if:contents.*.type,quiz|array|min:1',
            'contents.*.options.*.option_text' => 'required|string',
            'contents.*.options.*.is_correct' => 'required|boolean',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $maxOrderLes = Lesson::where('set_id', $request->set_id)->max('order');
        $nextOrderLes = is_null($maxOrderLes) ? 0 : $maxOrderLes + 1;

        $lesson = new Lesson();
        $lesson->name = $request->name;
        $lesson->set_id = $request->set_id;
        $lesson->order = $nextOrderLes;
        $lesson->save();

        foreach ($request->contents as $content) {
            $lessonContent = new LessonContent();

            $maxOrderContent = LessonContent::where('lesson_id', $lesson->id)->max('order');
            $nextOrderContent = is_null($maxOrderContent) ? 0 : $maxOrderContent + 1;

            $lessonContent->lesson_id = $lesson->id;
            $lessonContent->type = $content['type'];
            $lessonContent->content = $content['content'];
            $lessonContent->order = $nextOrderContent;
            $lessonContent->save();

            if ($content['type'] === 'quiz') {
                foreach ($content['options'] as $opt) {
                    $option = new Option();
                    $option->lesson_content_id = $lessonContent->id;
                    $option->option_text = $opt['option_text'];
                    $option->is_correct = $opt['is_correct'];
                    $option->save();
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson successfully added',
            'data' => [
                'name' => $lesson->name,
                'order' => $lesson->order,
                'id' => $lesson->id
            ],
        ], 201);
    }

    public function deleteLesson(Request $request, $id)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'not_found',
                'message' => "Resource not found"
            ], 404);
        }

        $lesson->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Lesson successfully deleted"
        ], 200);
    }

    public function checkAnswer(Request $request, $id, $contentId)
    {
        $user = User::where('username', $request->user()->username)->first();
        if (!$user) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'option_id' => 'required|exists:options,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $lesson = Lesson::find($id);

        if (!$lesson) {
            return response()->json([
                'status' => 'not_found',
                'message' => "Resource not found"
            ], 404);
        }

        $content = LessonContent::where('id', $contentId)->first();

        if ($content->type === 'learn') {
            return response()->json([
                "status" => "error",
                "message" => "Only for quiz content"
            ], 400);
        }

        $option = Option::where('lesson_content_id', $contentId)->where('id', $request->option_id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Check answer success',
            'data' => [
                'question' => $content->content,
                'user_anser' => $option->option_text,
                'is_correct' => $option->is_correct ? true : false
            ],
        ]);
    }

    public function completeLesson(Request $request, $id)
    {
        $user = User::where('username', $request->user()->username)->first();
        if (!$user) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $lesson = Lesson::find($id);

        if (!$lesson) {
            return response()->json([
                'status' => 'not_found',
                'message' => "Resource not found"
            ], 404);
        }

        $completed = new CompletedLesson();
        $completed->user_id = $request->user()->id;
        $completed->lesson_id = $lesson->id;
        $completed->save();

        return response()->json([
            'status' => 'success',
            'message' => "Lesson successfully completed"
        ]);
    }
}
