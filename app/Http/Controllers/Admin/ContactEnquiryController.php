<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactEnquiry;

class ContactEnquiryController extends Controller
{
    public function index()
    {
        $enquiries = ContactEnquiry::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.enquiries.index', compact('enquiries'));
    }

    public function show(ContactEnquiry $enquiry)
    {
        if (!$enquiry->is_read) {
            $enquiry->update(['is_read' => true]);
        }
        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function destroy(Request $request, ContactEnquiry $enquiry)
    {
        $enquiry->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Enquiry deleted successfully.']);
        }

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:contact_enquiries,id']);

        $count = ContactEnquiry::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} enquir" . ($count === 1 ? 'y' : 'ies') . " deleted successfully.",
            'count' => $count,
        ]);
    }
}
