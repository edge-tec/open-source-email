<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status',
        'max_domains', 'max_mailboxes', 'max_quota', 'avatar',
        'timezone', 'language', 'last_login_at', 'last_login_ip', 'parent_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function domains() { return $this->hasMany(Domain::class); }
    public function mailboxes() { return $this->hasMany(Mailbox::class); }
    public function parent() { return $this->belongsTo(User::class, 'parent_id'); }
    public function children() { return $this->hasMany(User::class, 'parent_id'); }
    public function twoFactorAuth() { return $this->hasOne(TwoFactorAuth::class); }

    // Scopes
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeRole($q, $role) { return $q->where('role', $role); }

    // Helpers
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool { return in_array($this->role, ['super_admin', 'admin']); }
    public function isReseller(): bool { return $this->role === 'reseller'; }

    public function canManageDomain(Domain $domain): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $domain->user_id === $this->id;
    }
}
