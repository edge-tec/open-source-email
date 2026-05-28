<?php

namespace App\Services\Installer;

class MailServerConfigurator
{
    /**
     * Generate Postfix configuration for virtual mailbox setup.
     */
    public function generatePostfixConfig(array $config): array
    {
        $domain = $config['domain'] ?? 'example.com';
        $hostname = $config['hostname'] ?? "mail.{$domain}";
        $dbHost = $config['db_host'] ?? '127.0.0.1';
        $dbName = $config['db_name'] ?? 'edgemail';
        $dbUser = $config['db_username'] ?? 'root';
        $dbPass = $config['db_password'] ?? '';

        $mainCf = <<<CONF
# EdgeMail Postfix Configuration
# Generated automatically during installation

smtpd_banner = \$myhostname ESMTP EdgeMail
biff = no
append_dot_mydomain = no
readme_directory = no

# TLS parameters
smtpd_tls_cert_file = /etc/ssl/certs/edgemail.pem
smtpd_tls_key_file = /etc/ssl/private/edgemail.key
smtpd_use_tls = yes
smtpd_tls_auth_only = yes
smtpd_tls_security_level = may
smtpd_tls_protocols = !SSLv2, !SSLv3, !TLSv1, !TLSv1.1
smtp_tls_security_level = may
smtp_tls_protocols = !SSLv2, !SSLv3, !TLSv1, !TLSv1.1

# SASL Authentication
smtpd_sasl_type = dovecot
smtpd_sasl_path = private/auth
smtpd_sasl_auth_enable = yes
smtpd_sasl_security_options = noanonymous
smtpd_sasl_local_domain = \$myhostname

# Network
myhostname = {$hostname}
mydomain = {$domain}
myorigin = \$mydomain
mydestination = localhost
mynetworks = 127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128
mailbox_size_limit = 0
recipient_delimiter = +
inet_interfaces = all
inet_protocols = all

# Virtual Mailbox
virtual_transport = lmtp:unix:private/dovecot-lmtp
virtual_mailbox_domains = mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
virtual_mailbox_maps = mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
virtual_alias_maps = mysql:/etc/postfix/mysql-virtual-alias-maps.cf

# Milter (Rspamd)
milter_protocol = 6
milter_default_action = accept
smtpd_milters = inet:localhost:11332
non_smtpd_milters = \$smtpd_milters

# Restrictions
smtpd_helo_required = yes
smtpd_helo_restrictions =
    permit_mynetworks,
    reject_non_fqdn_helo_hostname,
    reject_invalid_helo_hostname,
    permit

smtpd_sender_restrictions =
    permit_mynetworks,
    reject_non_fqdn_sender,
    reject_unknown_sender_domain,
    permit

smtpd_recipient_restrictions =
    permit_mynetworks,
    permit_sasl_authenticated,
    reject_unauth_destination,
    reject_non_fqdn_recipient,
    reject_unknown_recipient_domain,
    permit

# Message size limit (25MB)
message_size_limit = 26214400
CONF;

        $virtualDomains = <<<CONF
user = {$dbUser}
password = {$dbPass}
hosts = {$dbHost}
dbname = {$dbName}
query = SELECT domain FROM domains WHERE domain='%s' AND status='active'
CONF;

        $virtualMailboxMaps = <<<CONF
user = {$dbUser}
password = {$dbPass}
hosts = {$dbHost}
dbname = {$dbName}
query = SELECT CONCAT(d.domain, '/', m.local_part, '/Maildir/') FROM mailboxes m JOIN domains d ON m.domain_id = d.id WHERE m.email='%s' AND m.status='active'
CONF;

        $virtualAliasMaps = <<<CONF
user = {$dbUser}
password = {$dbPass}
hosts = {$dbHost}
dbname = {$dbName}
query = SELECT destination FROM aliases WHERE source='%s' AND status='active'
CONF;

        return [
            'main.cf' => $mainCf,
            'mysql-virtual-mailbox-domains.cf' => $virtualDomains,
            'mysql-virtual-mailbox-maps.cf' => $virtualMailboxMaps,
            'mysql-virtual-alias-maps.cf' => $virtualAliasMaps,
        ];
    }

    /**
     * Generate Dovecot configuration.
     */
    public function generateDovecotConfig(array $config): array
    {
        $dbHost = $config['db_host'] ?? '127.0.0.1';
        $dbName = $config['db_name'] ?? 'edgemail';
        $dbUser = $config['db_username'] ?? 'root';
        $dbPass = $config['db_password'] ?? '';

        $dovecotConf = <<<CONF
# EdgeMail Dovecot Configuration

protocols = imap pop3 lmtp sieve

# Logging
log_path = /var/log/dovecot/dovecot.log
info_log_path = /var/log/dovecot/dovecot-info.log

# Mail location
mail_location = maildir:/var/vmail/%d/%n/Maildir
mail_uid = 5000
mail_gid = 5000
mail_privileged_group = vmail
first_valid_uid = 5000
last_valid_uid = 5000

# Authentication
auth_mechanisms = plain login
disable_plaintext_auth = yes

passdb {
    driver = sql
    args = /etc/dovecot/dovecot-sql.conf.ext
}

userdb {
    driver = sql
    args = /etc/dovecot/dovecot-sql.conf.ext
}

# SSL
ssl = required
ssl_cert = </etc/ssl/certs/edgemail.pem
ssl_key = </etc/ssl/private/edgemail.key
ssl_min_protocol = TLSv1.2
ssl_prefer_server_ciphers = yes

# Services
service imap-login {
    inet_listener imap {
        port = 143
    }
    inet_listener imaps {
        port = 993
        ssl = yes
    }
}

service pop3-login {
    inet_listener pop3 {
        port = 110
    }
    inet_listener pop3s {
        port = 995
        ssl = yes
    }
}

service lmtp {
    unix_listener /var/spool/postfix/private/dovecot-lmtp {
        mode = 0600
        user = postfix
        group = postfix
    }
}

service auth {
    unix_listener /var/spool/postfix/private/auth {
        mode = 0666
        user = postfix
        group = postfix
    }
    unix_listener auth-userdb {
        mode = 0600
        user = vmail
    }
}

# Mailbox configuration
namespace inbox {
    inbox = yes

    mailbox Drafts {
        auto = subscribe
        special_use = \\Drafts
    }
    mailbox Sent {
        auto = subscribe
        special_use = \\Sent
    }
    mailbox "Sent Messages" {
        special_use = \\Sent
    }
    mailbox Spam {
        auto = subscribe
        special_use = \\Junk
    }
    mailbox Trash {
        auto = subscribe
        special_use = \\Trash
    }
    mailbox Archive {
        auto = no
        special_use = \\Archive
    }
}

# Quotas
plugin {
    quota = maildir:User quota
    quota_rule = *:storage=1G
    quota_warning = storage=95%% quota-warning 95 %u
    quota_warning2 = storage=80%% quota-warning 80 %u
}

protocol imap {
    mail_plugins = \$mail_plugins quota imap_quota
}
CONF;

        $dovecotSql = <<<CONF
driver = mysql
connect = host={$dbHost} dbname={$dbName} user={$dbUser} password={$dbPass}

default_pass_scheme = BLF-CRYPT

password_query = SELECT email AS user, password FROM mailboxes WHERE email='%u' AND status='active'

user_query = SELECT CONCAT('/var/vmail/', d.domain, '/', m.local_part) AS home, 5000 AS uid, 5000 AS gid, CONCAT('*:bytes=', m.quota * 1048576) AS quota_rule FROM mailboxes m JOIN domains d ON m.domain_id = d.id WHERE m.email='%u' AND m.status='active'

iterate_query = SELECT email AS user FROM mailboxes WHERE status='active'
CONF;

        return [
            'dovecot.conf' => $dovecotConf,
            'dovecot-sql.conf.ext' => $dovecotSql,
        ];
    }
}
