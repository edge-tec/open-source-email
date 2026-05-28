<?php

namespace App\Services\Installer;

class RequirementChecker
{
    /**
     * Check all system requirements.
     */
    public function check(): array
    {
        return [
            'php' => $this->checkPhp(),
            'extensions' => $this->checkExtensions(),
            'permissions' => $this->checkPermissions(),
            'functions' => $this->checkFunctions(),
            'server' => $this->checkServerRequirements(),
        ];
    }

    /**
     * Check PHP version requirement.
     */
    public function checkPhp(): array
    {
        $current = PHP_VERSION;
        $required = '8.3.0';
        $satisfied = version_compare($current, $required, '>=');

        return [
            'name' => 'PHP Version',
            'required' => '>= ' . $required,
            'current' => $current,
            'satisfied' => $satisfied,
            'critical' => true,
        ];
    }

    /**
     * Check required PHP extensions.
     */
    public function checkExtensions(): array
    {
        $required = config('edgemail.required_extensions', [
            'pdo', 'pdo_mysql', 'openssl', 'imap', 'mbstring',
            'bcmath', 'json', 'zip', 'curl', 'xml', 'fileinfo',
            'tokenizer',
        ]);

        $results = [];
        foreach ($required as $ext) {
            $loaded = extension_loaded($ext);
            $results[] = [
                'name' => strtoupper($ext),
                'loaded' => $loaded,
                'critical' => in_array($ext, ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'json']),
            ];
        }

        return $results;
    }

    /**
     * Check directory permissions.
     */
    public function checkPermissions(): array
    {
        $directories = config('edgemail.writable_directories', [
            'storage/app',
            'storage/framework',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ]);

        $results = [];
        foreach ($directories as $dir) {
            $path = base_path($dir);
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);

            $results[] = [
                'path' => $dir,
                'exists' => $exists,
                'writable' => $writable,
                'satisfied' => $writable,
            ];
        }

        return $results;
    }

    /**
     * Check required PHP functions are not disabled.
     */
    public function checkFunctions(): array
    {
        $required = ['exec', 'proc_open', 'proc_close', 'symlink'];
        $disabled = explode(',', ini_get('disable_functions'));
        $disabled = array_map('trim', $disabled);

        $results = [];
        foreach ($required as $func) {
            $available = !in_array($func, $disabled) && function_exists($func);
            $results[] = [
                'name' => $func . '()',
                'available' => $available,
                'critical' => false,
            ];
        }

        return $results;
    }

    /**
     * Check server software requirements.
     */
    public function checkServerRequirements(): array
    {
        return [
            [
                'name' => 'Memory Limit',
                'required' => '128M',
                'current' => ini_get('memory_limit'),
                'satisfied' => $this->parseBytes(ini_get('memory_limit')) >= $this->parseBytes('128M'),
            ],
            [
                'name' => 'Max Execution Time',
                'required' => '60',
                'current' => ini_get('max_execution_time'),
                'satisfied' => ini_get('max_execution_time') >= 60 || ini_get('max_execution_time') == 0,
            ],
            [
                'name' => 'Upload Max Filesize',
                'required' => '25M',
                'current' => ini_get('upload_max_filesize'),
                'satisfied' => $this->parseBytes(ini_get('upload_max_filesize')) >= $this->parseBytes('25M'),
            ],
            [
                'name' => 'Post Max Size',
                'required' => '26M',
                'current' => ini_get('post_max_size'),
                'satisfied' => $this->parseBytes(ini_get('post_max_size')) >= $this->parseBytes('26M'),
            ],
        ];
    }

    /**
     * Check if all critical requirements are met.
     */
    public function allCriticalMet(): bool
    {
        $checks = $this->check();

        // PHP version must be met
        if (!$checks['php']['satisfied']) {
            return false;
        }

        // All critical extensions must be loaded
        foreach ($checks['extensions'] as $ext) {
            if ($ext['critical'] && !$ext['loaded']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse a PHP ini byte value to bytes.
     */
    protected function parseBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }

        return $value;
    }
}
