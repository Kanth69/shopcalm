<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Display a listing of customer contact inquiries.
     */
    public function index(Request $request): View
    {
        $query = ContactEnquiry::with('resolver');

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->latest()->paginate(15)->withQueryString();

        $statusCounts = [
            'all'         => ContactEnquiry::count(),
            'unread'      => ContactEnquiry::where('status', 'unread')->count(),
            'in_progress' => ContactEnquiry::where('status', 'in_progress')->count(),
            'resolved'    => ContactEnquiry::where('status', 'resolved')->count(),
        ];

        return view('support.enquiries.index', compact('enquiries', 'statusCounts'));
    }

    /**
     * Display inquiry detail with customer context.
     */
    public function show(ContactEnquiry $enquiry): View
    {
        $enquiry->load('resolver');

        // Auto mark as read if currently unread
        if (!$enquiry->is_read) {
            $enquiry->update(['is_read' => true]);
        }

        // Find customer profile by email for 360 context
        $customer = User::where('email', $enquiry->email)
            ->with(['orders' => fn($q) => $q->latest()->take(5)])
            ->first();

        return view('support.enquiries.show', compact('enquiry', 'customer'));
    }

    /**
     * Update inquiry resolution status and internal notes.
     */
    public function update(Request $request, ContactEnquiry $enquiry): RedirectResponse
    {
        $request->validate([
            'status'      => ['required', 'string', 'in:unread,in_progress,resolved'],
            'reply_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $updateData = [
            'status'      => $request->status,
            'reply_notes' => $request->reply_notes,
            'is_read'     => true,
        ];

        if ($request->status === 'resolved') {
            $updateData['resolved_by'] = Auth::id();
            $updateData['resolved_at'] = now();
        }

        $enquiry->update($updateData);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Inquiry Updated',
            'message' => "Customer inquiry #{$enquiry->id} is marked as " . ucfirst(str_replace('_', ' ', $request->status)) . ".",
        ]);
    }

    /**
     * Delete an inquiry (e.g. spam).
     */
    public function destroy(ContactEnquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return redirect()->route('support.enquiries.index')->with('toast', [
            'type'    => 'info',
            'title'   => 'Inquiry Deleted',
            'message' => 'Contact inquiry was removed from records.',
        ]);
    }

    /**
     * Perform bulk status updates or deletion on selected inquiries.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'exists:contact_enquiries,id'],
            'action' => ['required', 'string', 'in:unread,in_progress,resolved,mark_read,delete'],
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $count = count($ids);

        if ($action === 'delete') {
            ContactEnquiry::whereIn('id', $ids)->delete();

            return back()->with('toast', [
                'type'    => 'info',
                'title'   => 'Inquiries Deleted',
                'message' => "Successfully removed {$count} selected inquiries.",
            ]);
        }

        if ($action === 'mark_read') {
            ContactEnquiry::whereIn('id', $ids)->update(['is_read' => true]);

            return back()->with('toast', [
                'type'    => 'success',
                'title'   => 'Marked as Read',
                'message' => "Successfully marked {$count} selected inquiries as read.",
            ]);
        }

        $updateData = [
            'status'  => $action,
            'is_read' => true,
        ];

        if ($action === 'resolved') {
            $updateData['resolved_by'] = Auth::id();
            $updateData['resolved_at'] = now();
        }

        ContactEnquiry::whereIn('id', $ids)->update($updateData);

        $statusLabel = ucfirst(str_replace('_', ' ', $action));

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Bulk Update Successful',
            'message' => "Successfully marked {$count} selected inquiries as {$statusLabel}.",
        ]);
    }
}
