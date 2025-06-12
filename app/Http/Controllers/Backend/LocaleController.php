<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class LocaleController extends Controller
{
    public function switch($lang): RedirectResponse
    {
        session()->put('locale', $lang);
        App::setLocale(session()->get('locale'));
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        session()->flash('success', 'Language changed successfully!');

        return redirect()->back();
    }
}
