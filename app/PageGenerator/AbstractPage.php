<?php

declare(strict_types=1);

namespace App\PageGenerator;

use App\PageGenerator\Contracts\PageContract;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;

abstract class AbstractPage implements PageContract, Renderable
{
    protected string $layout = 'backend.layouts.app';
    protected string $section = 'admin-content';
    protected array $viewData = [];
    protected ?string $customView = null;

    public function __construct()
    {
        $this->setUp();
    }

    protected function setUp(): void
    {
        // Override in child classes for initialization
    }

    public function render(): string
    {
        if (! $this->authorize()) {
            abort(403, 'Unauthorized action.');
        }

        $view = $this->getView() ?? $this->getDefaultView();

        if (! View::exists($view)) {
            return $this->renderWithComponents();
        }

        return view($view, $this->prepareViewData())->render();
    }

    protected function renderWithComponents(): string
    {
        $data = $this->prepareViewData();
        $content = $this->buildContent($data);

        return view($this->layout, [
            $this->section => $content,
            ...$data,
        ])->render();
    }

    protected function buildContent(array $data): string
    {
        return '';
    }

    protected function prepareViewData(): array
    {
        return array_merge($this->getDefaultViewData(), $this->getViewData());
    }

    protected function getDefaultViewData(): array
    {
        return [
            'title' => $this->getTitle(),
            'breadcrumbs' => $this->getBreadcrumbs(),
        ];
    }

    public function getView(): ?string
    {
        return $this->customView;
    }

    public function setView(string $view): self
    {
        $this->customView = $view;

        return $this;
    }

    protected function getDefaultView(): string
    {
        return '';
    }

    public function authorize(): bool
    {
        return true;
    }

    public function with(string $key, mixed $value): self
    {
        $this->viewData[$key] = $value;

        return $this;
    }

    public function withData(array $data): self
    {
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }

    abstract public function getTitle(): string;

    abstract public function getBreadcrumbs(): array;
}
