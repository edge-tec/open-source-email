<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\RequirementChecker;
use App\Services\Installer\PermissionFixer;
use App\Services\Installer\EnvironmentConfigurator;
use App\Services\Installer\DatabaseInstaller;
use App\Services\Installer\JsonSchemaParser;
use App\Services\Installer\MigrationGenerator;
use App\Services\Installer\MailServerConfigurator;
use App\Services\Installer\SslConfigurator;
use App\Services\Installer\CronJobInstaller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class InstallerController extends Controller
{
    protected RequirementChecker $requirements;
    protected PermissionFixer $permissions;
    protected EnvironmentConfigurator $envConfig;

    public function __construct(
        RequirementChecker $requirements,
        PermissionFixer $permissions,
        EnvironmentConfigurator $envConfig
    ) {
        $this->requirements = $requirements;
        $this->permissions = $permissions;
        $this->envConfig = $envConfig;
    }

    /**
     * Step 1: Welcome page.
     */
    public function welcome()
    {
        return view('installer.welcome');
    }

    /**
     * Step 2: Server requirements check.
     */
    public function requirements()
    {
        $checks = $this->requirements->check();
        $allMet = $this->requirements->allCriticalMet();

        return view('installer.requirements', compact('checks', 'allMet'));
    }

    /**
     * Step 3: Directory permissions.
     */
    public function permissions()
    {
        $permissions = $this->requirements->checkPermissions();
        $allWritable = $this->permissions->allWritable();

        return view('installer.permissions', compact('permissions', 'allWritable'));
    }

    /**
     * Fix permissions automatically.
     */
    public function fixPermissions()
    {
        $results = $this->permissions->fix();
        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Step 4: Database configuration.
     */
    public function database()
    {
        return view('installer.database');
    }

    /**
     * Test database connection via AJAX.
     */
    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|string',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
        ]);

        try {
            config([
                'database.connections.installer_test' => [
                    'driver' => 'mysql',
                    'host' => $request->db_host,
                    'port' => $request->db_port,
                    'database' => $request->db_name,
                    'username' => $request->db_username,
                    'password' => $request->db_password ?? '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
            ]);

            DB::connection('installer_test')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database connection successful!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 5: Mail server configuration.
     */
    public function mailConfig()
    {
        return view('installer.mail-config');
    }

    /**
     * Step 6: Admin account creation.
     */
    public function admin()
    {
        return view('installer.admin');
    }

    /**
     * Step 7: Run the installation.
     */
    public function install(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_name' => 'required',
            'db_username' => 'required',
            'domain' => 'required',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8',
        ]);

        $config = $request->all();

        try {
            // Step 1: Configure .env
            $this->envConfig->configure($config);

            // Step 2: Run database installation
            $parser = new JsonSchemaParser();
            $migrationGen = new MigrationGenerator($parser);
            $dbInstaller = new DatabaseInstaller($parser, $migrationGen);
            $result = $dbInstaller->install($config);

            if (!$result['success']) {
                return response()->json($result, 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully!',
                'log' => $result['log'],
                'redirect' => route('installer.complete'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Step 8: Installation complete.
     */
    public function complete()
    {
        return view('installer.complete');
    }
}
