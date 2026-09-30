<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title'               => 'Grand Festive Sale',
                'subtitle'            => 'Up to 50% off on flagship smartphones, OLED TVs, and premium audio.',
                'desktop_image'       => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1600&auto=format&fit=crop&q=80',
                'mobile_image'        => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&auto=format&fit=crop&q=80',
                'primary_button_text' => 'Shop Deals',
                'primary_button_link' => '/shop?category=electronics-appliances',
                'display_order'       => 1,
                'is_active'           => true,
                'bg_gradient'         => 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
            ],
            [
                'title'               => 'Step Into Comfort & Style',
                'subtitle'            => 'Explore the new Nike Sportswear & Streetwear Collection.',
                'desktop_image'       => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1600&auto=format&fit=crop&q=80',
                'mobile_image'        => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800&auto=format&fit=crop&q=80',
                'primary_button_text' => 'Explore Fashion',
                'primary_button_link' => '/shop?category=fashion-apparel',
                'display_order'       => 2,
                'is_active'           => true,
                'bg_gradient'         => 'linear-gradient(135deg, #831843 0%, #db2777 100%)',
            ],
            [
                'title'               => 'Transform Your Home Space',
                'subtitle'            => 'Minimalist lighting, organic bamboo bedding, and cozy sanctuary decor.',
                'desktop_image'       => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1600&auto=format&fit=crop&q=80',
                'mobile_image'        => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=800&auto=format&fit=crop&q=80',
                'primary_button_text' => 'Upgrade Home',
                'primary_button_link' => '/shop?category=home-living',
                'display_order'       => 3,
                'is_active'           => true,
                'bg_gradient'         => 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(['title' => $banner['title']], $banner);
        }
    }
}
