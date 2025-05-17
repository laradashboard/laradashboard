<?php

namespace App\Services\MenuService;

class AdminMenuItem
{
    protected string $label;
    protected ?string $icon = null;
    protected ?string $route = null;
    protected bool $active = false;
    protected ?string $id = null;
    protected array $children = [];
    protected ?string $target = null;
    protected int $priority = 1;
    protected array $permissions = [];
    protected ?string $htmlData = null;

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
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

    public function userHasPermission(): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        $user = auth()->user();
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
        foreach (is_object($attributes) ? (array) $attributes : $attributes as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'icon' => $this->icon,
            'route' => $this->route,
            'active' => $this->active,
            'id' => $this->id,
            'target' => $this->target,
            'priority' => $this->priority,
            'htmlData' => $this->htmlData,
            'children' => array_map(function ($child) {
                return $child instanceof self ? $child->toArray() : $child;
            }, $this->children),
        ];
    }
}
