<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Course;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetController extends Controller
{
    public function addSet(Request $request, Course $course)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $maxOrder = Set::where('course_id', $course->id)->max('order');
        $nextOrder = is_null($maxOrder) ? 0 : $maxOrder + 1;

        $set = new Set();
        $set->name = $request->name;
        $set->course_id = $course->id;
        $set->order = $nextOrder;
        $set->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Set successfully added',
            'data' => [
                'name' => $set->name,
                'order' => $set->order,
                'id' => $set->id
            ],
        ], 201);
    }

    public function deleteSet(Request $request, Course $course, $id)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $set = Set::where('id', $id)->where('course_id', $course->id)->where('name', $request->name)->first();
        $set->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Set successfully deleted"
        ], 200);
    }
}
