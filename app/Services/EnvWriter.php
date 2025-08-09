<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActionType;
use App\Concerns\HasActionLogTrait;

class EnvWriter
{
    use HasActionLogTrait;

    public function write($key, $value): void
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
        if (! preg_match("/^$key=/m", $file)) {
            $file .= PHP_EOL."$key=$formattedValue";
        }

        // Use file locking to prevent race conditions
        $fp = fopen($path, 'c+');
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            fwrite($fp, $file);
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    public function get($key)
    {
        $path = base_path('.env');
        $file = file_get_contents($path);
        preg_match("/^$key=(.*)/m", $file, $matches);

        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    public function maybeWriteKeysToEnvFile($keys): void
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

    public function batchWriteKeysToEnvFile(array $keys): void
    {
        try {
            $availableKeys = $this->getAvailableKeys();

            if (empty($keys) || empty($availableKeys)) {
                return;
            }

            $path = base_path('.env');
            $file = file_get_contents($path);

            $changesMade = false;

            foreach ($keys as $key => $value) {
                if (array_key_exists($key, $availableKeys)) {
                    $envKey = $availableKeys[$key];
                    $currentValue = $this->get($envKey);

                    // Normalize the current value by stripping surrounding quotes
                    $normalizedCurrentValue = trim($currentValue, '"');

                    // Skip writing if the normalized value hasn't changed or is null
                    if ($normalizedCurrentValue === (string) $value || $value === null) {
                        continue;
                    }

                    $formattedValue = "\"$value\"";
                    $file = preg_replace("/^$envKey=.*/m", "$envKey=$formattedValue", $file);

                    if (! preg_match("/^$envKey=/m", $file)) {
                        $file .= PHP_EOL."$envKey=$formattedValue";
                    }

                    $changesMade = true;
                }
            }

            // Write to the file only if changes were made
            if ($changesMade) {
                $fp = fopen($path, 'c+');
                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, 0);
                    fwrite($fp, $file);
                    fflush($fp);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }
        } catch (\Throwable $th) {
            $this->storeActionLog(ActionType::EXCEPTION, [
                'env_update_error' => $th->getMessage(),
            ]);
        }
    }
}
