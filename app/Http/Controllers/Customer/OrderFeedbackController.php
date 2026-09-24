<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderFeedbackController extends Controller
{
    /**
     * Store or update customer feedback for a completed order.
     */
    public function store(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($order->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Feedback can only be provided for delivered orders.'], 422);
        }

        $validated = $request->validate([
            'delivery_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'product_rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string', 'max:50'],
            'comment'         => ['nullable', 'string', 'max:1000'],
        ]);

        $riderId = $order->fulfillment?->rider_id;

        $feedback = OrderFeedback::updateOrCreate(
            ['order_id' => $order->id],
            [
                'user_id'         => Auth::id(),
                'rider_id'        => $riderId,
                'delivery_rating' => $validated['delivery_rating'],
                'product_rating'  => $validated['product_rating'],
                'tags'            => $validated['tags'] ?? [],
                'comment'         => $validated['comment'] ?? null,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback! ⭐',
                'feedback' => $feedback,
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Thank You!',
            'message' => 'Your delivery experience feedback has been recorded.',
        ]);
    }
}
