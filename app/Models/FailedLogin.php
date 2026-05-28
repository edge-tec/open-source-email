<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FailedLogin extends Model
{
    protected $fillable = ['ip_address', 'email', 'user_agent', 'service', 'is_banned', 'banned_until'];
    protected function casts(): array { return ['is_banned' => 'boolean', 'banned_until' => 'datetime']; }
}
