<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'mailbox_id', 'domain_id', 'message_id', 'direction', 'sender',
        'recipient', 'subject', 'size', 'status', 'dsn_code', 'error_message',
        'client_ip', 'server_ip', 'spam_score', 'is_spam', 'has_attachment', 'delivered_at',
    ];
    protected function casts(): array {
        return ['is_spam' => 'boolean', 'has_attachment' => 'boolean', 'delivered_at' => 'datetime', 'spam_score' => 'float'];
    }
    public function mailbox() { return $this->belongsTo(Mailbox::class); }
    public function domain() { return $this->belongsTo(Domain::class); }
    public function scopeInbound($q) { return $q->where('direction', 'inbound'); }
    public function scopeOutbound($q) { return $q->where('direction', 'outbound'); }
}
