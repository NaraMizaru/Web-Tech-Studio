<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as MiddlewareAuthenticate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends MiddlewareAuthenticate
{
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'status' => 'invalid_token',
            'message' => 'Invalid or expired token'
        ], 401));
    }
}
