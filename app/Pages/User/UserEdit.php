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

class UserEdit extends AbstractFormPage
{
    protected string $formMethod = 'PUT';

    public function __construct(
        Request $request,
        User $user,
        private readonly RolesService $rolesService,
        private readonly LanguageService $languageService,
        private readonly TimezoneService $timezoneService
    ) {
        parent::__construct($request, $user);
    }

    public function authorize(): bool
    {
        return Gate::allows('update', $this->model);
    }

    public function getTitle(): string
    {
        return __('Edit User');
    }

    public function getBreadcrumbs(): array
    {
        return [
            'title' => __('Edit User'),
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
        return [
            'first_name' => [
                'type' => 'text',
                'label' => __('First Name'),
                'required' => true,
                'placeholder' => __('Enter first name'),
            ],
            'last_name' => [
                'type' => 'text',
                'label' => __('Last Name'),
                'required' => true,
                'placeholder' => __('Enter last name'),
            ],
            'username' => [
                'type' => 'text',
                'label' => __('Username'),
                'required' => true,
                'placeholder' => __('Enter username'),
                'help' => __('Username must be unique'),
            ],
            'email' => [
                'type' => 'email',
                'label' => __('Email'),
                'required' => true,
                'placeholder' => __('Enter email address'),
            ],
            'password' => [
                'type' => 'password',
                'label' => __('New Password'),
                'placeholder' => __('Leave blank to keep current password'),
                'help' => __('Leave blank to keep current password'),
            ],
            'password_confirmation' => [
                'type' => 'password',
                'label' => __('Confirm New Password'),
                'placeholder' => __('Confirm new password'),
            ],
            'roles' => [
                'type' => 'select',
                'label' => __('Roles'),
                'multiple' => true,
                'options' => $this->rolesService->getRolesDropdown(),
                'help' => __('Select one or more roles for this user'),
                'default' => $this->model->roles->pluck('id')->toArray(),
            ],
            'locale' => [
                'type' => 'select',
                'label' => __('Language'),
                'options' => $this->languageService->getLanguages(),
            ],
            'timezone' => [
                'type' => 'select',
                'label' => __('Timezone'),
                'options' => $this->timezoneService->getTimezones(),
            ],
            'avatar' => [
                'type' => 'file',
                'label' => __('Avatar'),
                'accept' => 'image/*',
                'help' => __('Upload a new avatar image (optional)'),
                'preview' => true,
            ],
            'status' => [
                'type' => 'select',
                'label' => __('Status'),
                'options' => [
                    'active' => __('Active'),
                    'inactive' => __('Inactive'),
                    'suspended' => __('Suspended'),
                ],
            ],
        ];
    }

    public function getSections(): array
    {
        return [
            [
                'title' => __('Basic Information'),
                'description' => __('Update the basic information for this user.'),
                'fields' => ['first_name', 'last_name', 'username', 'email'],
                'columns' => 'md:grid-cols-2',
            ],
            [
                'title' => __('Change Password'),
                'description' => __('Update the user\'s password. Leave blank to keep current password.'),
                'fields' => ['password', 'password_confirmation'],
                'columns' => 'md:grid-cols-2',
            ],
            [
                'title' => __('Permissions & Preferences'),
                'description' => __('Configure user permissions and preferences.'),
                'fields' => ['roles', 'locale', 'timezone', 'status'],
                'columns' => 'md:grid-cols-2',
            ],
            [
                'title' => __('Profile'),
                'description' => __('Update profile information.'),
                'fields' => ['avatar'],
            ],
        ];
    }

    public function getFormAction(): string
    {
        return route('admin.users.update', $this->model->id);
    }

    public function getValidationRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->model->id,
            'email' => 'required|email|max:255|unique:users,email,' . $this->model->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'locale' => 'string|max:5',
            'timezone' => 'string|max:50',
            'avatar' => 'nullable|image|max:2048',
            'status' => 'in:active,inactive,suspended',
        ];
    }

    public function getCancelRoute(): string
    {
        return route('admin.users.index');
    }

    public function getSubmitButtonText(): string
    {
        return __('Update User');
    }

    public function beforeSave(array $data): array
    {
        // Hash password if provided
        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        // Remove password confirmation
        unset($data['password_confirmation']);

        return $data;
    }

    public function afterSave(Model $model): void
    {
        // Assign roles if provided
        if ($this->request->has('roles')) {
            $model->roles()->sync($this->request->input('roles', []));
        }

        // Handle avatar upload
        if ($this->request->hasFile('avatar')) {
            // Handle avatar upload logic here
            // This would typically involve storing the file and updating the user's avatar
        }
    }
}
