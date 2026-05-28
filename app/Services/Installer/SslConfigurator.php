<?php

namespace App\Services\Installer;

class SslConfigurator
{
    /**
     * Setup SSL certificate using Let's Encrypt.
     */
    public function setupLetsEncrypt(string $domain, string $email): array
    {
        $hostname = "mail.{$domain}";
        $certPath = "/etc/letsencrypt/live/{$hostname}";

        $commands = [
            "certbot certonly --standalone --preferred-challenges http -d {$hostname} --email {$email} --agree-tos --non-interactive",
            "ln -sf {$certPath}/fullchain.pem /etc/ssl/certs/edgemail.pem",
            "ln -sf {$certPath}/privkey.pem /etc/ssl/private/edgemail.key",
        ];

        return [
            'commands' => $commands,
            'cert_path' => "{$certPath}/fullchain.pem",
            'key_path' => "{$certPath}/privkey.pem",
            'symlink_cert' => '/etc/ssl/certs/edgemail.pem',
            'symlink_key' => '/etc/ssl/private/edgemail.key',
        ];
    }

    /**
     * Generate a self-signed certificate for development.
     */
    public function generateSelfSigned(string $domain): array
    {
        $hostname = "mail.{$domain}";

        $command = "openssl req -x509 -nodes -days 365 -newkey rsa:2048 "
            . "-keyout /etc/ssl/private/edgemail.key "
            . "-out /etc/ssl/certs/edgemail.pem "
            . "-subj \"/C=US/ST=State/L=City/O=EdgeMail/CN={$hostname}\"";

        return [
            'command' => $command,
            'cert_path' => '/etc/ssl/certs/edgemail.pem',
            'key_path' => '/etc/ssl/private/edgemail.key',
            'type' => 'self-signed',
            'expires_days' => 365,
        ];
    }

    /**
     * Generate certbot auto-renewal cron entry.
     */
    public function getRenewalCron(): string
    {
        return '0 0,12 * * * root certbot renew --quiet --post-hook "systemctl reload postfix dovecot nginx"';
    }
}
