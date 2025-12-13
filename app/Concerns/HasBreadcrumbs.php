<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\View\View;

trait HasBreadcrumbs
{
    public array $breadcrumbs = [
        'title' => '',
        'show_home' => true,
        'show_current' => true,
        'items' => [],
        'back_url' => null,
        'icon' => null,
        'action' => null,
    ];

    public function setBreadcrumbTitle(string $title): self
    {
        $this->breadcrumbs['title'] = $title;

        return $this;
    }

    public function setBreadcrumbShowHome(bool $show): self
    {
        $this->breadcrumbs['show_home'] = $show;

        return $this;
    }

    public function setBreadcrumbShowCurrent(bool $show): self
    {
        $this->breadcrumbs['show_current'] = $show;

        return $this;
    }

    public function setBreadcrumbItems(array $items): self
    {
        $this->breadcrumbs['items'] = $items;

        return $this;
    }

    public function addBreadcrumbItem(string $label, ?string $url = null): self
    {
        $this->breadcrumbs['items'][] = [
            'label' => $label,
            'url' => $url,
        ];

        return $this;
    }

    public function setBreadcrumbBackUrl(?string $url): self
    {
        $this->breadcrumbs['back_url'] = $url;

        return $this;
    }

    public function setBreadcrumbIcon(?string $icon): self
    {
        $this->breadcrumbs['icon'] = $icon;

        return $this;
    }

    /**
     * Set the action button for the page header.
     *
     * @param  array|string  $action  Can be an array with 'url', 'label', 'icon', 'permission' keys, or raw HTML string
     */
    public function setBreadcrumbAction(array|string $action): self
    {
        $this->breadcrumbs['action'] = $action;

        return $this;
    }

    /**
     * Set the action button using individual parameters.
     */
    public function setBreadcrumbActionButton(string $url, string $label, ?string $icon = 'feather:plus', ?string $permission = null, bool $isPill = false): self
    {
        $this->breadcrumbs['action'] = [
            'url' => $url,
            'label' => $label,
            'icon' => $icon,
            'permission' => $permission,
            'pill' => $isPill,
        ];

        return $this;
    }

    /**
     * Set the action button with a click handler (for Alpine.js or JavaScript).
     *
     * @param  string  $click  The click handler expression (e.g., "uploadModalOpen = true" for Alpine.js)
     * @param  string  $label  The button label
     * @param  string|null  $icon  The icon to display
     * @param  string|null  $permission  The permission required to see this button
     */
    public function setBreadcrumbActionClick(string $click, string $label, ?string $icon = 'feather:plus', ?string $permission = null): self
    {
        $this->breadcrumbs['action'] = [
            'click' => $click,
            'label' => $label,
            'icon' => $icon,
            'permission' => $permission,
        ];

        return $this;
    }

    public function renderViewWithBreadcrumbs($view, $data = []): View
    {
        return view($view, [...$data, 'breadcrumbs' => $this->breadcrumbs]);
    }
}
