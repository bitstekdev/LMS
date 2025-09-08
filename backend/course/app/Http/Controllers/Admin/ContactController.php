<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ContactController extends Controller
{
    /**
     * Display the contact messages.
     */
    public function index(Request $request)
    {
        // Mark all unread contacts as read
        Contact::whereNull('has_read')->update(['has_read' => 1]);

        // Search filter
        if ($request->search) {
            $contacts = Contact::where('email', 'like', "%{$request->search}%")
                ->orWhere('address', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('message', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%")
                ->paginate(20);
        } else {
            $contacts = Contact::paginate(20);
        }

        return view('admin.contact.index', ['contacts' => $contacts]);
    }

    /**
     * Delete a contact message by ID.
     */
    public function contact_delete($id)
    {
        Contact::destroy($id);

        Session::flash('success', get_phrase('Contact deleted successfully'));

        return redirect()->back();
    }

    /**
     * Reply to a contact message via email.
     */
    public function reply(Request $request)
    {
        $contact = Contact::find($request->send_to);

        if (! $contact) {
            Session::flash('error', get_phrase('Contact not found.'));

            return redirect()->route('admin.contacts');
        }

        // Send plain text reply email
        Mail::raw($request->reply_message, function ($message) use ($contact, $request) {
            $message->to($contact->email)
                ->subject($request->subject ?? get_settings('system_title'));
        });

        // Update contact as replied
        $contact->update(['replied' => 1]);

        Session::flash('success', get_phrase('Email sent successfully'));

        return redirect()->route('admin.contacts');
    }

    /**
     * Send an email using custom SMTP settings.
     */
    public function send_mail($user_email, $subject, $description)
    {
        // Override mail config dynamically
        config([
            'mail.mailers.smtp.transport' => get_settings('protocol'),
            'mail.mailers.smtp.host' => get_settings('smtp_host'),
            'mail.mailers.smtp.port' => get_settings('smtp_port'),
            'mail.mailers.smtp.encryption' => get_settings('smtp_crypto'),
            'mail.mailers.smtp.username' => get_settings('smtp_from_email'),
            'mail.mailers.smtp.password' => get_settings('smtp_pass'),
            'mail.from.address' => get_settings('smtp_from_email'),
            'mail.from.name' => get_settings('smtp_user'),
        ]);

        // Mail data payload
        $mail_data = [
            'subject' => $subject,
            'description' => $description,
        ];

        return Mail::to($user_email)->send(new Mailer($mail_data));
    }
}
