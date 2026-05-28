<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'domain', 'description', 'status', 'max_mailboxes',
        'max_aliases', 'max_quota', 'catch_all', 'dkim_enabled', 'dkim_selector',
        'dkim_private_key', 'dkim_public_key', 'spf_record', 'dmarc_record',
        'transport', 'is_backup_mx', 'verified_at',
    ];

    protected $hidden = ['dkim_private_key'];

    protected function casts(): array
    {
        return [
            'dkim_enabled' => 'boolean',
            'is_backup_mx' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function mailboxes() { return $this->hasMany(Mailbox::class); }
    public function aliases() { return $this->hasMany(Alias::class); }
    public function dkimKeys() { return $this->hasMany(DkimKey::class); }
    public function emailLogs() { return $this->hasMany(EmailLog::class); }
    public function spamFilters() { return $this->hasMany(SpamFilter::class); }

    public function scopeActive($q) { return $q->where('status', 'active'); }

    public function getMailboxCount(): int { return $this->mailboxes()->count(); }
    public function getAliasCount(): int { return $this->aliases()->count(); }
    public function getUsedQuota(): int
    {
        return $this->mailboxes()->sum('used_quota');
    }
}
