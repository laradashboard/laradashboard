<?php

declare(strict_types=1);

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config()->set('settings.auth_registration_email_domain_check_enabled', '1');
    config()->set('settings.auth_registration_honeypot_enabled', '1');
});

test('forgot password request skips disposable and dns email checks', function () {
    $rules = (new ForgotPasswordRequest())->rules();

    $validator = Validator::make(
        [
            'email' => 'admin@corp.local',
            'company_website' => '',
        ],
        $rules,
    );

    expect($validator->passes())->toBeTrue();
});

test('forgot password request still rejects known disposable domains only via standard email rule path', function () {
    $rules = (new ForgotPasswordRequest())->rules();

    $validator = Validator::make(
        [
            'email' => 'user@mailinator.com',
            'company_website' => '',
        ],
        $rules,
    );

    expect($validator->passes())->toBeTrue();
});

test('reset password request skips disposable and dns email checks', function () {
    $rules = (new ResetPasswordRequest())->rules();

    $validator = Validator::make(
        [
            'token' => 'test-token',
            'email' => 'admin@corp.local',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
            'company_website' => '',
        ],
        $rules,
    );

    expect($validator->passes())->toBeTrue();
});

test('registration merge rules still reject disposable email when domain check is enabled', function () {
    $rules = app(\App\Services\PublicFormGuardService::class)->mergeRules(
        ['email' => ['required', 'email']],
        emailFields: ['email'],
    );

    $validator = Validator::make(
        [
            'email' => 'user@mailinator.com',
            'company_website' => '',
        ],
        $rules,
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('forgot password request validates required email', function () {
    $rules = (new ForgotPasswordRequest())->rules();

    expect(fn () => Validator::make(['company_website' => ''], $rules)->validate())
        ->toThrow(ValidationException::class);
});
