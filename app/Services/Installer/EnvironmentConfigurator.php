<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

class EnvironmentConfigurator
{
    /**
     * Generate and write the .env file from installation config.
     */
    public function configure(array $config): bool
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        // Start from example if .env doesn't exist
        if (!File::exists($envPath) && File::exists($examplePath)) {
            File::copy($examplePath, $envPath);
        }

        $replacements = [
            'APP_NAME' => $config['app_name'] ?? 'EdgeMail',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => $config['app_url'] ?? 'http://localhost',
            'APP_INSTALLED' => 'true',

            'DB_HOST' => $config['db_host'] ?? '127.0.0.1',
            'DB_PORT' => $config['db_port'] ?? '3306',
            'DB_DATABASE' => $config['db_name'] ?? 'edgemail',
            'DB_USERNAME' => $config['db_username'] ?? 'root',
            'DB_PASSWORD' => $config['db_password'] ?? '',

            'EDGEMAIL_DOMAIN' => $config['domain'] ?? 'example.com',
            'EDGEMAIL_HOSTNAME' => $config['hostname'] ?? ('mail.' . ($config['domain'] ?? 'example.com')),

            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => $config['smtp_host'] ?? '127.0.0.1',
            'MAIL_PORT' => $config['smtp_port'] ?? '587',
            'MAIL_ENCRYPTION' => $config['smtp_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => 'no-reply@' . ($config['domain'] ?? 'example.com'),
            'MAIL_FROM_NAME' => $config['app_name'] ?? 'EdgeMail',

            'IMAP_HOST' => $config['imap_host'] ?? '127.0.0.1',
            'IMAP_PORT' => $config['imap_port'] ?? '993',
            'IMAP_ENCRYPTION' => $config['imap_encryption'] ?? 'ssl',

            'REDIS_HOST' => $config['redis_host'] ?? '127.0.0.1',
            'REDIS_PORT' => $config['redis_port'] ?? '6379',

            'CACHE_STORE' => 'redis',
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
        ];

        $envContent = File::get($envPath);

        foreach ($replacements as $key => $value) {
            $envContent = $this->setEnvValue($envContent, $key, $value);
        }

        // Generate APP_KEY if not set
        if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=\n') !== false) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $envContent = $this->setEnvValue($envContent, 'APP_KEY', $key);
        }

        File::put($envPath, $envContent);

        return true;
    }

    /**
     * Set a value in the .env content string.
     */
    protected function setEnvValue(string $envContent, string $key, string $value): string
    {
        // Escape the value if it contains spaces
        if (preg_match('/\s/', $value) && !preg_match('/^".*"$/', $value)) {
            $value = '"' . $value . '"';
        }

        $pattern = "/^{$key}=.*/m";

        if (preg_match($pattern, $envContent)) {
            return preg_replace($pattern, "{$key}={$value}", $envContent);
        }

        return $envContent . "\n{$key}={$value}";
    }

    /**
     * Verify the .env file has all required keys.
     */
    public function verify(): array
    {
        $required = [
            'APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME',
            'EDGEMAIL_DOMAIN', 'MAIL_HOST', 'IMAP_HOST',
        ];

        $missing = [];
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return ['valid' => false, 'missing' => $required, 'message' => '.env file not found'];
        }

        $content = File::get($envPath);

        foreach ($required as $key) {
            if (!preg_match("/^{$key}=.+/m", $content)) {
                $missing[] = $key;
            }
        }

        return [
            'valid' => empty($missing),
            'missing' => $missing,
            'message' => empty($missing) ? 'All required keys present' : 'Missing: ' . implode(', ', $missing),
        ];
    }
}
