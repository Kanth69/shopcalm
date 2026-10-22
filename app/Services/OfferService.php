<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Collection;

class OfferService
{
    /**
     * Get the highest priority live Mega Sale campaign.
     */
    public function getLiveMegaSale(): ?Offer
    {
        return Offer::megaSale()
            ->orderBy('priority', 'desc')
            ->first();
    }

    /**
     * Get all active Flash Deals.
     */
    public function getLiveFlashDeals(): Collection
    {
        return Offer::flashDeals()
            ->orderBy('priority', 'desc')
            ->get();
    }

    protected ?Collection $cachedLiveOffers = null;

    /**
     * Get all active live offers with targets memoized per request.
     */
    public function getLiveOffers(): Collection
    {
        if ($this->cachedLiveOffers !== null) {
            return $this->cachedLiveOffers;
        }

        $this->cachedLiveOffers = Offer::live()->with('targets')->orderBy('priority', 'desc')->get();
        return $this->cachedLiveOffers;
    }

    /**
     * Attach active sale prices and badges to a collection of products.
     */
    public function applyOfferDiscountsToProducts($products)
    {
        $liveOffers = $this->getLiveOffers();

        if ($liveOffers->isEmpty()) {
            return $products;
        }

        foreach ($products as $product) {
            $basePrice = (float) ($product->price ?? 0);
            if ($basePrice <= 0) continue;

            $applicableOffer = $this->findBestOfferForProduct($product, $liveOffers);
            if ($applicableOffer) {
                $discount = $applicableOffer->calculateDiscount($basePrice);
                $product->sale_price = max(0, $basePrice - $discount);
                $product->offer_badge = $applicableOffer->badge_text ?? "{$applicableOffer->title}";
                $product->offer_discount_percentage = round(($discount / $basePrice) * 100);
            }
        }

        return $products;
    }

    /**
     * Attach active sale prices and badges to a single product.
     */
    public function applyOfferDiscountToProduct(Product $product): Product
    {
        $liveOffers = $this->getLiveOffers();

        if ($liveOffers->isEmpty()) {
            return $product;
        }

        $basePrice = (float) ($product->price ?? 0);
        if ($basePrice > 0) {
            $applicableOffer = $this->findBestOfferForProduct($product, $liveOffers);
            if ($applicableOffer) {
                $discount = $applicableOffer->calculateDiscount($basePrice);
                $product->sale_price = max(0, $basePrice - $discount);
                $product->offer_badge = $applicableOffer->badge_text ?? "{$applicableOffer->title}";
                $product->offer_discount_percentage = round(($discount / $basePrice) * 100);
            }
        }

        return $product;
    }

    /**
     * Apply a specific chosen offer to a list of products.
     */
    public function applySpecificOfferToProducts($products, Offer $offer)
    {
        foreach ($products as $product) {
            $basePrice = (float) ($product->price ?? 0);
            if ($basePrice > 0) {
                $discount = $offer->calculateDiscount($basePrice);
                $product->sale_price = max(0, $basePrice - $discount);
                $product->offer_badge = $offer->badge_text ?? "{$offer->title}";
                $product->offer_discount_percentage = round(($discount / $basePrice) * 100);
            }
        }

        return $products;
    }

    /**
     * Find the best matching active offer for a specific product.
     * Specificity hierarchy:
     * 1. Exact PRODUCT match
     * 2. CATEGORY or BRAND match
     * 3. STOREWIDE (All products) match
     */
    public function findBestOfferForProduct(Product $product, Collection $offers): ?Offer
    {
        $basePrice = (float) ($product->price ?? 0);

        $productMatches = collect();
        $groupMatches = collect();
        $storewideMatches = collect();

        foreach ($offers as $offer) {
            $targets = $offer->targets;
            $type = $offer->type;

            // Storewide discount
            if ($targets->isEmpty() && in_array($type, ['MEGA_SALE', 'DIRECT_DISCOUNT'])) {
                $storewideMatches->push($offer);
                continue;
            }

            $matchedProduct = false;
            $matchedGroup = false;

            foreach ($targets as $target) {
                if ($target->target_type === 'PRODUCT' && $target->target_id == $product->id) {
                    $matchedProduct = true;
                    break;
                }
                if ($target->target_type === 'CATEGORY' && $target->target_id == $product->category_id) {
                    $matchedGroup = true;
                }
                if ($target->target_type === 'BRAND' && $target->target_id == $product->brand_id) {
                    $matchedGroup = true;
                }
            }

            if ($matchedProduct) {
                $productMatches->push($offer);
            } elseif ($matchedGroup) {
                $groupMatches->push($offer);
            }
        }

        // Candidates by specificity hierarchy: PRODUCT > GROUP (CATEGORY/BRAND) > STOREWIDE
        $candidates = $productMatches->isNotEmpty()
            ? $productMatches
            : ($groupMatches->isNotEmpty() ? $groupMatches : $storewideMatches);

        if ($candidates->isEmpty()) {
            return null;
        }

        // Return candidate with highest calculated discount (and priority as tie-breaker)
        return $candidates->sortByDesc(function ($offer) use ($basePrice) {
            $discount = $offer->calculateDiscount($basePrice);
            return ($discount * 10000) + (int) ($offer->priority ?? 0);
        })->first();
    }

    /**
     * Calculate instant checkout offer discount for a cart.
     */
    public function calculateCheckoutOfferDiscount($cart, string $paymentMethod = 'cod'): float
    {
        if (!$cart || !$cart->items || $cart->items->isEmpty()) {
            return 0.0;
        }

        $liveOffers = Offer::live()->with('targets')->orderBy('priority', 'desc')->get();
        $totalDiscount = 0.0;

        foreach ($liveOffers as $offer) {
            // Product-level offers are already baked into the cart item's unit_price. 
            // We should only calculate cart-level offers here (like BANK_OFFER).
            if (in_array($offer->type, ['MEGA_SALE', 'FLASH_DEAL', 'DIRECT_DISCOUNT', 'CATEGORY_SALE'])) {
                continue;
            }

            if ($cart->subtotal() < $offer->min_purchase_amount) {
                continue;
            }

            // Check Bank Offer matching payment method
            if ($offer->type === 'BANK_OFFER') {
                $bankTargets = $offer->targets->where('target_type', 'PAYMENT_METHOD');
                if ($bankTargets->isNotEmpty() && !$bankTargets->pluck('target_id')->contains($paymentMethod)) {
                    continue;
                }
            }

            $discount = $offer->calculateDiscount($cart->subtotal());
            $totalDiscount += $discount;

            // Break if offer is not stackable with other offers
            break;
        }

        return round($totalDiscount, 2);
    }
}
