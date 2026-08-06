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

    /**
     * Attach active sale prices and badges to a collection of products.
     */
    public function applyOfferDiscountsToProducts($products)
    {
        $liveOffers = Offer::live()->with('targets')->orderBy('priority', 'desc')->get();

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
     * Find the best matching active offer for a specific product.
     */
    public function findBestOfferForProduct(Product $product, Collection $offers): ?Offer
    {
        foreach ($offers as $offer) {
            $type = $offer->type;
            $targets = $offer->targets;

            // Direct discount on all products
            if ($targets->isEmpty() && in_array($type, ['MEGA_SALE', 'DIRECT_DISCOUNT'])) {
                return $offer;
            }

            foreach ($targets as $target) {
                if ($target->target_type === 'CATEGORY' && $target->target_id == $product->category_id) {
                    return $offer;
                }
                if ($target->target_type === 'BRAND' && $target->target_id == $product->brand_id) {
                    return $offer;
                }
                if ($target->target_type === 'PRODUCT' && $target->target_id == $product->id) {
                    return $offer;
                }
            }
        }

        return null;
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
