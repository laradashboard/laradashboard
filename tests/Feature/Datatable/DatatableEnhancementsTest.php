<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyCsrfToken;
use App\Livewire\Datatable\UserDatatable;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $adminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    foreach (['user.view', 'user.create', 'user.edit', 'user.delete'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole->syncPermissions(['user.view', 'user.create', 'user.edit', 'user.delete']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
});

test('datatable renders shift-click selection wiring', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('toggleItem(')
        ->assertSeeHtml('lastClickedIndex')
        ->assertSeeHtml('syncSelectedItemsToLivewire');
});

test('datatable renders sticky header page-scroll container', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('table-thead-sticky')
        ->assertSeeHtml('datatable-pagination-sticky');
});

test('unified page scroll is the default for every datatable', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(UserDatatable::class);

    expect($component->instance()->usesUnifiedPageScroll())->toBeTrue();

    $component
        ->assertSeeHtml('setupUnifiedPageScroll')
        ->assertSeeHtml('findOrAdoptPageHeader')
        ->assertSeeHtml("createElement('div')")
        ->assertSeeHtml("className = 'datatable-unified-scroll-header'")
        ->assertSeeHtml('datatable-page-scroll')
        ->assertSeeHtml('datatable-pagination-sticky')
        ->assertDontSeeHtml('datatable-scroll-area')
        ->assertDontSeeHtml("header.classList.add");
});

test('unified page scroll datatable enables horizontal scroll when the table overflows', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('class="datatable-table-scroll" wire:ignore.self')
        ->assertSeeHtml("querySelector('.datatable-table-scroll')")
        ->assertSeeHtml('class="datatable-scrollbar hidden" aria-hidden="true" tabindex="-1" wire:ignore')
        ->assertSeeHtml('setupTableHorizontalScroll')
        ->assertSeeHtml("'is-scrollable-x'")
        ->assertSeeHtml("icon.setAttribute('noobserver', '')")
        ->assertSeeHtml('icon.stopObserver?.()')
        ->assertSeeHtml('teardownTableHorizontalScroll');
});

test('datatable filters collapse into the filters panel when they do not fit the toolbar', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('data-filters-expanded')
        ->assertSeeHtml(":class=\"collapsed && 'md:invisible md:absolute md:top-0 md:right-0 md:pointer-events-none'\"")
        ->assertSeeHtml(':inert="collapsed"')
        ->assertSeeHtml('data-filters-collapsed')
        ->assertSeeHtml(":class=\"{ 'md:hidden': !collapsed, 'md:flex-none': collapsed }\"")
        ->assertSeeHtml('new ResizeObserver(() => this.fit())')
        ->assertSeeHtml('new MutationObserver(() => this.fit())')
        ->assertSeeHtml('<iconify-icon icon="lucide:chevron-down" class="transition-transform duration-200" :class="{\'rotate-180\': open}" noobserver>');
});

test('datatable filters and columns buttons share the row equally on phones', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('class="relative flex-1 min-w-0 md:flex-none"')
        ->assertSeeHtml('class="btn-default flex items-center justify-center gap-2 whitespace-nowrap w-full md:w-auto"')
        ->assertSeeHtml('class="btn-default flex items-center justify-center gap-2 w-full"');
});

test('datatable pagination keeps per page and previous/next on one row on phones', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertSeeHtml('datatable-pagination px-4 sm:px-6 flex flex-wrap sm:flex-nowrap items-center justify-between gap-x-4 gap-y-3')
        ->assertSeeHtml('class="text-sm text-gray-600 dark:text-gray-300 max-sm:sr-only"');
});

test('datatable per page and panel filters use the project dropdown instead of native selects', function () {
    $this->actingAs($this->admin);

    Livewire::test(UserDatatable::class)
        ->assertDontSeeHtml('<select')
        ->assertSeeHtml('id="perPage"')
        ->assertSeeHtml('id="perPage-listbox"')
        ->assertSeeHtml('bottom-full mb-1')
        ->assertSeeHtml('wire:click="$set(\'perPage\', 20)"')
        ->assertSeeHtml('id="mobile-filter-role"')
        ->assertSeeHtml('for="mobile-filter-role"')
        ->assertSeeHtml('wire:click="$set(\'role\', \'\')"')
        ->assertSeeHtml('class="form-control-combobox')
        ->assertSeeHtml('<span class="text-sm font-normal text-left truncate">10</span>')
        ->set('perPage', 20)
        ->assertSeeHtml('<span class="text-sm font-normal text-left truncate">20</span>');
});
