<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StorageUsage extends Model
{
    protected $table = 'storage_usage';
    protected $fillable = ['mailbox_id', 'domain_id', 'used_bytes', 'message_count', 'quota_bytes', 'percentage_used', 'last_calculated_at'];
    protected function casts(): array { return ['last_calculated_at' => 'datetime', 'percentage_used' => 'float']; }
    public function mailbox() { return $this->belongsTo(Mailbox::class); }
    public function domain() { return $this->belongsTo(Domain::class); }
}
