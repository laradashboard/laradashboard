<?php

declare(strict_types=1);

namespace Modules\Squartup\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SquartupController extends Controller
{
    public function health(): View
    {
        return view('squartup::health');
    }
}
