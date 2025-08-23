<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('backend.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->phone,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'phone_verified_at' => 1 ? now() : null,
        ]);
        $user->assignRole('student'); 

        Auth::guard('web')->login($user);

        session()->flash('success', 'Account created successfully!');

        return redirect()->route('admin.dashboard');
    }
}
