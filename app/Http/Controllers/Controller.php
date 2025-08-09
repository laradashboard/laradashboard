<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Concerns\AuthorizationChecker;
use App\Concerns\HasActionLogTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizationChecker;
    use AuthorizesRequests;
    use DispatchesJobs;
    use HasActionLogTrait;
    use ValidatesRequests;
}
