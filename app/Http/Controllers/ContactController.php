<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactEnquiry;
use App\Models\Page;

class ContactController extends Controller
{
    /**
     * Show the Contact Us page.
     */
    public function show()
    {
        $page = Page::where('slug', 'contact-us')->where('is_active', true)->first();
        return view('pages.contact', compact('page'));
    }

    /**
     * Submit contact form directly to Customer Support database.
     * No external emails are sent.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'nullable|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $wordCount = count(preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY));
                    if ($wordCount > 250) {
                        $fail("The message must not exceed 250 words (currently {$wordCount} words).");
                    }
                },
            ],
        ], [
            'name.required' => 'Please enter your full name.',
            'mobile.required' => 'Please enter your 10-digit mobile number.',
            'mobile.regex' => 'Please enter a valid 10-digit mobile number.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please select an inquiry subject.',
            'message.required' => 'Please enter your message.',
        ]);

        // Save enquiry directly to Customer Support portal table (contact_enquiries)
        ContactEnquiry::create($validated);

        if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you shortly.'
            ]);
        }

        return redirect()->back()->with('success', 'Your message has been sent successfully. We will get back to you shortly.');
    }
}
