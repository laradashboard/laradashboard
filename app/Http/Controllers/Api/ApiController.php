<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Concerns\HasActionLogTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiController extends Controller
{
    use HasActionLogTrait;

    /**
     * Return a success response.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error response.
     */
    protected function errorResponse(
        string $message = 'Error',
        int $code = 400,
        mixed $data = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a resource response.
     */
    protected function resourceResponse(
        JsonResource|ResourceCollection $resource,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return $this->successResponse($resource, $message, $code);
    }

    /**
     * Return a validation error response.
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}
