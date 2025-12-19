<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;

pest()->use(RefreshDatabase::class);

it('has fillable attributes', function () {
    $user = new User();
    expect($user->getFillable())->toEqual([
        'first_name',
        'last_name',
        'email',
        'password',
        'username',
        'avatar_id',
        'email_subscribed',
    ]);
});

it('has hidden attributes', function () {
    $user = new User();
    expect($user->getHidden())->toEqual([
        'password',
        'remember_token',
        'email_verified_at',
    ]);
});

it('has casted attributes', function () {
    $user = new User();
    $casts = $user->getCasts();
    expect($casts)->toHaveKey('email_verified_at');
    expect($casts['email_verified_at'])->toEqual('datetime');
});

it('sends admin reset password notification for admin routes', function () {
    Notification::fake();
    $user = User::factory()->create();
    app('request')->headers->set('referer', 'http://localhost/admin/login');
    app('request')->server->set('REQUEST_URI', '/admin/password/reset');
    $user->sendPasswordResetNotification('token');
    Notification::assertSentTo($user, AdminResetPasswordNotification::class);
});

it('sends default reset password notification for non admin routes', function () {
    Notification::fake();
    $user = User::factory()->create();
    app('request')->headers->set('referer', 'http://localhost:8000/login');
    app('request')->server->set('REQUEST_URI', '/password/reset');
    $user->sendPasswordResetNotification('token');
    Notification::assertSentTo($user, ResetPassword::class);
});

it('can check if user has any permission', function () {
    $user = User::factory()->create();
    $permission1 = Permission::create(['name' => 'test.permission1']);
    $permission2 = Permission::create(['name' => 'test.permission2']);
    $user->givePermissionTo($permission1);
    expect($user->hasAnyPermission(['test.permission1', 'test.permission2']))->toBeTrue();
    expect($user->hasAnyPermission('test.permission1'))->toBeTrue();
    expect($user->hasAnyPermission('test.permission2'))->toBeFalse();
    expect($user->hasAnyPermission([]))->toBeTrue();
});
