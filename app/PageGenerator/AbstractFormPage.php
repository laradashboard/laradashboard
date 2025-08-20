<?php

declare(strict_types=1);

namespace App\PageGenerator;

use App\PageGenerator\Contracts\FormPageContract;
use App\PageGenerator\Traits\HasFieldBuilders;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class AbstractFormPage extends AbstractPage implements FormPageContract
{
    use HasFieldBuilders;
    protected Request $request;
    protected ?Model $model = null;
    protected string $formMethod = 'POST';
    protected string $submitButtonText = 'Save';
    protected string $cancelButtonText = 'Cancel';
    protected bool $showCancelButton = true;
    protected bool $isAjaxForm = false;
    protected string $formId = 'form';
    protected string $formClasses = '';

    public function __construct(Request $request, ?Model $model = null)
    {
        $this->request = $request;
        $this->model = $model;
        parent::__construct();
    }

    protected function buildContent(array $data): string
    {
        return view('page-generator.form', $data)->render();
    }

    protected function getDefaultView(): string
    {
        return 'page-generator.form';
    }

    protected function prepareViewData(): array
    {
        $data = parent::prepareViewData();

        return array_merge($data, [
            'fields' => $this->processFields($this->getFields()),
            'formAction' => $this->getFormAction(),
            'formMethod' => $this->getFormMethod(),
            'model' => $this->getModel(),
            'submitButtonText' => $this->getSubmitButtonText(),
            'cancelButtonText' => $this->getCancelButtonText(),
            'cancelRoute' => $this->getCancelRoute(),
            'showCancelButton' => $this->showCancelButton(),
            'isAjaxForm' => $this->isAjaxForm(),
            'formId' => $this->getFormId(),
            'formClasses' => $this->getFormClasses(),
            'sections' => $this->getSections(),
            'enctype' => $this->getEnctype(),
        ]);
    }

    protected function processFields(array $fields): array
    {
        $processedFields = [];

        foreach ($fields as $key => $field) {
            if ($field instanceof \App\PageGenerator\FieldBuilders\BaseFieldBuilder) {
                // If it's a field builder, convert to array using the field's name
                $processedFields[$field->getName()] = $field->toArray();
            } elseif (is_string($key) && is_array($field)) {
                // Traditional array syntax
                $processedFields[$key] = $field;
            } elseif (is_int($key) && $field instanceof \App\PageGenerator\FieldBuilders\BaseFieldBuilder) {
                // Array of field builders without keys
                $processedFields[$field->getName()] = $field->toArray();
            }
        }

        return $processedFields;
    }

    public function getFormMethod(): string
    {
        return $this->formMethod;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function getSubmitButtonText(): string
    {
        return __($this->submitButtonText);
    }

    public function getCancelButtonText(): string
    {
        return __($this->cancelButtonText);
    }

    public function showCancelButton(): bool
    {
        return $this->showCancelButton;
    }

    public function isAjaxForm(): bool
    {
        return $this->isAjaxForm;
    }

    public function getFormId(): string
    {
        return $this->formId;
    }

    public function getFormClasses(): string
    {
        return $this->formClasses;
    }

    public function getSections(): array
    {
        return [
            [
                'title' => null,
                'fields' => array_keys($this->getFields()),
            ],
        ];
    }

    public function beforeSave(array $data): array
    {
        return $data;
    }

    public function afterSave(Model $model): void
    {
        // Override in child classes
    }

    protected function getEnctype(): string
    {
        $fields = $this->processFields($this->getFields());

        foreach ($fields as $field) {
            if (($field['type'] ?? '') === 'file') {
                return 'multipart/form-data';
            }
        }

        return 'application/x-www-form-urlencoded';
    }

    protected function getFieldValue(string $name, mixed $default = null): mixed
    {
        if ($this->model) {
            return old($name, $this->model->getAttribute($name) ?? $default);
        }

        return old($name, $default);
    }

    abstract public function getFields(): array;

    abstract public function getFormAction(): string;

    abstract public function getValidationRules(): array;

    abstract public function getCancelRoute(): string;
}
