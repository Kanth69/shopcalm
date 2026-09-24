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
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|digits:10',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Save enquiry directly to Customer Support portal table (contact_enquiries)
        ContactEnquiry::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you shortly.'
            ]);
        }

        return redirect()->back()->with('success', 'Your message has been sent successfully. We will get back to you shortly.');
    }
}
