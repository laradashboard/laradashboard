<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class UserTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'user-table-q0kavd-table';
    public bool $showFilters = false;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable(fileName: 'users')
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()
                ->includeViewOnBottom('components.table.user.header')
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query()->with('roles'); // Eager load roles
    }

    public function relationSearch(): array
    {
        return [
            'roles' => [ // Define the relationship search
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name', function ($user) {
                return Blade::render(
                    '
                            <div class="flex gap-2">
                                <span>
                                    <img src="{{ $user->avatar_url }}" class="w-10 h-10 min-w-10 min-h-10 rounded-full">
                                </span>
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $user->first_name }}
                                    </span><br />
                                    <span class="text-xs text-gray-500 dark:text-gray-300">
                                        {{ $user->username }}
                                    </span>
                                </div>
                            </div>
                        ',
                    ['user' => $user]
                );
            })
            ->add('email')
            ->add('roles', function ($user) {
                return Blade::render(
                    '
                        @foreach ($user->roles as $role)
                            <span class="capitalize badge">
                                @if (auth()->user()->can("role.edit"))
                                    <a href="{{ route("admin.roles.edit", $role->id) }}" data-tooltip-target="tooltip-role-{{ $role->id }}-{{ $user->id }}" class="hover:text-primary">
                                        {{ $role->name }}
                                    </a>
                                    <div id="tooltip-role-{{ $role->id }}-{{ $user->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-md shadow-xs opacity-0 tooltip dark:bg-gray-700">
                                        {{ __("Edit") }} {{ $role->name }} {{ __("Role") }}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                @else
                                    {{ $role->name }}
                                @endif
                            </span>
                        @endforeach',
                    ['user' => $user]
                );
            })
            ->add('created_at')
            ->add('actions', function ($user) {
                return Blade::render(
                    '
                    <div>
                        @if (auth()->user()->canBeModified($user) || auth()->user()->can("user.login_as") || auth()->user()->canBeModified($user, "user.delete"))
                        <x-buttons.action-buttons :show-label="false" align="right">
                            @if (auth()->user()->canBeModified($user))
                                <div class="px-6 py-2">
                                    <a
                                        href="{{ route("admin.users.edit", $user->id) }}"
                                        class="text-gray-500 hover:text-gray-700 block"
                                    >
                                        {{ __("Edit") }}
                                    </a>
                                </div>
                            @endif
                            @if (auth()->user()->canBeModified($user, "user.delete"))
                                <div class="px-6 py-2">
                                    <a
                                        href="{{ route("admin.users.destroy", $user->id) }}"
                                        class="text-gray-500 hover:text-gray-700 block"
                                    >
                                        {{ __("Delete") }}
                                    </a>
                                </div>
                            @endif
                            @if (auth()->user()->can("user.login_as") && $user->id != auth()->user()->id)
                                <div class="px-6 py-2">
                                    <a
                                        href="{{ route("admin.users.login-as", $user->id) }}"
                                        class="text-gray-500 hover:text-gray-700 block"
                                    >
                                        {{ __("Login as") }}
                                    </a>
                                </div>
                            @endif
                        </x-buttons.action-buttons>
                        @endif
                    </div>
                    ',
                    ['user' => $user]
                );
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Roles', 'roles')
                ->searchable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            Column::make('Actions', 'actions'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name', 'first_name')
                ->operators(['contains', 'is', 'is_not']),

            Filter::select('role_filter', 'role_filter')
                ->dataSource(Role::select('id', 'name')->get()->toArray())
                ->optionLabel('name')
                ->optionValue('id'),
        ];
    }

    // Custom filter method
    public function filterRoleFilter(Builder $query, string $value): Builder
    {
        return $query->whereHas('roles', function ($q) use ($value) {
            $q->where('roles.id', $value);
        });
    }

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    // Optional: Add custom filter logic if needed
    public function filterRoleByName(Builder $query, string $value): Builder
    {
        return $query->whereHas('roles', function ($q) use ($value) {
            $q->where('name', $value);
        });
    }
}
