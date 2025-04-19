<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserLoginAsController extends Controller
{
    public function loginAs(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['user.login_as']);

        $user = User::findOrFail($id);

        Session::put('original_user_id', auth()->id());
        Auth::login($user);

        session()->flash('success', __('You are now logged in as :name.', ['name' => $user->name]));

        return redirect()->route('admin.dashboard');
    }

    public function switchBack(): RedirectResponse
    {
        $originalUserId = session()->pull('original_user_id');
        if ($originalUserId) {
            Auth::loginUsingId($originalUserId);
            session()->flash('success', __('Switched back to the original user.'));
        }

        return redirect()->route('admin.dashboard');
    }
}
