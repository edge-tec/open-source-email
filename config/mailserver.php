<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mail Server Configuration
    |--------------------------------------------------------------------------
    */

    'postfix' => [
        'config_path' => env('POSTFIX_CONFIG_PATH', '/etc/postfix'),
        'main_cf' => env('POSTFIX_MAIN_CF', '/etc/postfix/main.cf'),
        'master_cf' => env('POSTFIX_MASTER_CF', '/etc/postfix/master.cf'),
        'virtual_mailbox_base' => env('POSTFIX_VMAIL_BASE', '/var/vmail'),
        'message_size_limit' => env('POSTFIX_MESSAGE_SIZE', 26214400), // 25MB
        'smtpd_tls_cert_file' => env('SSL_CERT_PATH', '/etc/ssl/certs/edgemail.pem'),
        'smtpd_tls_key_file' => env('SSL_KEY_PATH', '/etc/ssl/private/edgemail.key'),
    ],

    'dovecot' => [
        'config_path' => env('DOVECOT_CONFIG_PATH', '/etc/dovecot'),
        'mail_location' => env('DOVECOT_MAIL_LOCATION', 'maildir:/var/vmail/%d/%n/Maildir'),
        'protocols' => ['imap', 'pop3', 'lmtp'],
        'auth_mechanisms' => ['plain', 'login'],
        'ssl_cert' => env('SSL_CERT_PATH', '/etc/ssl/certs/edgemail.pem'),
        'ssl_key' => env('SSL_KEY_PATH', '/etc/ssl/private/edgemail.key'),
    ],

    'rspamd' => [
        'config_path' => env('RSPAMD_CONFIG_PATH', '/etc/rspamd'),
        'host' => env('RSPAMD_HOST', '127.0.0.1'),
        'port' => env('RSPAMD_PORT', 11334),
        'web_port' => env('RSPAMD_WEB_PORT', 11334),
        'password' => env('RSPAMD_PASSWORD', ''),
    ],

    'dkim' => [
        'selector' => env('DKIM_SELECTOR', 'edgemail'),
        'key_bits' => 2048,
        'key_path' => env('DKIM_KEY_PATH', '/var/lib/rspamd/dkim'),
    ],

    'dns' => [
        'spf_record' => 'v=spf1 mx a ~all',
        'dmarc_record' => 'v=DMARC1; p=quarantine; rua=mailto:dmarc@{domain}; ruf=mailto:dmarc@{domain}; fo=1',
    ],
];
