<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();

            // \Log::info('JWT Payload:', $payload->toArray());
            if ($user && $payload->get('role') === 'ADMIN') {
                return $next($request);
            }

            return response()->json(['message' => 'Unauthorized', 'role' => $payload->get('role')], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized, invalid token or not found'], 403);
        }
    }
}

