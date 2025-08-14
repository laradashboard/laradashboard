<?php

declare(strict_types=1);

namespace App\Services\MenuService;

use Illuminate\Support\Facades\Auth;

class AdminMenuItem
{
    public string $label = '';

    public ?string $icon = null;

    public ?string $iconClass = null;

    public ?string $route = null;

    public bool $active = false;

    public ?string $id = null;

    /**
     * @var AdminMenuItem[]
     */
    public array $children = [];

    public ?string $target = null;

    public int $priority = 1;

    public array $permissions = [];

    public ?string $itemStyles = '';

    public ?string $htmlData = null;

    public string $title = '';

    public function setLabel(string $label): self
    {
        $this->label = $label;
        if (empty($this->title)) {
            $this->title = $label;
        }

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        if (empty($this->label)) {
            $this->label = $title;
        }

        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function setIconClass(?string $iconClass): self
    {
        $this->iconClass = $iconClass;

        return $this;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function setPermissions(string|array $permissions): self
    {
        $this->permissions = is_array($permissions) ? $permissions : [$permissions];

        return $this;
    }

    public function setItemStyles(?string $styles): self
    {
        $this->itemStyles = $styles;

        return $this;
    }

    /**
     * Check if this menu item or any of its children are active
     * based on the current route
     */
    public function isActive(): bool
    {
        if ($this->active) {
            return true;
        }

        // Check if any children are active.
        foreach ($this->children as $child) {
            if ($child->isActive()) {
                return true;
            }
        }

        return false;
    }

    public function userHasPermission(): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        $user = Auth::user();
        foreach ($this->permissions as $permission) {
            if ($user && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function setHtml(string $htmlData): self
    {
        $this->htmlData = $htmlData;

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            } elseif (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'title' => $this->title,
            'icon' => $this->icon,
            'iconClass' => $this->iconClass,
            'route' => $this->route,
            'active' => $this->active,
            'id' => $this->id,
            'target' => $this->target,
            'permissions' => $this->permissions,
            'priority' => $this->priority,
            'htmlData' => $this->htmlData,
            'children' => array_map(function ($child) {
                return $child->toArray();
            }, $this->children),
        ];
    }
}
