<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mailbox = App\Models\Mailbox::first();
$host = config('edgemail.imap.host', '127.0.0.1');
$port = (int) config('edgemail.imap.port', 993);
$encryption = config('edgemail.imap.encryption', 'ssl');
$flags = $encryption === 'ssl' ? '/imap/ssl/novalidate-cert' : '/imap/notls';
$ref = "{{$host}:{$port}{$flags}}";
$connection = @imap_open($ref, $mailbox->email, $mailbox->password);
if(!$connection) die('Failed: ' . imap_last_error());
$list = imap_getmailboxes($connection, $ref, '*');
foreach($list as $l) {
    echo "Folder: " . $l->name . "\n";
}
imap_close($connection);
