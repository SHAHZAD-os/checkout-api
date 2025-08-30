<?php

namespace App\Services;

use App\Models\UserSession;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthService
{
    public function login($request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $user = Auth::user();

        UserSession::create([
            'user_id' => $user->id,
            'login_time' => now(),
        ]);

        return ApiResponse::success(['token' => $token], 'Login successful');
    }

    public function logout()
    {
        $user = JWTAuth::parseToken()->authenticate();

        UserSession::where('user_id', $user->id)
            ->whereNull('logout_time')
            ->update(['logout_time' => now()]);

        JWTAuth::invalidate(JWTAuth::getToken());

        return ApiResponse::success([], 'Logout successful');
    }
    public function getLoginDuration()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $session = UserSession::where('user_id', $user->id)
            ->whereNull('logout_time')
            ->latest('login_time')
            ->first();

        if (!$session) {
            return ApiResponse::error('No active session found', 404);
        }

        $duration = now()->diff($session->login_time);

        return ApiResponse::success([
            'login_duration' => $this->formatDuration($duration)
        ], 'Login duration calculated');
    }
    public function getonlineDuration()
{
    try {
        $user = JWTAuth::parseToken()->authenticate();

        $sessions = UserSession::where('user_id', $user->id)->get();

        if ($sessions->isEmpty()) {
            return ApiResponse::error('No sessions found', 404);
        }

        $totalSeconds = 0;

        foreach ($sessions as $s) {
            if ($s->logout_time) {
                $totalSeconds += $s->login_time->diffInSeconds($s->logout_time);
            } else {
                $totalSeconds += $s->login_time->diffInSeconds(now());
            }
        }

        return ApiResponse::success([
            'online_duration' => $this->formatDurationFromSeconds($totalSeconds)
        ], 'Online duration calculated');

    } catch (\Throwable $e) {
        return ApiResponse::exception($e);
    }
}


    private function formatDuration($diff)
    {
        return sprintf(
            "%d hours %d minutes %d seconds",
            $diff->h + ($diff->d * 24),
            $diff->i,
            $diff->s
        );
    }

    private function formatDurationFromSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return "$hours hours $minutes minutes $secs seconds";
    }
}
