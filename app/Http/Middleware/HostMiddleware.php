<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HostMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        if (!$user->isHost()) {
            return response()->json([
                'success' => false,
                'message' => 'Host access required'
            ], 403);
        }

        if (!$user->canHost()) {
            return response()->json([
                'success' => false,
                'message' => 'Account verification required to host properties'
            ], 403);
        }

        return $next($request);
    }
}