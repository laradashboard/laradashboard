<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Models\EmailTemplate;
use App\Enums\TemplateType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmailTemplateService
{
    private function _buildTemplateQuery($filter = null)
    {
        if ($filter === null) {
            $filter = request()->all();
        }

        $query = EmailTemplate::query()
            ->with(['creator', 'updater']);

        if (isset($filter['search']) && ! empty($filter['search'])) {
            $search = $filter['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filter['type']) && ! empty($filter['type'])) {
            $query->where('type', $filter['type']);
        }

        if (isset($filter['is_active']) && $filter['is_active'] !== '') {
            $query->where('is_active', (bool) $filter['is_active']);
        }

        if (isset($filter['created_by']) && ! empty($filter['created_by'])) {
            $query->where('created_by', $filter['created_by']);
        }

        if (isset($filter['date_from']) && ! empty($filter['date_from'])) {
            $query->whereDate('created_at', '>=', $filter['date_from']);
        }

        if (isset($filter['date_to']) && ! empty($filter['date_to'])) {
            $query->whereDate('created_at', '<=', $filter['date_to']);
        }

        return $query;
    }

    public function getPaginatedTemplates(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $filter = request()->all();
        if ($search) {
            $filter['search'] = $search;
        }

        return $this->_buildTemplateQuery($filter)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllTemplates($filter = null): Collection
    {
        return $this->_buildTemplateQuery($filter)
            ->orderBy('name')
            ->get();
    }

    public function getAllTemplatesExcept(int $excludeId): Collection
    {
        return EmailTemplate::where('id', '!=', $excludeId)
            ->orderBy('name')
            ->get();
    }

    public function getTemplateById(int $id): ?EmailTemplate
    {
        return EmailTemplate::with(['creator', 'updater', 'emailLogs', 'headerTemplate', 'footerTemplate'])
            ->find($id);
    }

    public function getTemplateByUuid(string $uuid): ?EmailTemplate
    {
        return EmailTemplate::with(['creator', 'updater', 'emailLogs', 'headerTemplate', 'footerTemplate'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function createTemplate(array $data): EmailTemplate
    {
        DB::beginTransaction();

        try {
            // Generate UUID if not provided
            if (! isset($data['uuid']) || empty($data['uuid'])) {
                $data['uuid'] = Str::uuid();
            }

            // Set created_by if not provided
            if (! isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }

            // Extract and validate variables from content
            $template = EmailTemplate::create($data);

            // Update extracted variables
            $variables = $template->extractVariables();
            $template->update(['variables' => $variables]);

            DB::commit();

            Log::info('Email template created', ['template_id' => $template->id, 'name' => $template->name]);

            return $template->load(['creator', 'updater']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create email template', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    public function updateTemplate(EmailTemplate $template, array $data): EmailTemplate
    {
        DB::beginTransaction();

        try {
            // Set updated_by
            $data['updated_by'] = auth()->id();

            $template->update($data);

            // Update extracted variables
            $variables = $template->extractVariables();
            $template->update(['variables' => $variables]);

            DB::commit();

            Log::info('Email template updated', ['template_id' => $template->id, 'name' => $template->name]);

            return $template->load(['creator', 'updater']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update email template', ['error' => $e->getMessage(), 'template_id' => $template->id]);
            throw $e;
        }
    }

    public function deleteTemplate(EmailTemplate $template): bool
    {
        DB::beginTransaction();

        try {
            // Check if template is being used in any campaigns.
            if (DB::table('email_campaigns')->where('template_id', $template->id)->exists()) {
                throw new \Exception('Cannot delete template that is being used in campaigns. Please remove it from any campaigns before deleting.');
            }

            // Delete preview image if exists
            if ($template->preview_image && Storage::disk('public')->exists($template->preview_image)) {
                Storage::disk('public')->delete($template->preview_image);
            }

            $templateId = $template->id;
            $templateName = $template->name;

            $template->delete();

            DB::commit();

            Log::info('Email template deleted', ['template_id' => $templateId, 'name' => $templateName]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete email template', ['error' => $e->getMessage(), 'template_id' => $template->id]);
            throw $e;
        }
    }

    public function duplicateTemplate(EmailTemplate $template, string $newName): EmailTemplate
    {
        DB::beginTransaction();

        try {
            $data = $template->toArray();

            // Remove unique fields and update for new template
            unset($data['id'], $data['uuid'], $data['created_at'], $data['updated_at']);
            $data['name'] = $newName;
            $data['uuid'] = Str::uuid();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = null;
            $data['is_default'] = false;

            $newTemplate = EmailTemplate::create($data);

            DB::commit();

            Log::info('Email template duplicated', [
                'original_template_id' => $template->id,
                'new_template_id' => $newTemplate->id,
                'new_name' => $newName,
            ]);

            return $newTemplate->load(['creator', 'updater']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to duplicate email template', ['error' => $e->getMessage(), 'template_id' => $template->id]);
            throw $e;
        }
    }

    public function setAsDefault(EmailTemplate $template): EmailTemplate
    {
        DB::beginTransaction();

        try {
            // Remove default from other templates of the same type
            EmailTemplate::where('type', $template->type)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set this template as default
            $template->update(['is_default' => true]);

            DB::commit();

            Log::info('Email template set as default', ['template_id' => $template->id, 'type' => $template->type->value]);

            return $template;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to set email template as default', ['error' => $e->getMessage(), 'template_id' => $template->id]);
            throw $e;
        }
    }

    public function renderTemplate(EmailTemplate $template, array $data): array
    {
        return $template->renderTemplate($data);
    }

    public function getTemplatesByType(TemplateType $type): Collection
    {
        return EmailTemplate::where('type', $type)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function getDefaultTemplate(TemplateType $type): ?EmailTemplate
    {
        return EmailTemplate::where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public function uploadPreviewImage(EmailTemplate $template, $file): string
    {
        try {
            // Delete old preview image if exists
            if ($template->preview_image && Storage::disk('public')->exists($template->preview_image)) {
                Storage::disk('public')->delete($template->preview_image);
            }

            // Store new image
            $filename = 'template_preview_' . $template->id . '_' . time() . '.' . $file->extension();
            $path = $file->storeAs('email-templates/previews', $filename, 'public');

            // Update template
            $template->update(['preview_image' => $path]);

            Log::info('Email template preview image uploaded', ['template_id' => $template->id, 'path' => $path]);

            return $path;

        } catch (\Exception $e) {
            Log::error('Failed to upload email template preview image', ['error' => $e->getMessage(), 'template_id' => $template->id]);
            throw $e;
        }
    }
}
