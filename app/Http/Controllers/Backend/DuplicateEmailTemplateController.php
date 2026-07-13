<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Email\StoreDuplicateEmailRequest;
use App\Models\EmailTemplate;
use App\Services\Emails\EmailTemplateService;
use Illuminate\Http\RedirectResponse;

class DuplicateEmailTemplateController extends Controller
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
    ) {
    }

    public function store(EmailTemplate $emailTemplate, StoreDuplicateEmailRequest $request): RedirectResponse
    {
        $this->authorize('create', EmailTemplate::class);

        try {
            $newTemplate = $this->emailTemplateService->duplicateTemplate(
                $emailTemplate,
                $request->input('name')
            );

            return redirect()
                ->route('admin.email-templates.show', $newTemplate->id)
                ->with('success', __('Email template duplicated successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('Failed to duplicate the email template. Error :error', ['error' => $e->getMessage()]));
        }
    }
}
