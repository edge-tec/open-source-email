<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QueueLog extends Model
{
    protected $fillable = ['queue_id', 'sender', 'recipient', 'subject', 'size', 'status', 'attempts', 'next_retry_at', 'error_message'];
    protected function casts(): array { return ['next_retry_at' => 'datetime']; }
}
