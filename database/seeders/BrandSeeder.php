<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electronics = \App\Models\Category::where('slug', 'electronics-appliances')->first();
        $smartphones = \App\Models\Category::where('slug', 'smartphones-accessories')->first();
        $fashion = \App\Models\Category::where('slug', 'fashion-apparel')->first();
        $home = \App\Models\Category::where('slug', 'home-living')->first();

        $brands = [
            [
                'name'        => 'Samsung',
                'slug'        => 'samsung',
                'category_id' => $smartphones?->id,
                'description' => 'Global leader in mobile innovation, QLED TVs, and smart home appliances.',
                'logo'        => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400&auto=format&fit=crop&q=80',
                'status'      => true,
            ],
            [
                'name'        => 'Apple',
                'slug'        => 'apple',
                'category_id' => $smartphones?->id,
                'description' => 'Premium iPhones, MacBooks, iPads, and AirPods.',
                'logo'        => 'https://images.unsplash.com/photo-1563770660941-20978e870e26?w=400&auto=format&fit=crop&q=80',
                'status'      => true,
            ],
            [
                'name'        => 'Sony',
                'slug'        => 'sony',
                'category_id' => $electronics?->id,
                'description' => 'World-class noise cancelling headphones, Bravia OLED TVs, and PlayStation gaming.',
                'logo'        => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=400&auto=format&fit=crop&q=80',
                'status'      => true,
            ],
            [
                'name'        => 'Nike',
                'slug'        => 'nike',
                'category_id' => $fashion?->id,
                'description' => 'Iconic athletic footwear, apparel, and sports lifestyle gear.',
                'logo'        => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&auto=format&fit=crop&q=80',
                'status'      => true,
            ],
            [
                'name'        => 'ShopCalm Essentials',
                'slug'        => 'shopcalm-essentials',
                'category_id' => $home?->id,
                'description' => 'Premium house-brand products curated for quality, longevity, and calm living.',
                'logo'        => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=400&auto=format&fit=crop&q=80',
                'status'      => true,
            ],
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::updateOrCreate(['slug' => $brand['slug']], $brand);
        }
    }
}
