<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilesController extends Controller
{
    public function edit(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['profile.edit'], true);

        $user = Auth::user();

        return view('backend.pages.profile.edit', compact('user'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Edit Profile'),
                ],
            ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['profile.edit'], true);

        // Prevent modification of super admin in demo mode.
        $this->preventSuperAdminModification(auth()->user(), ['profile.edit']);

        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $requestInputs = ld_apply_filters('user_profile_update_data_before', [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ], $user);

        $user->update($requestInputs);

        ld_do_action('user_profile_update_after', $user);

        session()->flash('success', 'Profile updated successfully.');

        $this->storeActionLog(ActionType::UPDATED, ['profile' => $user]);

        return redirect()->route('profile.edit');
    }
}
