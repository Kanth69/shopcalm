<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review' => 'nullable|string',
        ]);

        $product->reviews()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'review' => $request->review,
                'status' => 'Pending',
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your review will be posted soon after administrator approval!'
            ]);
        }

        return back()->with('review_submitted_swal', 'Your review will be posted soon!');
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
