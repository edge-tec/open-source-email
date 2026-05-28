<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SpamFilter extends Model
{
    protected $fillable = ['mailbox_id', 'domain_id', 'filter_type', 'value', 'action', 'priority', 'status'];
    public function mailbox() { return $this->belongsTo(Mailbox::class); }
    public function domain() { return $this->belongsTo(Domain::class); }
}
