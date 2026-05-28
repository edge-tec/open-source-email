<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForwardingRule extends Model
{
    protected $fillable = ['mailbox_id', 'destination', 'keep_copy', 'status'];
    protected function casts(): array { return ['keep_copy' => 'boolean']; }
    public function mailbox() { return $this->belongsTo(Mailbox::class); }
}
