<?php

namespace App\Traits;

use App\Models\Mailbox;

trait HasActiveMailbox
{
    public function getActiveMailbox()
    {
        // If accessed via the webmail login portal
        if (session('webmail_auth') === true && session('active_mailbox_id')) {
            return Mailbox::where('id', session('active_mailbox_id'))->active()->first();
        }

        // If accessed via the admin panel
        if (auth()->check()) {
            $user = auth()->user();
            if (session()->has('active_mailbox_id')) {
                $mailbox = Mailbox::where('id', session('active_mailbox_id'))->active()->first();
                if ($mailbox && ($user->isAdmin() || $mailbox->user_id === $user->id)) {
                    return $mailbox;
                }
            }
            return $user->mailboxes()->active()->first();
        }

        return null;
    }
}
