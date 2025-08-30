<?php

     namespace App\Http\Middleware;

     use Closure;
     use Illuminate\Http\Request;
     use Tymon\JWTAuth\Facades\JWTAuth;

     class UpdateLastActivity
     {
         public function handle(Request $request, Closure $next)
         {
             try {
                 if ($user = JWTAuth::parseToken()->authenticate()) {
                     $request->attributes->set('request_time', now());
                     $user->update(['last_activity_at' => now()]);
                 }
             } catch (\Exception) {
                 // Skip if no valid token
             }
             return $next($request);
         }
     }