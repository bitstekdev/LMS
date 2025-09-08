<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::latest()->paginate(10);

        return view('admin.newsletter.index', compact('newsletters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Newsletter::create($request->only('subject', 'description'));

        Session::flash('success', get_phrase('Newsletter created successfully'));

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $newsletter = Newsletter::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $newsletter->update($request->only('subject', 'description'));

        Session::flash('success', get_phrase('Newsletter updated successfully.'));

        return redirect()->back();
    }

    public function delete($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();

        Session::flash('success', get_phrase('Newsletter deleted successfully.'));

        return redirect()->back();
    }

    public function subscribers(Request $request)
    {
        $subscribers = NewsletterSubscriber::latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('email', 'like', '%'.$request->search.'%');
            })
            ->paginate(20);

        return view('admin.newsletter.subscribers', compact('subscribers'));
    }

    public function subscribed_user_delete($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();

        Session::flash('success', get_phrase('Subscriber deleted successfully'));

        return redirect()->back();
    }

    public function newsletters_form()
    {
        return view('admin.newsletter.send_newsletter');
    }

    public function get_user(Request $request)
    {
        $users = User::where('name', 'LIKE', '%'.$request->searchVal.'%')->get();

        $response = $users->map(fn ($user) => [
            'id' => $user->id,
            'text' => $user->name,
        ]);

        return response()->json($response);
    }

    public function send_newsletters(Request $request)
    {
        $request->validate([
            'send_to' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $members = match ($request->send_to) {
            'all' => array_merge(
                User::all()->all(),
                NewsletterSubscriber::all()->all()
            ),
            'student' => User::where('role', 'student')->get(),
            'instructor' => User::where('role', 'instructor')->get(),
            'all_subscriber' => NewsletterSubscriber::all(),
            'registered_subscriber' => NewsletterSubscriber::join('users', 'newsletter_subscribers.email', '=', 'users.email')->get(),
            'non_registered_subscriber' => NewsletterSubscriber::leftJoin('users', 'newsletter_subscribers.email', '=', 'users.email')
                ->whereNull('users.id')
                ->get(),
            'selected_user' => Session::flash('error', get_phrase('Please select a user')) && redirect()->route('admin.newsletter'),
            default => [],
        };

        foreach ($members as $member) {
            $email = $member->email ?? null;
            if ($email) {
                $this->send_mail($email, $request->subject, $request->description);
            }
        }

        Session::flash('success', get_phrase('Email sent successfully'));

        return redirect()->route('admin.newsletter');
    }

    private function send_mail($user_email, $subject, $description)
    {
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

        $mail_data = compact('subject', 'description');

        return Mail::to($user_email)->send(new Mailer($mail_data));
    }
}
