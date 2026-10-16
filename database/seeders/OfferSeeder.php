<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use App\Models\OfferTarget;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds for promotional offers.
     */
    public function run(): void
    {
        // 1. Resolve Targets Dynamically
        $electronicsCategory = Category::where('slug', 'electronics')->first() ?? Category::first();
        $mobilesCategory     = Category::where('slug', 'mobiles')->first() ?? Category::where('id', '!=', $electronicsCategory?->id)->first();
        $nikeProduct         = Product::where('slug', 'like', '%nike%')->orWhere('name', 'like', '%Nike%')->first() ?? Product::where('status', 'Active')->first();
        $nikeBrand           = Brand::where('slug', 'like', '%nike%')->orWhere('name', 'like', '%Nike%')->first();

        $offers = [
            [
                'title'               => 'Grand Festive Electronics Sale',
                'slug'                => 'grand-festive-electronics-sale',
                'type'                => 'MEGA_SALE',
                'badge_text'          => '🔥 MEGA SALE EVENT',
                'description'         => 'Save up to 25% on cutting-edge 4K Smart TVs, laptops, wireless audio & top tech essentials.',
                'banner_image'        => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&auto=format&fit=crop&q=80',
                'theme_color'         => '#4f46e5',
                'discount_type'       => 'PERCENTAGE',
                'discount_value'      => 20.00,
                'min_purchase_amount' => 0.00,
                'max_discount_amount' => 5000.00,
                'start_time'          => now()->subDays(1),
                'end_time'            => now()->addDays(30),
                'priority'            => 10,
                'is_active'           => true,
                'target'              => $electronicsCategory ? [
                    'target_type' => 'CATEGORY',
                    'target_id'   => $electronicsCategory->id,
                ] : null,
            ],
            [
                'title'               => 'Flagship 5G Mobiles Flash Drop',
                'slug'                => 'flagship-mobiles-flash-drop',
                'type'                => 'FLASH_DEAL',
                'badge_text'          => '⚡ LIGHTNING DEAL',
                'description'         => 'Instant 15% price drop across top 5G smartphones, AMOLED displays, and flagship processors.',
                'banner_image'        => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200&auto=format&fit=crop&q=80',
                'theme_color'         => '#dc2626',
                'discount_type'       => 'PERCENTAGE',
                'discount_value'      => 15.00,
                'min_purchase_amount' => 0.00,
                'max_discount_amount' => 7500.00,
                'start_time'          => now()->subHours(2),
                'end_time'            => now()->addDays(7),
                'priority'            => 15,
                'is_active'           => true,
                'target'              => $mobilesCategory ? [
                    'target_type' => 'CATEGORY',
                    'target_id'   => $mobilesCategory->id,
                ] : null,
            ],
            [
                'title'               => 'Nike Air Max Exclusive Drop',
                'slug'                => 'nike-air-max-exclusive-drop',
                'type'                => 'FLASH_DEAL',
                'badge_text'          => '👟 SNEAKER DROP',
                'description'         => 'Exclusive 30% OFF on iconic Nike Air Max sneakers featuring premium responsive air cushioning.',
                'banner_image'        => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1200&auto=format&fit=crop&q=80',
                'theme_color'         => '#ea580c',
                'discount_type'       => 'PERCENTAGE',
                'discount_value'      => 30.00,
                'min_purchase_amount' => 0.00,
                'max_discount_amount' => 4000.00,
                'start_time'          => now()->subHours(1),
                'end_time'            => now()->addDays(14),
                'priority'            => 20,
                'is_active'           => true,
                'target'              => $nikeProduct ? [
                    'target_type' => 'PRODUCT',
                    'target_id'   => $nikeProduct->id,
                ] : ($nikeBrand ? [
                    'target_type' => 'BRAND',
                    'target_id'   => $nikeBrand->id,
                ] : null),
            ],
        ];

        foreach ($offers as $offerData) {
            $targetData = $offerData['target'] ?? null;
            unset($offerData['target']);

            $offer = Offer::updateOrCreate(
                ['slug' => $offerData['slug']],
                $offerData
            );

            if ($targetData) {
                OfferTarget::updateOrCreate(
                    [
                        'offer_id'    => $offer->id,
                        'target_type' => $targetData['target_type'],
                        'target_id'   => $targetData['target_id'],
                    ],
                    []
                );
            }
        }

        // Flush all relevant offer & storefront caches
        Cache::forget('home_active_offers');
        Cache::forget('home_offers');
        Cache::forget('home_banners');
        Cache::forget('featured_products');
        Cache::forget('trending_products');
        Cache::forget('latest_products');
    }
}
