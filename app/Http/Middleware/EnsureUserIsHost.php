<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsHost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Please log in to access host features.');
        }

        if (!$user->isHost()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Host access required'
                ], 403);
            }
            
            return redirect()->route('home')->with('error', 'Host access required. Please upgrade your account to become a host.');
        }

        if (!$user->canHost()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account verification required to host properties'
                ], 403);
            }
            
            return redirect()->route('profile.verify-identity')->with('error', 'Please verify your identity to access host features.');
        }

        return $next($request);
    }
}
