<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SettingService;
use App\Services\EnvWriter;
use App\Enums\ActionType;

class EmailSettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly EnvWriter $envWriter,
    ) {
    }

    public function index(): Renderable
    {
        $this->authorize('manage', Setting::class);

        $this->setBreadcrumbTitle(__('Email Settings'))
            ->setBreadcrumbIcon('lucide:mail');

        return $this->renderViewWithBreadcrumbs('backend.pages.email-settings.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $request->validate([
            'email_from_email' => 'required|email',
            'email_from_name' => 'required|string|max:255',
            'email_reply_to_email' => 'nullable|email',
            'email_reply_to_name' => 'nullable|string|max:255',
            'email_utm_source_default' => 'nullable|string|max:255',
            'email_utm_medium_default' => 'nullable|string|max:255',
        ]);

        $fields = $request->only([
            'email_from_email',
            'email_from_name',
            'email_reply_to_email',
            'email_reply_to_name',
            'email_utm_source_default',
            'email_utm_medium_default',
        ]);

        foreach ($fields as $fieldName => $fieldValue) {
            $this->settingService->addSetting($fieldName, $fieldValue);
        }

        $this->envWriter->batchWriteKeysToEnvFile($fields);

        $this->storeActionLog(ActionType::UPDATED, [
            'email_settings' => $fields,
        ]);

        return redirect()->back()->with('success', __('Email settings updated successfully.'));
    }
}
