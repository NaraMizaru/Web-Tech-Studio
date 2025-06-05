<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\LessonController;
use App\Http\Controllers\API\SetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('courses')->group(function () {
            Route::controller(CourseController::class)->group(function () {
                Route::get('/', 'getAllPublishedCourse');
                Route::post('/', 'addCourse');
                Route::put('/{id}', 'editCourse');
                Route::delete('/{id}', 'deleteCourse');
            });

            Route::controller(SetController::class)->group(function () {
                Route::post('/{course}/sets', 'addSet');
                Route::delete('/{course}/sets/{id}', 'deleteSet');
            });
        });

        Route::prefix('lessons')->group(function () {
            Route::controller(LessonController::class)->group(function () {
                Route::post('/', 'addLesson');
                Route::put('/{id}/complete', 'completeLesson');
                Route::delete('/{id}', 'deleteLesson');
                Route::post('/{id}/contents/{contentId}/check', 'checkAnswer');
            });
        });
    });
});
