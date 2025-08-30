<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'Unauthenticated',
                    'data'    => []
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'status'  => 401,
                'message' => 'Token expired. Please login again.',
                'data'    => []
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'status'  => 401,
                'message' => 'Invalid token. Please login again.',
                'data'    => []
            ], 401);
        } catch (TokenBlacklistedException $e) {
            return response()->json([
                'success' => false,
                'status'  => 401,
                'message' => 'Token has been blacklisted. Please login again.',
                'data'    => []
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 401,
                'message' => 'Unauthorized',
                'data'    => []
            ], 401);
        }

        return $next($request);
    }
}
