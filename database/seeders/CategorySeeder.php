<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Electronics & Appliances',
                'slug'        => 'electronics-appliances',
                'description' => 'Latest smart TVs, audio speakers, home theater systems, and electronic gadgets.',
                'image'       => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=800&auto=format&fit=crop&q=80',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Smartphones & Accessories',
                'slug'        => 'smartphones-accessories',
                'description' => 'Top flagship smartphones, wireless earbuds, fast chargers, and protective cases.',
                'image'       => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Fashion & Apparel',
                'slug'        => 'fashion-apparel',
                'description' => 'Trending streetwear, casual denim, footwear, jackets, and summer wear for men & women.',
                'image'       => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800&auto=format&fit=crop&q=80',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Home & Living',
                'slug'        => 'home-living',
                'description' => 'Modern furniture, ambient lighting, plush bedsheets, and kitchen cookware.',
                'image'       => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=800&auto=format&fit=crop&q=80',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Beauty & Personal Care',
                'slug'        => 'beauty-personal-care',
                'description' => 'Organic skincare, herbal hair serums, grooming trimmers, and luxury perfumes.',
                'image'       => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800&auto=format&fit=crop&q=80',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Daily Essentials & Grocery',
                'slug'        => 'daily-essentials-grocery',
                'description' => 'Fresh organic produce, pantry staples, dry fruits, and gourmet snacks.',
                'image'       => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&auto=format&fit=crop&q=80',
                'status'      => 'Active',
            ],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
