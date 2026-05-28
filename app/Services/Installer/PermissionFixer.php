<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

class PermissionFixer
{
    /**
     * Fix all required directory permissions.
     */
    public function fix(): array
    {
        $directories = [
            'storage' => '0775',
            'storage/app' => '0775',
            'storage/app/public' => '0775',
            'storage/framework' => '0775',
            'storage/framework/cache' => '0775',
            'storage/framework/cache/data' => '0775',
            'storage/framework/sessions' => '0775',
            'storage/framework/views' => '0775',
            'storage/logs' => '0775',
            'bootstrap/cache' => '0775',
        ];

        $results = [];

        foreach ($directories as $dir => $permission) {
            $path = base_path($dir);

            // Create directory if it doesn't exist
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, octdec($permission), true);
            }

            // Fix permissions
            $success = @chmod($path, octdec($permission));

            $results[] = [
                'path' => $dir,
                'permission' => $permission,
                'fixed' => $success,
                'writable' => is_writable($path),
            ];
        }

        // Create .gitignore files in storage directories
        $this->createGitIgnore(base_path('storage/app'), "*\n!public/\n!.gitignore");
        $this->createGitIgnore(base_path('storage/framework/cache/data'), "*\n!.gitignore");
        $this->createGitIgnore(base_path('storage/framework/sessions'), "*\n!.gitignore");
        $this->createGitIgnore(base_path('storage/framework/views'), "*\n!.gitignore");
        $this->createGitIgnore(base_path('storage/logs'), "*\n!.gitignore");

        return $results;
    }

    /**
     * Create a .gitignore file in a directory.
     */
    protected function createGitIgnore(string $path, string $content): void
    {
        $gitignore = $path . '/.gitignore';
        if (!File::exists($gitignore)) {
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0775, true);
            }
            File::put($gitignore, $content);
        }
    }

    /**
     * Check if all critical directories are writable.
     */
    public function allWritable(): bool
    {
        $critical = ['storage', 'storage/logs', 'bootstrap/cache'];

        foreach ($critical as $dir) {
            $path = base_path($dir);
            if (!is_dir($path) || !is_writable($path)) {
                return false;
            }
        }

        return true;
    }
}
