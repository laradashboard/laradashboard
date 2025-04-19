<?php

declare(strict_types=1);

namespace App\Services;

class EnvWriter
{
    public function write($key, $value)
    {
        // If the value didn't change, don't write it to the file.
        if ($this->get($key) === $value) {
            return;
        }

        $path = base_path('.env');
        $file = file_get_contents($path);

        // Wrap the value in double quotes.
        $formattedValue = "\"$value\"";

        $file = preg_replace("/^$key=.*/m", "$key=$formattedValue", $file);

        // If the key doesn't exist, append it
        if (!preg_match("/^$key=/m", $file)) {
            $file .= PHP_EOL . "$key=$formattedValue";
        }

        file_put_contents($path, $file);
    }

    public function get($key)
    {
        $path = base_path('.env');
        $file = file_get_contents($path);
        preg_match("/^$key=(.*)/m", $file, $matches);
        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    public function maybeWriteKeysToEnvFile($keys)
    {
        $availableKeys = $this->getAvailableKeys();

        // Stop if no keys are matching to availableKeys.
        if (empty($keys) || empty($availableKeys)) {
            return;
        }

        foreach ($keys as $key => $value) {
            if (array_key_exists($key, $availableKeys)) {
                $this->write($availableKeys[$key], (string) $value);
            }
        }
    }

    public function getAvailableKeys()
    {
        return ld_apply_filters('available_keys', [
            'app_name' => 'APP_NAME',
        ]);
    }
}