<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxFilter extends Model
{
    protected $fillable = [
        'mailbox_id',
        'name',
        'criteria_field',
        'criteria_operator',
        'criteria_value',
        'action',
        'action_value',
        'status',
    ];

    public function mailbox()
    {
        return $this->belongsTo(Mailbox::class);
    }
}
