<?php

declare(strict_types=1);

namespace App\Pages\User;

use App\Models\User;
use App\PageGenerator\AbstractFormPage;
use App\Services\LanguageService;
use App\Services\RolesService;
use App\Services\TimezoneService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserCreateFlexible extends AbstractFormPage
{
    public function __construct(
        Request $request,
        private readonly RolesService $rolesService,
        private readonly LanguageService $languageService,
        private readonly TimezoneService $timezoneService
    ) {
        parent::__construct($request);
    }

    public function authorize(): bool
    {
        return Gate::allows('create', User::class);
    }

    public function getTitle(): string
    {
        return __('Create User');
    }

    public function getBreadcrumbs(): array
    {
        return [
            'title' => __('New User'),
            'items' => [
                [
                    'label' => __('Users'),
                    'url' => route('admin.users.index'),
                ],
            ],
        ];
    }

    public function getFields(): array
    {
        // Using fluent API with type-safe field builders
        return $this->form(false) // No sections
            ->defaultContainerClass('md:col-span-1') // Default half-width
            ->field(
                'first_name',
                $this->text('first_name')
                    ->label(__('First Name'))
                    ->placeholder(__('Enter first name'))
                    ->required()
            )
            ->field(
                'last_name',
                $this->text('last_name')
                    ->label(__('Last Name'))
                    ->placeholder(__('Enter last name'))
                    ->required()
            )
            ->field(
                'username',
                $this->text('username')
                    ->label(__('Username'))
                    ->placeholder(__('Enter username'))
                    ->help(__('Username must be unique'))
                    ->required()
                    ->fullWidth() // Override default to full width
            )
            ->field(
                'email',
                $this->email('email')
                    ->label(__('Email'))
                    ->placeholder(__('Enter email address'))
                    ->required()
                    ->fullWidth()
            )
            ->field(
                'password',
                $this->password('password')
                    ->label(__('Password'))
                    ->placeholder(__('Enter password'))
                    ->help(__('Password must be at least 8 characters'))
                    ->minLength(8)
                    ->required()
            )
            ->field(
                'password_confirmation',
                $this->password('password_confirmation')
                    ->label(__('Confirm Password'))
                    ->placeholder(__('Confirm password'))
                    ->required()
            )
            ->field(
                'roles',
                $this->select('roles')
                    ->label(__('Roles'))
                    ->options($this->rolesService->getRolesDropdown())
                    ->multiple()
                    ->help(__('Select one or more roles for this user'))
                    ->fullWidth()
            )
            ->field(
                'locale',
                $this->select('locale')
                    ->label(__('Language'))
                    ->options($this->languageService->getLanguages())
                    ->default(config('app.locale'))
            )
            ->field(
                'timezone',
                $this->select('timezone')
                    ->label(__('Timezone'))
                    ->options($this->timezoneService->getTimezones())
                    ->default(config('app.timezone'))
            )
            ->field(
                'avatar',
                $this->file('avatar')
                    ->label(__('Avatar'))
                    ->images()
                    ->help(__('Upload an avatar image (optional)'))
                    ->fullWidth()
            )
            ->getFields();
    }

    // Alternative method using traditional array syntax for comparison
    public function getFieldsArraySyntax(): array
    {
        return [
            'first_name' => [
                'type' => 'text',
                'label' => __('First Name'),
                'required' => true,
                'placeholder' => __('Enter first name'),
                'containerClass' => 'md:col-span-1',
            ],
            'last_name' => [
                'type' => 'text',
                'label' => __('Last Name'),
                'required' => true,
                'placeholder' => __('Enter last name'),
                'containerClass' => 'md:col-span-1',
            ],
            'username' => [
                'type' => 'text',
                'label' => __('Username'),
                'required' => true,
                'placeholder' => __('Enter username'),
                'help' => __('Username must be unique'),
                'containerClass' => 'col-span-full',
            ],
            'email' => [
                'type' => 'email',
                'label' => __('Email'),
                'required' => true,
                'placeholder' => __('Enter email address'),
                'containerClass' => 'col-span-full',
            ],
            // ... more fields
        ];
    }

    public function getSections(): array
    {
        // No sections for flexible layout
        return [];
    }

    public function getFormAction(): string
    {
        return route('admin.users.store');
    }

    public function getValidationRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'locale' => 'string|max:5',
            'timezone' => 'string|max:50',
            'avatar' => 'nullable|image|max:2048',
        ];
    }

    public function getCancelRoute(): string
    {
        return route('admin.users.index');
    }

    public function getSubmitButtonText(): string
    {
        return __('Create User');
    }

    public function beforeSave(array $data): array
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        // Remove password confirmation
        unset($data['password_confirmation']);

        return $data;
    }

    public function afterSave(Model $model): void
    {
        // Assign roles if provided (assumes User model has roles relationship)
        if ($this->request->has('roles') && method_exists($model, 'roles')) {
            $model->roles()->sync($this->request->input('roles', []));
        }

        // Handle avatar upload
        if ($this->request->hasFile('avatar')) {
            // Handle avatar upload logic here
            // This would typically involve storing the file and updating the user's avatar
        }
    }
}
