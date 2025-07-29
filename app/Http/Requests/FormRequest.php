<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Concerns\AuthorizationChecker;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

/**
 * Wrapper of Laravel's FormRequest to include our own authorization checking.
 */
class FormRequest extends LaravelFormRequest
{
    use AuthorizationChecker;
}
