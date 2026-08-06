<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Services\OfferService;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    protected $offerService;

    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function index(Request $request)
    {
        $liveMegaSale = $this->offerService->getLiveMegaSale();
        $flashDeals = $this->offerService->getLiveFlashDeals();
        $allOffers = Offer::live()->with('targets')->orderBy('priority', 'desc')->get();

        $query = Product::where('status', 'Active');
        $selectedOfferId = $request->input('offer_id');
        $selectedOffer = null;

        if ($selectedOfferId) {
            $selectedOffer = $allOffers->firstWhere('id', $selectedOfferId);
        }

        if ($selectedOffer) {
            // Filter products specifically for the selected offer
            if ($selectedOffer->targets->isNotEmpty()) {
                $targetCatIds = $selectedOffer->targets->where('target_type', 'CATEGORY')->pluck('target_id')->filter()->toArray();
                $targetBrandIds = $selectedOffer->targets->where('target_type', 'BRAND')->pluck('target_id')->filter()->toArray();
                $targetProductIds = $selectedOffer->targets->where('target_type', 'PRODUCT')->pluck('target_id')->filter()->toArray();

                $query->where(function ($q) use ($targetCatIds, $targetBrandIds, $targetProductIds) {
                    if (!empty($targetCatIds)) $q->orWhereIn('category_id', $targetCatIds);
                    if (!empty($targetBrandIds)) $q->orWhereIn('brand_id', $targetBrandIds);
                    if (!empty($targetProductIds)) $q->orWhereIn('id', $targetProductIds);
                });
            }
        } elseif ($allOffers->isNotEmpty()) {
            // Filter products matching any live offer
            $targetCatIds = [];
            $targetBrandIds = [];
            $targetProductIds = [];
            $isStorewide = false;

            foreach ($allOffers as $offer) {
                if ($offer->targets->isEmpty()) {
                    $isStorewide = true;
                } else {
                    foreach ($offer->targets as $t) {
                        if ($t->target_type === 'CATEGORY') $targetCatIds[] = $t->target_id;
                        if ($t->target_type === 'BRAND') $targetBrandIds[] = $t->target_id;
                        if ($t->target_type === 'PRODUCT') $targetProductIds[] = $t->target_id;
                    }
                }
            }

            if (!$isStorewide) {
                $query->where(function ($q) use ($targetCatIds, $targetBrandIds, $targetProductIds) {
                    if (!empty($targetCatIds)) $q->orWhereIn('category_id', $targetCatIds);
                    if (!empty($targetBrandIds)) $q->orWhereIn('brand_id', $targetBrandIds);
                    if (!empty($targetProductIds)) $q->orWhereIn('id', $targetProductIds);
                });
            }
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->with(['category', 'brand'])->latest()->paginate(16)->appends($request->query());

        // Apply offer discount prices & badges
        $this->offerService->applyOfferDiscountsToProducts($products->items());

        return view('customer.offers.index', compact('liveMegaSale', 'flashDeals', 'allOffers', 'products', 'selectedOffer'));
    }
}
