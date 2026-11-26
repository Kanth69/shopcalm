<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $userId = Auth::id();

        // Enforce Verified Buyer Requirement: Customer must have purchased and received this product
        $isVerifiedBuyer = Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->exists();

        if (!$isVerifiedBuyer) {
            $msg = 'Only verified buyers who have received this product can submit a review.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 403);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review' => 'nullable|string',
        ], [
            'rating.required' => 'Please select a star rating (1 to 5 stars).',
            'rating.min' => 'Please select at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
        ]);

        $product->reviews()->updateOrCreate(
            ['user_id' => $userId],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'review' => $request->review,
                'status' => 'Pending',
                'is_verified_purchase' => true,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review has been submitted successfully.'
            ]);
        }

        return back()->with('review_submitted_swal', 'Thank you! Your review has been submitted successfully.');
    }

    public function destroy(Request $request, ProductReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403);
        }

        $review->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your review has been deleted successfully.'
            ]);
        }

        return back()->with('success', 'Your review has been deleted.');
    }
}
