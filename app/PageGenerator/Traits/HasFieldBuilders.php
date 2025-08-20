<?php

declare(strict_types=1);

namespace App\PageGenerator\Traits;

use App\PageGenerator\FieldBuilders\CheckboxInput;
use App\PageGenerator\FieldBuilders\DateTimeInput;
use App\PageGenerator\FieldBuilders\EmailInput;
use App\PageGenerator\FieldBuilders\FileInput;
use App\PageGenerator\FieldBuilders\HiddenInput;
use App\PageGenerator\FieldBuilders\PasswordInput;
use App\PageGenerator\FieldBuilders\RadioInput;
use App\PageGenerator\FieldBuilders\SelectInput;
use App\PageGenerator\FieldBuilders\TextareaInput;
use App\PageGenerator\FieldBuilders\TextInput;
use App\PageGenerator\FormBuilder;

trait HasFieldBuilders
{
    protected function text(string $name): TextInput
    {
        return TextInput::make($name);
    }

    protected function email(string $name): EmailInput
    {
        return EmailInput::make($name);
    }

    protected function password(string $name): PasswordInput
    {
        return PasswordInput::make($name);
    }

    protected function textarea(string $name): TextareaInput
    {
        return TextareaInput::make($name);
    }

    protected function select(string $name): SelectInput
    {
        return SelectInput::make($name);
    }

    protected function checkbox(string $name): CheckboxInput
    {
        return CheckboxInput::make($name);
    }

    protected function radio(string $name): RadioInput
    {
        return RadioInput::make($name);
    }

    protected function file(string $name): FileInput
    {
        return FileInput::make($name);
    }

    protected function datetime(string $name): DateTimeInput
    {
        return DateTimeInput::make($name);
    }

    protected function hidden(string $name): HiddenInput
    {
        return HiddenInput::make($name);
    }

    protected function form(bool $useSections = true): FormBuilder
    {
        return FormBuilder::make($useSections);
    }
}
