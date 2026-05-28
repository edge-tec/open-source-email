<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mailbox extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'domain_id', 'local_part', 'email', 'password', 'name',
        'quota', 'used_quota', 'status', 'maildir', 'signature',
        'is_catchall', 'send_only', 'last_login_at', 'last_login_ip', 'msg_count',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'is_catchall' => 'boolean',
            'send_only' => 'boolean',
            'last_login_at' => 'datetime',
            'quota' => 'integer',
            'used_quota' => 'integer',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function domain() { return $this->belongsTo(Domain::class); }
    public function forwardingRules() { return $this->hasMany(ForwardingRule::class); }
    public function autoresponder() { return $this->hasOne(Autoresponder::class); }
    public function emailLogs() { return $this->hasMany(EmailLog::class); }
    public function storageUsage() { return $this->hasOne(StorageUsage::class); }
    public function spamFilters() { return $this->hasMany(SpamFilter::class); }

    public function scopeActive($q) { return $q->where('status', 'active'); }

    public function getQuotaPercentage(): float
    {
        if ($this->quota <= 0) return 0;
        return round(($this->used_quota / ($this->quota * 1048576)) * 100, 2);
    }

    public function getMaildirPath(): string
    {
        return $this->maildir ?: $this->domain->domain . '/' . $this->local_part . '/Maildir/';
    }
}
