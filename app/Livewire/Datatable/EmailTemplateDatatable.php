<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailTemplate;
use Spatie\QueryBuilder\QueryBuilder;

class EmailTemplateDatatable extends Datatable
{
    public string $model = EmailTemplate::class;

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or subject');
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.email-templates.create',
            'edit' => 'admin.email-templates.edit',
            'delete' => 'admin.email-templates.destroy',
        ];
    }

    public function getPermissions(): array
    {
        return [
            'create' => 'settings.edit',
            'edit' => 'settings.edit',
            'delete' => 'settings.edit',
        ];
    }

    protected function getRouteParameters(): array
    {
        return [];
    }

    protected function getItemRouteParameters($item): array
    {
        return ['email_template' => $item->id];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Name'),
                'width' => '20%',
                'sortable' => true,
                'sortBy' => 'name',
            ],
            [
                'id' => 'subject',
                'title' => __('Subject'),
                'width' => '25%',
                'sortable' => true,
                'sortBy' => 'subject',
            ],
            [
                'id' => 'type',
                'title' => __('Type'),
                'width' => '15%',
                'sortable' => true,
                'sortBy' => 'type',
            ],
            [
                'id' => 'is_active',
                'title' => __('Status'),
                'width' => '10%',
                'sortable' => true,
                'sortBy' => 'is_active',
            ],
            [
                'id' => 'is_default',
                'title' => __('Default'),
                'width' => '10%',
                'sortable' => true,
                'sortBy' => 'is_default',
            ],
            [
                'id' => 'actions',
                'title' => __('Action'),
                'width' => '15%',
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for(EmailTemplate::query())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('subject', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            });

        return $this->sortQuery($query);
    }

    public function renderNameColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-name', compact('emailTemplate'));
    }

    public function renderIsActiveColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-is-active', compact('emailTemplate'));
    }

    public function renderIsDefaultColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-is-default', compact('emailTemplate'));
    }

    public function renderTypeColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-type', compact('emailTemplate'));
    }

    public function renderSubjectColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-subject', compact('emailTemplate'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $emailTemplates = EmailTemplate::whereIn('id', $ids)->where('is_default', false)->get();
        $deletedCount = 0;
        foreach ($emailTemplates as $emailTemplate) {
            $this->authorize('manage', \App\Models\Setting::class);
            $emailTemplate->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|EmailTemplate $emailTemplate): bool
    {
        if ($emailTemplate->is_default) {
            return false;
        }
        $this->authorize('manage', \App\Models\Setting::class);
        return $emailTemplate->delete();
    }
}