<?php

namespace App\Http\Controllers\OrderManager;

use App\Http\Controllers\Controller;
use App\Models\OrderFeedback;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Http\Request;

class RiderPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $riderQuery = User::deliveryPartners();

        if ($request->filled('rider_id')) {
            $riderQuery->where('id', $request->rider_id);
        }

        $allRidersList = User::deliveryPartners()->get();

        $riders = $riderQuery->get()->map(function ($rider) use ($request) {
            $feedbackQuery = OrderFeedback::where('rider_id', $rider->id)
                ->with(['order', 'user'])
                ->latest();

            if ($request->filled('rating')) {
                $feedbackQuery->where('delivery_rating', $request->rating);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $feedbackQuery->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                      ->orWhereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            }

            $allRiderFeedbacks = OrderFeedback::where('rider_id', $rider->id)->get();
            $totalFeedbacks = $allRiderFeedbacks->count();
            $avgRating = $totalFeedbacks > 0 ? round($allRiderFeedbacks->avg('delivery_rating'), 1) : 0;
            $avgPackaging = $totalFeedbacks > 0 ? round($allRiderFeedbacks->avg('product_rating'), 1) : 0;
            $fiveStars = $allRiderFeedbacks->where('delivery_rating', 5)->count();
            $satisfactionRate = $totalFeedbacks > 0 ? round(($fiveStars / $totalFeedbacks) * 100) : 100;

            $totalDeliveries = OrderFulfillment::where('rider_id', $rider->id)
                ->whereHas('order', fn($q) => $q->where('status', 'delivered'))
                ->count();

            $activeDeliveries = OrderFulfillment::where('rider_id', $rider->id)
                ->whereHas('order', fn($q) => $q->whereIn('status', ['packed', 'shipped', 'out for delivery', 'processing']))
                ->count();

            // Collect top tags
            $allTags = [];
            foreach ($allRiderFeedbacks as $fb) {
                if (is_array($fb->tags)) {
                    foreach ($fb->tags as $t) {
                        $allTags[$t] = ($allTags[$t] ?? 0) + 1;
                    }
                }
            }
            arsort($allTags);

            return [
                'id'                => $rider->id,
                'name'              => $rider->name,
                'phone'             => $rider->mobile_number,
                'email'             => $rider->email,
                'total_deliveries'  => $totalDeliveries,
                'active_deliveries' => $activeDeliveries,
                'total_feedbacks'   => $totalFeedbacks,
                'feedback_count'    => $totalFeedbacks,
                'avg_rating'        => $avgRating,
                'avg_packaging'     => $avgPackaging,
                'five_stars'        => $fiveStars,
                'satisfaction_rate' => $satisfactionRate,
                'top_tags'          => $allTags,
                'feedbacks'         => $feedbackQuery->get(),
            ];
        })->sortByDesc('avg_rating')->values();

        $totalFleetRiders = $allRidersList->count();
        $totalFleetFeedbacks = OrderFeedback::whereNotNull('rider_id')->count();
        $overallAvgRating = (float) (OrderFeedback::whereNotNull('rider_id')->avg('delivery_rating') ?? 0);

        return view('order-manager.riders.performance', compact(
            'riders',
            'allRidersList',
            'totalFleetRiders',
            'totalFleetFeedbacks',
            'overallAvgRating'
        ));
    }
}
