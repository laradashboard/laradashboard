<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Every test app starts with no modules enabled (see MODULES_STATUSES_FILE in
        // phpunit.xml). Only ever reset a throwaway file under storage/framework/.
        $statusesFile = getenv('MODULES_STATUSES_FILE');
        if (is_string($statusesFile) && str_starts_with($statusesFile, 'storage/framework/')) {
            $path = __DIR__.'/../'.$statusesFile;

            if (is_file($path)) {
                unlink($path);
            }
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
