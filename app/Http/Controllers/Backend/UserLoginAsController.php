<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class UserLoginAsController extends Controller
{
    public function loginAs(int $id): RedirectResponse
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        if (! $currentUser) {
            abort(403);
        }

        $this->authorize('loginAs', $targetUser);

        if ($currentUser->id === $targetUser->id) {
            abort(403, __('You cannot impersonate your own account.'));
        }

        if (
            $targetUser->hasRole(Role::SUPERADMIN)
            && ! $currentUser->hasRole(Role::SUPERADMIN)
        ) {
            Log::warning('Blocked Superadmin impersonation attempt.', [
                'actor_user_id' => $currentUser->id,
                'target_user_id' => $targetUser->id,
                'actor_email' => $currentUser->email,
                'target_email' => $targetUser->email,
                'ip' => request()->ip(),
            ]);

            abort(403, __('You are not allowed to impersonate a Superadmin user.'));
        }

        Session::put('original_user_id', $currentUser->id);

        Log::info('User impersonation started.', [
            'actor_user_id' => $currentUser->id,
            'target_user_id' => $targetUser->id,
            'actor_email' => $currentUser->email,
            'target_email' => $targetUser->email,
            'ip' => request()->ip(),
        ]);

        Auth::login($targetUser);

        session()->flash('success', __('You are now logged in as :name.', [
            'name' => $targetUser->full_name,
        ]));

        return redirect()->route('admin.dashboard');
    }

    public function switchBack(): RedirectResponse
    {
        $impersonatedUser = Auth::user();
        $originalUserId = session()->pull('original_user_id');

        if ($originalUserId) {
            Log::info('User impersonation ended.', [
                'original_user_id' => $originalUserId,
                'impersonated_user_id' => $impersonatedUser?->id,
                'ip' => request()->ip(),
            ]);

            Auth::loginUsingId($originalUserId);

            session()->flash('success', __('Switched back to the original user.'));
        }

        return redirect()->route('admin.dashboard');
    }
}
