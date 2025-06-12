<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Nwidart\Modules\Facades\Module;

class ModuleTranslationMiddleware
{
    public function handle($request, Closure $next)
    {
        $locale = Session::get('locale', config('app.locale'));

        foreach (Module::all() as $module) {
            $jsonPath = $module->getPath()."/Resources/lang/{$locale}.json";

            if (File::exists($jsonPath)) {
                $translations = json_decode(File::get($jsonPath), true);

                if (is_array($translations)) {
                    foreach ($translations as $key => $value) {
                        app('translator')->getLoader()->addJsonPath(dirname($jsonPath));
                        break;
                    }
                }
            }
        }

        return $next($request);
    }
}
