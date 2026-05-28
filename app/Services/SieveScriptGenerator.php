<?php

namespace App\Services;

use App\Models\Mailbox;
use App\Models\MailboxFilter;

class SieveScriptGenerator
{
    /**
     * Generate the complete Sieve script for a mailbox.
     */
    public function generate(Mailbox $mailbox): string
    {
        $script = "require [\"fileinto\", \"reject\", \"vacation\", \"envelope\", \"body\", \"relational\", \"comparator-i;ascii-numeric\", \"mailbox\"];\n\n";

        // 1. Forwarding Rules
        $forwardingRules = $mailbox->forwardingRules()->where('status', 'active')->get();
        foreach ($forwardingRules as $fw) {
            $script .= "redirect \"" . addslashes($fw->destination) . "\";\n";
            if (!$fw->keep_copy) {
                $script .= "discard;\nstop;\n";
            }
        }

        // 2. Mailbox Filters
        $filters = MailboxFilter::where('mailbox_id', $mailbox->id)->get();
        foreach ($filters as $filter) {
            $field = strtolower($filter->criteria_field); // 'from', 'subject', 'body'
            $op = strtolower($filter->criteria_operator); // 'contains', 'exact'
            $val = addslashes($filter->criteria_value);
            $action = strtolower($filter->action);

            if ($field === 'body') {
                $condition = "body :text " . ($op === 'exact' ? ":is" : ":contains") . " \"{$val}\"";
            } else {
                // Ensure proper header capitalization (From, Subject)
                $headerName = ucfirst($field); 
                $condition = "header " . ($op === 'exact' ? ":is" : ":contains") . " \"{$headerName}\" \"{$val}\"";
            }

            $script .= "if {$condition} {\n";
            
            if ($action === 'delete') {
                $script .= "    discard;\n    stop;\n";
            } elseif ($action === 'move_trash') {
                $script .= "    fileinto \"Trash\";\n    stop;\n";
            } elseif ($action === 'move_spam') {
                $script .= "    fileinto \"Junk\";\n    stop;\n";
            }
            $script .= "}\n\n";
        }

        // 3. Autoresponder (Vacation)
        $auto = $mailbox->autoresponder;
        if ($auto && $auto->status === 'active') {
            $now = now();
            $start = $auto->start_date ? \Carbon\Carbon::parse($auto->start_date) : null;
            $end = $auto->end_date ? \Carbon\Carbon::parse($auto->end_date) : null;

            if ((!$start || $now->gte($start)) && (!$end || $now->lte($end))) {
                $subject = addslashes($auto->subject ?? 'Auto-Reply');
                // Sieve string formatting
                $body = str_replace(["\r\n", "\n"], "\\n", addslashes($auto->body));
                
                $script .= "vacation :days 1 :subject \"{$subject}\" \"{$body}\";\n\n";
            }
        }

        return $script;
    }
}
