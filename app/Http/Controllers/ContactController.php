<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactEnquiry;
use App\Models\Page;
use App\Mail\ContactEnquiryMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendBrevoEmailJob;

class ContactController extends Controller
{
    public function show()
    {
        $page = Page::where('slug', 'contact-us')->where('is_active', true)->first();
        return view('pages.contact', compact('page'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|digits:10',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $enquiry = ContactEnquiry::create($validated);

        // Send email to admin
        $adminEmail = config('mail.from.address'); // Or a specific admin email setting
        
        $mailable = new ContactEnquiryMail($enquiry);
        $html = $mailable->render();
        $text = strip_tags($html);
        
        SendBrevoEmailJob::dispatch(
            $adminEmail,
            'New Contact Enquiry: ' . $enquiry->subject,
            $html,
            $text
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you shortly.'
            ]);
        }

        return redirect()->back()->with('success', 'Your message has been sent successfully. We will get back to you shortly.');
    }
}
