<?php

namespace App\Services\Mail;

use App\Models\Mailbox;
use Illuminate\Support\Collection;

class ImapService
{
    protected Mailbox $mailbox;
    protected $connection = null;
    protected string $host;
    protected int $port;
    protected string $encryption;

    public function __construct(Mailbox $mailbox)
    {
        $this->mailbox = $mailbox;
        $this->host = config('edgemail.imap.host', '127.0.0.1');
        $this->port = (int) config('edgemail.imap.port', 993);
        $this->encryption = config('edgemail.imap.encryption', 'ssl');
    }

    protected function connect(string $folder = 'INBOX'): void
    {
        $flags = '/imap/notls';
        if ($this->encryption === 'ssl') {
            $flags = '/imap/ssl/novalidate-cert';
        } elseif ($this->encryption === 'tls') {
            $flags = '/imap/tls/novalidate-cert';
        }
        $mailbox = "{{$this->host}:{$this->port}{$flags}}{$folder}";

        $password = session()->has('webmail_password') ? decrypt(session('webmail_password')) : $this->mailbox->password;

        $this->connection = @imap_open($mailbox, $this->mailbox->email, $password);

        if (!$this->connection) {
            throw new \RuntimeException('IMAP connection failed: ' . imap_last_error());
        }
    }

    protected function disconnect(): void
    {
        if ($this->connection) {
            imap_close($this->connection);
            $this->connection = null;
        }
    }

    public function getMessages(string $folder = 'INBOX', int $page = 1, int $perPage = 25): Collection
    {
        $this->connect($folder);

        $info = imap_check($this->connection);
        $total = $info->Nmsgs ?? 0;

        if ($total === 0) {
            $this->disconnect();
            return collect();
        }

        $start = max(1, $total - ($page * $perPage) + 1);
        $end = max(1, $total - (($page - 1) * $perPage));

        if ($start > $end) {
            $this->disconnect();
            return collect();
        }

        $messages = collect();
        $sequence = "{$start}:{$end}";
        $overviews = @imap_fetch_overview($this->connection, $sequence);

        if ($overviews) {
            foreach (array_reverse($overviews) as $overview) {
                $messages->push([
                    'uid' => $overview->uid ?? $overview->msgno,
                    'msgno' => $overview->msgno,
                    'subject' => isset($overview->subject) ? imap_utf8($overview->subject) : '(No Subject)',
                    'from' => isset($overview->from) ? imap_utf8($overview->from) : '',
                    'to' => isset($overview->to) ? imap_utf8($overview->to) : '',
                    'date' => $overview->date ?? '',
                    'size' => $overview->size ?? 0,
                    'seen' => (bool) ($overview->seen ?? false),
                    'flagged' => (bool) ($overview->flagged ?? false),
                    'answered' => (bool) ($overview->answered ?? false),
                ]);
            }
        }

        $this->disconnect();
        return $messages;
    }

    public function getMessage($uid, string $folder = 'INBOX'): array
    {
        $this->connect($folder);

        $msgno = imap_msgno($this->connection, $uid);
        if (!$msgno) {
            $msgno = $uid; // Fallback to message number
        }

        $header = imap_headerinfo($this->connection, $msgno);
        $structure = imap_fetchstructure($this->connection, $msgno);
        $body = $this->getBody($msgno, $structure);

        // Mark as read
        imap_setflag_full($this->connection, (string) $msgno, '\\Seen');

        $message = [
            'uid' => $uid,
            'msgno' => $msgno,
            'subject' => isset($header->subject) ? imap_utf8($header->subject) : '(No Subject)',
            'from' => $this->parseAddress($header->from ?? []),
            'to' => $this->parseAddress($header->to ?? []),
            'cc' => $this->parseAddress($header->cc ?? []),
            'date' => $header->date ?? '',
            'body_html' => $body['html'] ?? '',
            'body_text' => $body['text'] ?? '',
            'attachments' => $this->getAttachments($msgno, $structure),
        ];

        $this->disconnect();
        return $message;
    }

    protected function getBody(int $msgno, $structure): array
    {
        $body = ['html' => '', 'text' => ''];

        if (!isset($structure->parts) || empty($structure->parts)) {
            $content = imap_fetchbody($this->connection, $msgno, 1);
            if (isset($structure->encoding) && $structure->encoding == 3) $content = base64_decode($content);
            if (isset($structure->encoding) && $structure->encoding == 4) $content = quoted_printable_decode($content);
            
            if (isset($structure->subtype) && strtolower($structure->subtype) === 'html') {
                $body['html'] = $content;
            } else {
                $body['text'] = $content;
            }
            return $body;
        }

        foreach ($structure->parts as $partNum => $part) {
            $partNumber = $partNum + 1;
            if (isset($part->type) && $part->type == 0) {
                $content = imap_fetchbody($this->connection, $msgno, (string) $partNumber);
                if (isset($part->encoding) && $part->encoding == 3) $content = base64_decode($content);
                if (isset($part->encoding) && $part->encoding == 4) $content = quoted_printable_decode($content);

                if (isset($part->subtype) && strtolower($part->subtype) == 'html') {
                    $body['html'] = $content;
                } else {
                    $body['text'] = $content;
                }
            }
        }

        return $body;
    }

    protected function getAttachments(int $msgno, $structure): array
    {
        $attachments = [];
        if (!isset($structure->parts)) return $attachments;

        foreach ($structure->parts as $partNum => $part) {
            if (isset($part->ifdisposition) && $part->ifdisposition && isset($part->disposition) && strtolower($part->disposition) === 'attachment') {
                $filename = 'attachment';
                if (isset($part->ifdparameters) && $part->ifdparameters && isset($part->dparameters)) {
                    foreach ($part->dparameters as $param) {
                        if (isset($param->attribute) && strtolower($param->attribute) === 'filename') {
                            $filename = imap_utf8($param->value);
                        }
                    }
                }
                $attachments[] = [
                    'filename' => $filename,
                    'part' => $partNum + 1,
                    'size' => $part->bytes ?? 0,
                    'encoding' => $part->encoding ?? 0,
                ];
            }
        }

        return $attachments;
    }

    protected function parseAddress(array $addresses): string
    {
        $parsed = [];
        foreach ($addresses as $addr) {
            $email = ($addr->mailbox ?? '') . '@' . ($addr->host ?? '');
            $name = isset($addr->personal) ? imap_utf8($addr->personal) : '';
            $parsed[] = $name ? "{$name} <{$email}>" : $email;
        }
        return implode(', ', $parsed);
    }

    public function getFolders(): array
    {
        $this->connect();
        $flags = '/imap/notls';
        if ($this->encryption === 'ssl') {
            $flags = '/imap/ssl/novalidate-cert';
        } elseif ($this->encryption === 'tls') {
            $flags = '/imap/tls/novalidate-cert';
        }
        $ref = "{{$this->host}:{$this->port}{$flags}}";
        $list = imap_list($this->connection, $ref, '*') ?: [];

        $folders = [];
        foreach ($list as $folder) {
            $name = str_replace($ref, '', $folder);
            $name = imap_utf7_decode($name);
            $folders[] = $name;
        }

        $required = ['Sent', 'Junk', 'Trash', 'Drafts'];
        $added = false;
        foreach ($required as $req) {
            if (!in_array($req, $folders)) {
                $status = @imap_createmailbox($this->connection, imap_utf7_encode($ref . $req));
                if ($status) {
                    @imap_subscribe($this->connection, imap_utf7_encode($ref . $req));
                } else {
                    \Illuminate\Support\Facades\Log::error("Failed to create folder $req. IMAP Errors: " . print_r(imap_errors(), true));
                }
                $folders[] = $req;
                $added = true;
            }
        }
        if ($added) {
            sort($folders);
        }

        $this->disconnect();
        return $folders;
    }

    public function getUnreadCount(string $folder = 'INBOX'): int
    {
        $this->connect($folder);
        $status = imap_status($this->connection, "{{$this->host}:{$this->port}}{$folder}", SA_UNSEEN);
        $count = $status->unseen ?? 0;
        $this->disconnect();
        return $count;
    }

    public function search(string $query): Collection
    {
        $this->connect();
        $uids = imap_search($this->connection, "TEXT \"{$query}\"") ?: [];
        $messages = collect();

        foreach (array_reverse(array_slice($uids, 0, 50)) as $uid) {
            $overview = imap_fetch_overview($this->connection, (string) $uid);
            if ($overview) {
                $o = $overview[0];
                $messages->push([
                    'uid' => $o->uid ?? $o->msgno,
                    'subject' => isset($o->subject) ? imap_utf8($o->subject) : '(No Subject)',
                    'from' => isset($o->from) ? imap_utf8($o->from) : '',
                    'date' => $o->date ?? '',
                    'seen' => (bool) ($o->seen ?? false),
                ]);
            }
        }

        $this->disconnect();
        return $messages;
    }

    public function moveMessage($uid, string $targetFolder, string $sourceFolder = 'INBOX'): void
    {
        $this->connect($sourceFolder);
        imap_mail_move($this->connection, (string) $uid, $targetFolder, CP_UID);
        imap_expunge($this->connection);
        $this->disconnect();
    }

    public function deleteMessage($uid, string $sourceFolder = 'INBOX'): void
    {
        $this->connect($sourceFolder);
        imap_delete($this->connection, (string) $uid, FT_UID);
        imap_expunge($this->connection);
        $this->disconnect();
    }

    public function emptyFolder(string $folder): void
    {
        $this->connect($folder);
        // Mark all messages for deletion
        imap_delete($this->connection, '1:*');
        imap_expunge($this->connection);
        $this->disconnect();
    }

    public function toggleRead($uid, string $sourceFolder = 'INBOX'): void
    {
        $this->connect($sourceFolder);
        $msgno = imap_msgno($this->connection, $uid) ?: $uid;
        $header = imap_headerinfo($this->connection, $msgno);
        if ($header->Unseen === 'U' || $header->Recent === 'N') {
            imap_setflag_full($this->connection, (string) $msgno, '\\Seen');
        } else {
            imap_clearflag_full($this->connection, (string) $msgno, '\\Seen');
        }
        $this->disconnect();
    }

    public function createFolder(string $name): void
    {
        $this->connect();
        $flags = '/imap/notls';
        if ($this->encryption === 'ssl') {
            $flags = '/imap/ssl/novalidate-cert';
        } elseif ($this->encryption === 'tls') {
            $flags = '/imap/tls/novalidate-cert';
        }
        imap_createmailbox($this->connection, imap_utf7_encode("{{$this->host}:{$this->port}{$flags}}{$name}"));
        $this->disconnect();
    }

    public function deleteFolder(string $name): void
    {
        $this->connect();
        $flags = '/imap/notls';
        if ($this->encryption === 'ssl') {
            $flags = '/imap/ssl/novalidate-cert';
        } elseif ($this->encryption === 'tls') {
            $flags = '/imap/tls/novalidate-cert';
        }
        imap_deletemailbox($this->connection, imap_utf7_encode("{{$this->host}:{$this->port}{$flags}}{$name}"));
        $this->disconnect();
    }

    public function appendMessage(string $folder, string $messageString, string $flags = ''): bool
    {
        $this->connect();
        $connFlags = '/imap/notls';
        if ($this->encryption === 'ssl') {
            $connFlags = '/imap/ssl/novalidate-cert';
        } elseif ($this->encryption === 'tls') {
            $connFlags = '/imap/tls/novalidate-cert';
        }
        $mailboxRef = "{{$this->host}:{$this->port}{$connFlags}}{$folder}";
        $result = imap_append($this->connection, $mailboxRef, $messageString, $flags);
        $this->disconnect();
        return $result;
    }
}
