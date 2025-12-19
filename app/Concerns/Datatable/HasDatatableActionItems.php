<?php

declare(strict_types=1);

namespace App\Concerns\Datatable;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

trait HasDatatableActionItems
{
    public string $actionColumnLabel = '';
    public bool $showActionColumnLabel = false;
    public string $actionColumnIcon = 'lucide:more-horizontal';
    public string $viewButtonLabel = '';
    public string $viewButtonIcon = 'lucide:eye';
    public string $editButtonLabel = '';
    public string $editButtonIcon = 'lucide:pencil';
    public string $deleteButtonLabel = '';
    public string $deleteButtonIcon = 'lucide:trash';

    public function getActionCellPermissions($item): array
    {
        $canDelete = Auth::user()->can($this->getPermissions()['delete'] ?? '', $item);

        // Check if item has is_deleteable attribute and respect it
        if (array_key_exists('is_deleteable', $item->getAttributes()) && ! $item->is_deleteable) {
            $canDelete = false;
        }

        return [
            'view' => Auth::user()->can($this->getPermissions()['view'] ?? '', $item),
            'edit' => Auth::user()->can($this->getPermissions()['edit'] ?? '', $item),
            'delete' => $canDelete,
        ];
    }

    public function setActionLabels(): self
    {
        $this->actionColumnLabel = __('Actions');
        $this->viewButtonLabel = __('View');
        $this->editButtonLabel = __('Edit');
        $this->deleteButtonLabel = __('Delete');
        return $this;
    }

    public function showActionItems($item): bool
    {
        $permissions = $this->getActionCellPermissions($item);
        $permissionsCheck = false;

        // Add Or condition permission check.
        foreach ($permissions as $value) {
            if ($value) {
                $permissionsCheck = true;
                break;
            }
        }
        return $permissionsCheck;
    }

    public function renderActionsColumn($item): string|Renderable
    {
        if ($this->showActionItems($item) === false) {
            return '';
        }

        return view('backend.livewire.datatable.action-buttons', [
            'item' => $item,
            'permissions' => $this->getActionCellPermissions($item),
            'deleteAction' => method_exists($this, 'getDeleteAction') ? $this->getDeleteAction($item->id) : null,

            // Component properties.
            'actionColumnLabel' => $this->actionColumnLabel,
            'showActionColumnLabel' => $this->showActionColumnLabel,
            'actionColumnIcon' => $this->actionColumnIcon,
            'viewButtonIcon' => $this->viewButtonIcon,
            'viewButtonLabel' => $this->viewButtonLabel,
            'editButtonIcon' => $this->editButtonIcon,
            'editButtonLabel' => $this->editButtonLabel,
            'deleteButtonIcon' => $this->deleteButtonIcon,
            'deleteButtonLabel' => $this->deleteButtonLabel,

            // Component methods results.
            'routes' => $this->getRoutes(),
            'componentPermissions' => $this->getPermissions(),
            'viewRouteUrl' => $this->getViewRouteUrl($item),
            'editRouteUrl' => $this->getEditRouteUrl($item),
            'deleteRouteUrl' => $this->getDeleteRouteUrl($item),
            'modelNameSingular' => $this->getModelNameSingular(),

            // Render methods results.
            'beforeActionView' => $this->renderBeforeActionView($item),
            'afterActionView' => $this->renderAfterActionView($item),
            'afterActionEdit' => $this->renderAfterActionEdit($item),
            'afterActionDelete' => $this->renderAfterActionDelete($item),
        ]);
    }

    public function renderBeforeActionView($item): string|Renderable
    {
        return '';
    }

    public function renderAfterActionEdit($item): string|Renderable
    {
        return '';
    }

    public function renderAfterActionDelete($item): string|Renderable
    {
        return '';
    }

    public function renderAfterActionView($item): string|Renderable
    {
        return '';
    }
}
