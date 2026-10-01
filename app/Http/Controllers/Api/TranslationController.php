<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    public function __invoke(string $lang): JsonResponse
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $lang) !== 1) {
            return response()->json(['error' => 'Language not found'], 404);
        }

        $directory = realpath(resource_path('lang'));
        $path = realpath(resource_path('lang'.DIRECTORY_SEPARATOR.$lang.'.json'));

        if (
            $directory === false
            || $path === false
            || ! str_starts_with($path, $directory.DIRECTORY_SEPARATOR)
        ) {
            return response()->json(['error' => 'Language not found'], 404);
        }

        return response()->json(json_decode(File::get($path), true));
    }
}
