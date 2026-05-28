<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

class MigrationGenerator
{
    protected JsonSchemaParser $parser;
    protected string $migrationPath;

    public function __construct(JsonSchemaParser $parser)
    {
        $this->parser = $parser;
        $this->migrationPath = database_path('migrations');
    }

    /**
     * Generate migration files for all JSON schemas.
     */
    public function generateAll(): array
    {
        $schemas = $this->parser->loadAll();
        $generated = [];

        // Clean existing auto-generated migrations
        $this->cleanAutoMigrations();

        $order = 0;
        foreach ($schemas as $tableName => $schema) {
            $order++;
            $filePath = $this->generateMigrationFile($schema, $order);
            $generated[] = [
                'table' => $tableName,
                'file' => basename($filePath),
                'path' => $filePath,
            ];
        }

        // Generate Sanctum personal_access_tokens migration
        $generated[] = $this->generateSanctumMigration($order + 1);

        // Generate Laravel sessions and cache tables
        $generated[] = $this->generateSessionsMigration($order + 2);
        $generated[] = $this->generateCacheMigration($order + 3);
        $generated[] = $this->generateJobsMigration($order + 4);

        return $generated;
    }

    /**
     * Generate a single migration file from a schema.
     */
    public function generateMigrationFile(array $schema, int $order): string
    {
        $content = $this->parser->generateMigrationContent($schema);
        $timestamp = date('Y_m_d') . '_' . str_pad($order, 6, '0', STR_PAD_LEFT);
        $fileName = "{$timestamp}_create_{$schema['table']}_table.php";
        $filePath = $this->migrationPath . '/' . $fileName;

        if (!File::isDirectory($this->migrationPath)) {
            File::makeDirectory($this->migrationPath, 0755, true);
        }

        File::put($filePath, $content);

        return $filePath;
    }

    /**
     * Remove previously auto-generated migrations.
     */
    protected function cleanAutoMigrations(): void
    {
        $files = File::glob($this->migrationPath . '/*_create_*_table.php');
        foreach ($files as $file) {
            File::delete($file);
        }
    }

    /**
     * Generate Sanctum personal_access_tokens migration.
     */
    protected function generateSanctumMigration(int $order): array
    {
        $timestamp = date('Y_m_d') . '_' . str_pad($order, 6, '0', STR_PAD_LEFT);
        $fileName = "{$timestamp}_create_personal_access_tokens_table.php";
        $filePath = $this->migrationPath . '/' . $fileName;

        $content = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
PHP;

        File::put($filePath, $content);

        return ['table' => 'personal_access_tokens', 'file' => $fileName, 'path' => $filePath];
    }

    /**
     * Generate sessions table migration.
     */
    protected function generateSessionsMigration(int $order): array
    {
        $timestamp = date('Y_m_d') . '_' . str_pad($order, 6, '0', STR_PAD_LEFT);
        $fileName = "{$timestamp}_create_sessions_table.php";
        $filePath = $this->migrationPath . '/' . $fileName;

        $content = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
PHP;

        File::put($filePath, $content);

        return ['table' => 'sessions', 'file' => $fileName, 'path' => $filePath];
    }

    /**
     * Generate cache table migration.
     */
    protected function generateCacheMigration(int $order): array
    {
        $timestamp = date('Y_m_d') . '_' . str_pad($order, 6, '0', STR_PAD_LEFT);
        $fileName = "{$timestamp}_create_cache_table.php";
        $filePath = $this->migrationPath . '/' . $fileName;

        $content = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
PHP;

        File::put($filePath, $content);

        return ['table' => 'cache', 'file' => $fileName, 'path' => $filePath];
    }

    /**
     * Generate jobs table migration.
     */
    protected function generateJobsMigration(int $order): array
    {
        $timestamp = date('Y_m_d') . '_' . str_pad($order, 6, '0', STR_PAD_LEFT);
        $fileName = "{$timestamp}_create_jobs_table.php";
        $filePath = $this->migrationPath . '/' . $fileName;

        $content = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
PHP;

        File::put($filePath, $content);

        return ['table' => 'jobs', 'file' => $fileName, 'path' => $filePath];
    }
}
