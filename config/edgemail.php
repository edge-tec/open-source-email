<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EdgeMail Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'EdgeMail'),
    'version' => '1.0.0',
    'hostname' => env('EDGEMAIL_HOSTNAME', 'mail.example.com'),
    'domain' => env('EDGEMAIL_DOMAIN', 'example.com'),

    // Installation status
    'installed' => env('APP_INSTALLED', false),

    // Storage defaults
    'default_mailbox_quota' => env('EDGEMAIL_DEFAULT_QUOTA', 1024), // MB
    'max_attachment_size' => env('EDGEMAIL_MAX_ATTACHMENT', 25), // MB

    // Mail server paths
    'vmail_path' => env('EDGEMAIL_VMAIL_PATH', '/var/vmail'),
    'vmail_uid' => env('EDGEMAIL_VMAIL_UID', 5000),
    'vmail_gid' => env('EDGEMAIL_VMAIL_GID', 5000),

    // Security
    'max_login_attempts' => env('FAIL2BAN_MAX_ATTEMPTS', 5),
    'ban_duration' => env('FAIL2BAN_BAN_DURATION', 3600),
    'two_factor_enabled' => env('EDGEMAIL_2FA_ENABLED', true),

    // SSL
    'ssl' => [
        'enabled' => env('SSL_ENABLED', false),
        'cert_path' => env('SSL_CERT_PATH', '/etc/ssl/certs/edgemail.pem'),
        'key_path' => env('SSL_KEY_PATH', '/etc/ssl/private/edgemail.key'),
        'auto_renew' => env('SSL_AUTO_RENEW', true),
    ],

    // Rspamd
    'rspamd' => [
        'host' => env('RSPAMD_HOST', '127.0.0.1'),
        'port' => env('RSPAMD_PORT', 11334),
        'password' => env('RSPAMD_PASSWORD', ''),
    ],

    // IMAP
    'imap' => [
        'host' => env('IMAP_HOST', '127.0.0.1'),
        'port' => env('IMAP_PORT', 993),
        'encryption' => env('IMAP_ENCRYPTION', 'ssl'),
    ],

    // Roles
    'roles' => [
        'super_admin' => 'super_admin',
        'admin' => 'admin',
        'reseller' => 'reseller',
        'user' => 'user',
    ],

    // JSON schema path
    'json_schema_path' => database_path('json'),

    // Required PHP extensions
    'required_extensions' => [
        'pdo', 'pdo_mysql', 'openssl', 'imap', 'mbstring',
        'bcmath', 'json', 'zip', 'curl', 'xml', 'fileinfo',
        'tokenizer', 'redis',
    ],

    // Required writable directories
    'writable_directories' => [
        'storage/app',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ],
];
