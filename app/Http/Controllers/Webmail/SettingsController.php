<?php
namespace App\Http\Controllers\Webmail;
use App\Http\Controllers\Controller;
use App\Models\Autoresponder;
use App\Models\ForwardingRule;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index() {
        $mailbox = auth()->user()->getActiveMailbox();
        $autoresponder = $mailbox ? $mailbox->autoresponder : null;
        $forwarding = $mailbox ? $mailbox->forwardingRules : collect();
        $filters = $mailbox ? \App\Models\MailboxFilter::where('mailbox_id', $mailbox->id)->get() : collect();
        return view('webmail.settings', compact('mailbox', 'autoresponder', 'forwarding', 'filters'));
    }

    public function update(Request $request) {
        $mailbox = auth()->user()->getActiveMailbox();
        if (!$mailbox) return back()->withErrors(['No mailbox found.']);

        // Update signature and display name
        if ($request->has('signature') || $request->has('name')) {
            $updateData = [];
            if ($request->has('signature')) $updateData['signature'] = $request->signature;
            if ($request->has('name')) $updateData['name'] = $request->name;
            $mailbox->update($updateData);
        }

        // Update autoresponder
        if ($request->has('autoresponder_subject')) {
            Autoresponder::updateOrCreate(
                ['mailbox_id' => $mailbox->id],
                [
                    'subject' => $request->autoresponder_subject,
                    'body' => $request->autoresponder_body,
                    'status' => $request->boolean('autoresponder_active') ? 'active' : 'inactive',
                    'start_date' => $request->autoresponder_start,
                    'end_date' => $request->autoresponder_end,
                ]
            );
        }

        // Update forwarding
        if ($request->has('forwarding_destination')) {
            ForwardingRule::updateOrCreate(
                ['mailbox_id' => $mailbox->id],
                [
                    'destination' => $request->forwarding_destination,
                    'keep_copy' => $request->boolean('forwarding_keep_copy'),
                    'status' => $request->boolean('forwarding_active') ? 'active' : 'inactive',
                ]
            );
        }

        return back()->with('success', 'Settings updated.');
    }

    public function addFilter(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'criteria_field' => 'required|string',
            'criteria_operator' => 'required|string',
            'criteria_value' => 'required|string',
            'action' => 'required|string',
        ]);
        
        $mailbox = auth()->user()->getActiveMailbox();
        if (!$mailbox) return back()->withErrors(['No mailbox found.']);

        \App\Models\MailboxFilter::create(array_merge($request->all(), ['mailbox_id' => $mailbox->id]));

        return back()->with('success', 'Filter added successfully.');
    }

    public function destroyFilter($id) {
        $mailbox = auth()->user()->getActiveMailbox();
        if (!$mailbox) return back()->withErrors(['No mailbox found.']);

        $filter = \App\Models\MailboxFilter::where('mailbox_id', $mailbox->id)->findOrFail($id);
        $filter->delete();

        return back()->with('success', 'Filter removed.');
    }
}
