<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h1>About ShopCalm</h1><p>Welcome to ShopCalm. We are dedicated to providing you the best products.</p>',
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>Get in touch with the ShopCalm team. We are here to help!</p>',
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'content' => '<h1>Terms & Conditions</h1><p>Please read these terms and conditions carefully before using ShopCalm.</p>',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us at ShopCalm.</p>',
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '<h1>Frequently Asked Questions</h1><p>Find answers to common questions here.</p>',
            ],
            [
                'title' => 'Return & Refund Policy',
                'slug' => 'return-refund-policy',
                'content' => '<h1>Return & Refund Policy</h1><p>Learn about our return and refund process.</p>',
            ],
            [
                'title' => 'Shipping Policy',
                'slug' => 'shipping-policy',
                'content' => '<h1>Shipping Policy</h1><p>Information about shipping rates and delivery times.</p>',
            ],
            [
                'title' => 'Cancellation Policy',
                'slug' => 'cancellation-policy',
                'content' => '<h1>Cancellation Policy</h1><p>How to cancel an order on ShopCalm.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
