<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DkimKey extends Model
{
    protected $fillable = ['domain_id', 'selector', 'private_key', 'public_key', 'dns_record', 'key_bits', 'status'];
    protected $hidden = ['private_key'];
    public function domain() { return $this->belongsTo(Domain::class); }
}
