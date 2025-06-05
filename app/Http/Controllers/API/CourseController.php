<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function addCourse(Request $request)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable',
            'slug' => 'required|unique:courses,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }

        $course = Course::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->slug),
            'is_published' => $request->is_published ?? 0
        ]);

        $reponse = [
            'status' => 'success',
            'message' => 'Course successfully added',
            'data' => [
                'name' => $course->name,
                'description' => $course->description,
                'slug' => $course->slug,
                'updated_at' => $course->updated_at,
                'created_at' => $course->created_at,
                'id' => $course->id
            ],
        ];

        return response($reponse, 201);
    }

    public function editCourse(Request $request, $id)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                "status" => "not_found",
                "message" => "Resource not found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable',
            'is_published' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field(s) in request',
                'errors' => $validator->errors()
            ], 400);
        }


        $course->name = $request->name;
        $course->description = $request->description;
        $course->is_published = $request->is_published;
        $course->save();

        $reponse = [
            'status' => 'success',
            'message' => 'Course successfully updated',
            'data' => [
                'name' => $course->name,
                'slug' => $course->slug,
                'description' => $course->description,
                'is_published' => $course->is_published,
                'updated_at' => $course->updated_at,
                'created_at' => $course->created_at,
                'id' => $course->id
            ],
        ];

        return response($reponse, 200);
    }

    public function deleteCourse(Request $request, $id)
    {
        $admin = Administrator::where('username', $request->user()->username)->first();

        if (!$admin) {
            return response()->json([
                "status" => "insufficient_permissions",
                "message" => "Access forbidden"
            ], 403);
        }

        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                "status" => "not_found",
                "message" => "Resource not found"
            ], 404);
        }

        $course->delete();
        return response()->json([
            "status" => "success",
            "message" => "Course successfully deleted"
        ], 200);
    }

    public function getAllPublishedCourse()
    {
        $course = Course::where('is_published', 1)->get();

        return response()->json([
            'status' => 'success',
            'message' => "Courses retrieved successfully",
            'data' => [
                'courses' => $course
            ]
        ]);
    }

    // public function getDetailCourse($slug)
    // {
    //     $course = Course::where('slug', $slug)->first();


    // }
}
