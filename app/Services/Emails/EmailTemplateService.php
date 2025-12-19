<?php

declare(strict_types=1);

namespace App\Services\Emails;

use Illuminate\Database\Eloquent\Collection;
use App\Models\EmailTemplate;
use App\Enums\TemplateType;
use Illuminate\Support\Facades\DB;
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
            ->select('id', 'name')
            ->get();
    }

    public function getTemplateById(int $id): ?EmailTemplate
    {
        return EmailTemplate::with(['creator', 'updater', 'emailLogs'])
            ->find($id);
    }

    public function getTemplateByUuid(string $uuid): ?EmailTemplate
    {
        return EmailTemplate::with(['creator', 'updater', 'emailLogs'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function createTemplate(array $data): EmailTemplate
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['uuid']) || empty($data['uuid'])) {
                $data['uuid'] = Str::uuid();
            }

            if (! isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }

            $template = EmailTemplate::create($data);

            return $template->load(['creator', 'updater']);
        });
    }

    public function updateTemplate(EmailTemplate $emailTemplate, array $data): EmailTemplate
    {
        // Fill the model with the new values first so that Eloquent can determine which attributes are dirty.
        $emailTemplate->fill($data);

        if ($emailTemplate->isClean(array_keys($data))) {
            return $emailTemplate;
        }

        return DB::transaction(function () use ($emailTemplate, $data) {
            $data['updated_by'] = auth()->id();
            $emailTemplate->update($data);

            return $emailTemplate->load(['creator', 'updater']);
        });
    }

    public function deleteTemplate(EmailTemplate $template): bool
    {
        return DB::transaction(function () use ($template) {
            // Check if template is being used in any campaigns.
            if (DB::table('email_campaigns')->where('template_id', $template->id)->exists()) {
                throw new \Exception('Cannot delete template that is being used in campaigns. Please remove it from any campaigns before deleting.');
            }

            $template->delete();

            return true;
        });
    }

    public function duplicateTemplate(EmailTemplate $template, string $newName): EmailTemplate
    {
        return DB::transaction(function () use ($template, $newName) {
            $data = $template->toArray();

            // Remove unique fields and update for new template.
            unset($data['id'], $data['uuid'], $data['created_at'], $data['updated_at']);
            $data['name'] = $newName;
            $data['uuid'] = Str::uuid();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = null;
            $data['is_deleteable'] = true;

            $newTemplate = EmailTemplate::create($data);

            return $newTemplate->load(['creator', 'updater']);
        });
    }

    public function getTemplatesByType($type): Collection
    {
        $value = $type instanceof TemplateType ? $type->value : (string) $type;
        return EmailTemplate::where('type', $value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getEmailTemplatesDropdown(): array
    {
        return EmailTemplate::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
