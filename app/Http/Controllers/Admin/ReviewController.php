<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('review', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'all'      => ProductReview::count(),
            'pending'  => ProductReview::where('status', 'Pending')->count(),
            'approved' => ProductReview::where('status', 'Approved')->count(),
            'rejected' => ProductReview::where('status', 'Rejected')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function update(Request $request, ProductReview $review)
    {
        $request->validate(['status' => 'required|in:Approved,Rejected']);
        $review->update(['status' => $request->status]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Review updated to {$request->status}.",
                'status'  => $request->status
            ]);
        }

        return back()->with('success', 'Review status updated.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:product_reviews,id',
            'action' => 'required|in:approve,reject,delete'
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'approve') {
            ProductReview::whereIn('id', $ids)->update(['status' => 'Approved']);
            $msg = count($ids) . ' review(s) approved successfully.';
        } elseif ($action === 'reject') {
            ProductReview::whereIn('id', $ids)->update(['status' => 'Rejected']);
            $msg = count($ids) . ' review(s) rejected successfully.';
        } elseif ($action === 'delete') {
            ProductReview::whereIn('id', $ids)->delete();
            $msg = count($ids) . ' review(s) deleted successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }

    public function destroy(Request $request, ProductReview $review)
    {
        $review->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.'
            ]);
        }

        return back()->with('success', 'Review deleted successfully.');
    }
}
