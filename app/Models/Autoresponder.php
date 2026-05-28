<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Autoresponder extends Model
{
    protected $fillable = ['mailbox_id', 'subject', 'body', 'is_html', 'status', 'start_date', 'end_date', 'interval_hours'];
    protected function casts(): array { return ['is_html' => 'boolean', 'start_date' => 'date', 'end_date' => 'date']; }
    public function mailbox() { return $this->belongsTo(Mailbox::class); }
    public function isActive(): bool {
        if ($this->status !== 'active') return false;
        $now = now()->toDateString();
        if ($this->start_date && $now < $this->start_date) return false;
        if ($this->end_date && $now > $this->end_date) return false;
        return true;
    }
}
