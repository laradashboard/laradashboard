<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Squartup\Http\Controllers\SquartupController;

/*
|--------------------------------------------------------------------------
| Squartup web routes — Phase 1 foundation only
|--------------------------------------------------------------------------
|
| Do not add /admin, /dashboard, /profile, auth, /docs, or /{slug} here.
|
*/

Route::get('/squartup', [SquartupController::class, 'health'])->name('squartup.health');
