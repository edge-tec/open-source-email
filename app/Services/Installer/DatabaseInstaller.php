<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Setting;
use Exception;

class DatabaseInstaller
{
    protected JsonSchemaParser $parser;
    protected MigrationGenerator $migrationGenerator;
    protected array $log = [];

    public function __construct(JsonSchemaParser $parser, MigrationGenerator $migrationGenerator)
    {
        $this->parser = $parser;
        $this->migrationGenerator = $migrationGenerator;
    }

    /**
     * Run the full database installation process.
     */
    public function install(array $config): array
    {
        $steps = [
            'test_connection' => 'Testing database connection...',
            'generate_migrations' => 'Generating migrations from JSON schemas...',
            'run_migrations' => 'Running database migrations...',
            'seed_settings' => 'Seeding default settings...',
            'create_admin' => 'Creating admin account...',
            'configure_mail' => 'Configuring mail server settings...',
        ];

        $results = [];

        foreach ($steps as $step => $message) {
            $this->log[] = $message;

            try {
                $result = match ($step) {
                    'test_connection' => $this->testConnection($config),
                    'generate_migrations' => $this->generateMigrations(),
                    'run_migrations' => $this->runMigrations(),
                    'seed_settings' => $this->seedSettings($config),
                    'create_admin' => $this->createAdmin($config),
                    'configure_mail' => $this->configureMail($config),
                };

                $results[$step] = [
                    'success' => true,
                    'message' => $message . ' Done!',
                    'data' => $result,
                ];
            } catch (Exception $e) {
                $results[$step] = [
                    'success' => false,
                    'message' => $message . ' Failed!',
                    'error' => $e->getMessage(),
                ];

                // Stop on failure
                break;
            }
        }

        return [
            'success' => !collect($results)->contains('success', false),
            'steps' => $results,
            'log' => $this->log,
        ];
    }

    /**
     * Test the database connection.
     */
    public function testConnection(array $config): bool
    {
        try {
            config([
                'database.connections.mysql.host' => $config['db_host'] ?? '127.0.0.1',
                'database.connections.mysql.port' => $config['db_port'] ?? '3306',
                'database.connections.mysql.database' => $config['db_name'] ?? 'edgemail',
                'database.connections.mysql.username' => $config['db_username'] ?? 'root',
                'database.connections.mysql.password' => $config['db_password'] ?? '',
            ]);

            DB::connection('mysql')->reconnect();
            DB::connection('mysql')->getPdo();

            $this->log[] = '✓ Database connection successful';
            return true;
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate migration files from JSON schemas.
     */
    public function generateMigrations(): array
    {
        $generated = $this->migrationGenerator->generateAll();
        $this->log[] = '✓ Generated ' . count($generated) . ' migration files';
        return $generated;
    }

    /**
     * Run the database migrations.
     */
    public function runMigrations(): bool
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->log[] = '✓ All migrations executed successfully';
        return true;
    }

    /**
     * Seed default application settings.
     */
    public function seedSettings(array $config): bool
    {
        $settings = [
            ['group' => 'general', 'key' => 'app_name', 'value' => 'EdgeMail', 'type' => 'string'],
            ['group' => 'general', 'key' => 'app_url', 'value' => $config['app_url'] ?? 'http://localhost', 'type' => 'string'],
            ['group' => 'general', 'key' => 'main_domain', 'value' => $config['domain'] ?? 'example.com', 'type' => 'string'],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'UTC', 'type' => 'string'],
            ['group' => 'general', 'key' => 'language', 'value' => 'en', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'smtp_host', 'value' => $config['smtp_host'] ?? '127.0.0.1', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'smtp_port', 'value' => $config['smtp_port'] ?? '587', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'smtp_encryption', 'value' => $config['smtp_encryption'] ?? 'tls', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'imap_host', 'value' => $config['imap_host'] ?? '127.0.0.1', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'imap_port', 'value' => $config['imap_port'] ?? '993', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'imap_encryption', 'value' => $config['imap_encryption'] ?? 'ssl', 'type' => 'string'],
            ['group' => 'mail', 'key' => 'max_attachment_size', 'value' => '25', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'ban_duration', 'value' => '3600', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'two_factor_enabled', 'value' => 'true', 'type' => 'boolean'],
            ['group' => 'security', 'key' => 'rate_limit_per_minute', 'value' => '60', 'type' => 'integer'],
            ['group' => 'quota', 'key' => 'default_mailbox_quota', 'value' => '1024', 'type' => 'integer'],
            ['group' => 'quota', 'key' => 'default_domain_max_mailboxes', 'value' => '10', 'type' => 'integer'],
            ['group' => 'quota', 'key' => 'default_domain_max_aliases', 'value' => '50', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->log[] = '✓ Default settings seeded (' . count($settings) . ' entries)';
        return true;
    }

    /**
     * Create the super admin account.
     */
    public function createAdmin(array $config): array
    {
        $admin = DB::table('users')->updateOrInsert(
            ['email' => $config['admin_email']],
            [
                'name' => $config['admin_name'] ?? 'Super Admin',
                'email' => $config['admin_email'],
                'password' => Hash::make($config['admin_password']),
                'role' => 'super_admin',
                'status' => 'active',
                'max_domains' => 999,
                'max_mailboxes' => 9999,
                'max_quota' => 102400,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->log[] = '✓ Admin account created: ' . $config['admin_email'];

        return [
            'email' => $config['admin_email'],
            'role' => 'super_admin',
        ];
    }

    /**
     * Configure mail server settings in the database.
     */
    public function configureMail(array $config): bool
    {
        $domain = $config['domain'] ?? 'example.com';

        // Create the primary domain
        $userId = DB::table('users')->where('email', $config['admin_email'])->value('id');

        if ($userId) {
            DB::table('domains')->updateOrInsert(
                ['domain' => $domain],
                [
                    'user_id' => $userId,
                    'domain' => $domain,
                    'description' => 'Primary mail domain',
                    'status' => 'active',
                    'max_mailboxes' => 100,
                    'max_aliases' => 500,
                    'max_quota' => 102400,
                    'transport' => 'virtual',
                    'spf_record' => 'v=spf1 mx a ~all',
                    'dmarc_record' => "v=DMARC1; p=quarantine; rua=mailto:dmarc@{$domain}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->log[] = '✓ Primary domain configured: ' . $domain;
        return true;
    }

    /**
     * Get the installation log.
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
