<?php

namespace App\Exceptions;

use App\Helpers\FileHelper;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, string>
     */
    protected $dontReport = [
        MissingAppKeyException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle MissingAppKeyException - redirect to installation wizard
        if ($exception instanceof MissingAppKeyException) {
            // Skip if already on install route
            if (! str_starts_with($request->path(), 'install')) {
                return redirect('/install');
            }
        }

        // Handle PostTooLargeException with user-friendly message
        if ($exception instanceof PostTooLargeException) {
            $effectiveMaxFormatted = FileHelper::getMaxUploadSizeFormatted();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('The uploaded file is too large. Maximum allowed size is :size. Please upload a smaller file or contact your administrator to increase the upload limit.', ['size' => $effectiveMaxFormatted]),
                ], 413);
            }

            session()->flash('error', __('The uploaded file is too large. Maximum allowed size is :size. Please upload a smaller file or contact your administrator to increase the upload limit.', ['size' => $effectiveMaxFormatted]));

            return redirect()->back();
        }

        return parent::render($request, $exception);
    }
}
