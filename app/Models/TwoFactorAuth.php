<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auth';
    protected $fillable = ['user_id', 'secret', 'recovery_codes', 'is_enabled', 'confirmed_at', 'last_used_at'];
    protected $hidden = ['secret', 'recovery_codes'];
    protected function casts(): array {
        return ['is_enabled' => 'boolean', 'confirmed_at' => 'datetime', 'last_used_at' => 'datetime', 'recovery_codes' => 'array'];
    }
    public function user() { return $this->belongsTo(User::class); }
}
