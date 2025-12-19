<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Services\TemplateTypeRegistry;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailTemplateDatatable extends Datatable
{
    public string $model = EmailTemplate::class;
    public string $type = '';
    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'type' => [],
    ];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or subject');
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'type',
                'label' => __('Type'),
                'filterLabel' => __('Filter by Type'),
                'icon' => 'lucide:filter',
                'allLabel' => __('All Types'),
                'options' => TemplateTypeRegistry::getDropdownItems(),
                'selected' => $this->type,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            'create' => 'admin.email-templates.create',
            'view' => 'admin.email-templates.show',
            'edit' => 'admin.email-templates.edit',
            'delete' => 'admin.email-templates.destroy',
        ];
    }

    public function getPermissions(): array
    {
        return [
            'create' => 'settings.edit',
            'view' => 'settings.edit',
            'edit' => 'settings.edit',
            'delete' => 'settings.edit',
            'duplicate' => 'settings.edit',
        ];
    }

    protected function getItemRouteParameters($item): array
    {
        return [
            'email_template' => $item->id,
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'preview',
                'title' => __('Preview'),
                'width' => '10%',
                'sortable' => false,
            ],
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
                'width' => '20%',
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
                'id' => 'actions',
                'title' => __('Action'),
                'width' => '10%',
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
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            });

        return $this->sortQuery($query);
    }

    public function renderPreviewColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-preview', compact('emailTemplate'));
    }

    public function renderNameColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-name', compact('emailTemplate'));
    }

    public function renderIsActiveColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-is-active', compact('emailTemplate'));
    }

    public function renderTypeColumn(EmailTemplate $emailTemplate): Renderable
    {
        return view('backend.pages.email-templates.partials.email-template-type', compact('emailTemplate'));
    }

    public function renderSubjectColumn(EmailTemplate $emailTemplate): string
    {
        return Str::limit($emailTemplate->subject, 50);
    }

    public function renderAfterActionView($emailTemplate): string|Renderable
    {
        return view('backend.pages.email-templates.partials.action-button-test', compact('emailTemplate'));
    }

    public function renderAfterActionEdit($emailTemplate): string|Renderable
    {
        if (! Auth::user()->can($this->getPermissions()['duplicate'] ?? '', $emailTemplate)) {
            return '';
        }

        return view('backend.pages.email-templates.partials.action-button-duplicate', compact('emailTemplate'));
    }

    protected function handleBulkDelete(array $ids): int
    {
        $emailTemplates = EmailTemplate::whereIn('id', $ids)->where('is_deleteable', true)->get();
        $deletedCount = 0;
        foreach ($emailTemplates as $emailTemplate) {
            $this->authorize('manage', Setting::class);
            $emailTemplate->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    public function handleRowDelete(Model|EmailTemplate $emailTemplate): bool
    {
        if (! $emailTemplate->is_deleteable) {
            return false;
        }
        $this->authorize('manage', Setting::class);
        return $emailTemplate->delete();
    }

    public function getActionCellPermissions($item): array
    {
        $permissions = parent::getActionCellPermissions($item);

        if (! $item->is_deleteable) {
            $permissions['delete'] = false;
        }

        // Duplicate permission for action items
        $permissions['duplicate'] = Auth::user()->can($this->getPermissions()['duplicate'] ?? '', $item);

        return $permissions;
    }
}
