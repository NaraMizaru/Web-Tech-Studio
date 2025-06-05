<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function registerCourse(Request $request, $slug)
    {
        $course = Course::where('slug', $slug)->first();

        if (!$course) {
            return response()->json([
                'status' => 'not_found',
                'message' => "Resource not found"
            ], 404);
        }

        $enrollmentsExist = Enrollment::where('user_id', $request->user()->id)->first();
        if ($enrollmentsExist) {
            return response()->json([
                "status" => "error",
                "message" => "The user is already registered for this course"
            ], 400);
        }

        $enrollments = new Enrollment();
        $enrollments->user_id = $request->user()->id;
        $enrollments->course_id = $course->id;
        $enrollments->save();

        return response()->json([
            "status" => "success",
            "message" => "User registered successful"
        ], 201);
    }
}
