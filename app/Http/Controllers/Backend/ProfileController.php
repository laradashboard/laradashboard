<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileAdditionalRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Services\LanguageService;
use App\Services\TimezoneService;
use App\Services\UserService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly TimezoneService $timezoneService,
        private readonly UserService $userService,
    ) {
    }

    public function edit(): Renderable
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        // Load user metadata
        $userMeta = $user->userMeta()->pluck('meta_value', 'meta_key')->toArray();

        // Load localization data
        $locales = $this->languageService->getLanguages();
        $timezones = $this->timezoneService->getTimezones();

        return view('backend.pages.profile.edit', compact('user', 'userMeta', 'locales', 'timezones'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Edit Profile'),
                ],
            ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        // Prevent modification of super admin in demo mode.
        $this->preventSuperAdminModification(Auth::user(), ['profile.edit']);

        /**
         * @var User $user
         */
        $user = Auth::user();

        // Use UserService to update user
        $this->userService->updateUserWithMetadata($user, $request->validated(), $request);

        ld_do_action('user_profile_update_after', $user);

        session()->flash('success', 'Profile updated successfully.');

        $this->storeActionLog(ActionType::UPDATED, ['profile' => $user]);

        return redirect()->route('profile.edit');
    }

    public function updateAdditional(UpdateProfileAdditionalRequest $request): RedirectResponse
    {
        // Prevent modification of super admin in demo mode.
        $this->preventSuperAdminModification(Auth::user(), ['profile.edit']);

        /**
         * @var User $user
         */
        $user = Auth::user();

        // Use UserService to update user metadata
        $this->userService->updateUserWithMetadata($user, $request->validated(), $request);

        ld_do_action('user_profile_additional_update_after', $user);

        session()->flash('success', 'Additional information updated successfully.');

        $this->storeActionLog(ActionType::UPDATED, ['profile_additional' => $user]);

        return redirect()->route('profile.edit');
    }
}
