<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompletedLesson;
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

    public function getUserProgress(Request $request)
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course')
            ->get();

        $completedLessons = CompletedLesson::where('user_id', $user->id)->get();

        $groupedLessons = $completedLessons->groupBy(function ($lesson) {
            return $lesson->lesson->set->course_id ?? null;
        });

        $progress = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;

            $lessons = $groupedLessons[$course->id] ?? collect();

            $progress[] = [
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'slug' => $course->slug,
                    'description' => $course->description,
                    'is_published' => $course->is_published,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at
                ],
                'completed_lessons' => $lessons->map(function ($lesson) {
                    return [
                        'id' => $lesson->lesson->id,
                        'name' => $lesson->lesson->name,
                        'order' => $lesson->lesson->order
                    ];
                })->values()
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User progress retrieved successfully',
            'data' => [
                'progress' => $progress
            ]
        ], 200);
    }
}
