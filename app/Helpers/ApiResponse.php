<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ApiResponse
{
    /**
     * Success Response
     */
    public static function success($data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], $status);
    }
    public static function error(string $message = 'Error', int $status = 500, $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status'  => $status,
            'message' => $message,
            'errors'  => $errors
        ], $status);
    }
    public static function validation(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status'  => 422,
            'message' => 'Validation failed',
            'errors'  => $e->errors()
        ], 422);
    }
    public static function exception(\Throwable $e, int $status = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status'  => $status,
            'message' => $e->getMessage(),
            'trace'   => config('app.debug') ? $e->getTrace() : [] // hide trace in production
        ], $status);
    }
}
