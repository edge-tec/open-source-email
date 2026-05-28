<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alias extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['domain_id', 'source', 'destination', 'status', 'is_catchall', 'comment'];
    protected function casts(): array { return ['is_catchall' => 'boolean']; }
    public function domain() { return $this->belongsTo(Domain::class); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
