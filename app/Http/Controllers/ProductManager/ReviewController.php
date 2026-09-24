<?php

namespace App\Http\Controllers\ProductManager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Display product reviews and rating analytics.
     */
    public function index(Request $request): View
    {
        $query = ProductReview::with(['product', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int)$request->rating);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('review', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $reviews = $query->paginate(15)->withQueryString();

        // Metrics & Rating distribution
        $totalReviews = ProductReview::count();
        $pendingReviews = ProductReview::where('status', 'Pending')->count();
        $approvedReviews = ProductReview::where('status', 'Approved')->count();
        $rejectedReviews = ProductReview::where('status', 'Rejected')->count();
        $averageRating = ProductReview::where('status', 'Approved')->avg('rating') ?? 0;

        $ratingCounts = [
            5 => ProductReview::where('rating', 5)->count(),
            4 => ProductReview::where('rating', 4)->count(),
            3 => ProductReview::where('rating', 3)->count(),
            2 => ProductReview::where('rating', 2)->count(),
            1 => ProductReview::where('rating', 1)->count(),
        ];

        $products = Product::has('reviews')->orderBy('name')->get();

        return view('product-manager.reviews.index', compact(
            'reviews',
            'totalReviews',
            'pendingReviews',
            'approvedReviews',
            'rejectedReviews',
            'averageRating',
            'ratingCounts',
            'products'
        ));
    }

    /**
     * Update review (Edit text, rating, or Approve / Reject status).
     */
    public function update(Request $request, ProductReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Pending,Approved,Rejected'],
            'review' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $review->update($validated);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Review Updated',
            'message' => "Review updated and marked as {$request->status}.",
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Review Deleted',
            'message' => 'Review removed successfully.',
        ]);
    }

    /**
     * Perform bulk moderation actions on reviews.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_reviews' => ['required', 'array', 'min:1'],
            'selected_reviews.*' => ['integer', 'exists:product_reviews,id'],
            'action' => ['required', 'in:approve,reject,delete'],
        ]);

        $ids = $request->selected_reviews;
        $count = count($ids);

        if ($request->action === 'approve') {
            ProductReview::whereIn('id', $ids)->update(['status' => 'Approved']);
            $msg = "Approved {$count} reviews.";
        } elseif ($request->action === 'reject') {
            ProductReview::whereIn('id', $ids)->update(['status' => 'Rejected']);
            $msg = "Rejected {$count} reviews.";
        } elseif ($request->action === 'delete') {
            ProductReview::whereIn('id', $ids)->delete();
            $msg = "Deleted {$count} reviews.";
        }

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Bulk Action Completed',
            'message' => $msg,
        ]);
    }
}
