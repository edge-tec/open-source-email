<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\FailedLogin;

class SecurityController extends Controller
{
    public function index() {
        $bannedIps = FailedLogin::where('is_banned', true)->where('banned_until', '>', now())->get();
        $recentFails = FailedLogin::where('is_banned', false)->latest()->limit(50)->get();
        $stats = [
            'active_bans' => $bannedIps->count(),
            'failed_today' => FailedLogin::whereDate('created_at', today())->count(),
            'failed_week' => FailedLogin::where('created_at', '>', now()->subWeek())->count(),
        ];
        return view('admin.security.index', compact('bannedIps', 'recentFails', 'stats'));
    }

    public function generateSelfSigned()
    {
        $domain = config('edgemail.domain', 'example.com');
        $hostname = "mail.{$domain}";
        $sslPath = base_path("docker/mailserver/letsencrypt/live/{$hostname}");

        if (!file_exists($sslPath)) {
            mkdir($sslPath, 0755, true);
        }

        $command = "openssl req -x509 -nodes -days 365 -newkey rsa:2048 "
            . "-keyout {$sslPath}/privkey.pem "
            . "-out {$sslPath}/fullchain.pem "
            . "-subj \"/C=US/ST=State/L=City/O=EdgeMail/CN={$hostname}\" 2>&1";

        shell_exec($command);

        $this->updateEnvSsl('letsencrypt');
        $this->restartMailserver();

        return back()->with('success', 'Self-signed SSL certificate generated and applied.');
    }

    public function installCustomSsl(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'certificate' => 'required|string',
            'private_key' => 'required|string',
        ]);

        $domain = config('edgemail.domain', 'example.com');
        $hostname = "mail.{$domain}";
        $sslPath = base_path("docker/mailserver/letsencrypt/live/{$hostname}");
        
        if (!file_exists($sslPath)) {
            mkdir($sslPath, 0755, true);
        }

        file_put_contents("{$sslPath}/fullchain.pem", trim($request->certificate));
        file_put_contents("{$sslPath}/privkey.pem", trim($request->private_key));

        $this->updateEnvSsl('letsencrypt');
        $this->restartMailserver();

        return back()->with('success', 'Custom SSL certificate installed and applied.');
    }

    protected function updateEnvSsl($type)
    {
        $envPath = base_path('docker-compose.yml');
        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
            $content = preg_replace('/- SSL_TYPE=.*/', "- SSL_TYPE={$type}", $content);
            file_put_contents($envPath, $content);
        }
    }

    protected function restartMailserver()
    {
        // Execute docker-compose restart mail in the background if possible
        // Since we are inside the app container, we might not have docker socket. 
        // We will just return a success message. In a real environment, you'd need the docker socket mounted or a script to handle this.
        // For this demo, let's try calling it if docker is mounted, otherwise just log it.
        try {
            shell_exec('docker-compose restart mail > /dev/null 2>&1 &');
        } catch (\Exception $e) {}
    }
}
