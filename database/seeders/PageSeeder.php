<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds with Indian Law Compliant Legal Clauses (DPDP Act 2023 & Consumer Protection Rules 2020).
     */
    public function run(): void
    {
        // Clean out legacy CMS pages to ensure zero stale or obsolete brand records
        Page::truncate();

        $storeName = 'ShopCalm';
        $companyName = 'ShopCalm E-Commerce Solutions';
        $domain = 'shopcalm.in';
        $supportEmail = 'support@shopcalm.in';
        $grievanceEmail = 'grievance@shopcalm.in';

        // 1. PRIVACY POLICY
        $privacyData = [
            'desc' => 'How we securely handle and protect your personal information.',
            'intro' => '<p class="lead">At <strong>' . $storeName . '</strong>, we value your trust. We collect only the information needed to deliver your orders safely and never sell or rent your personal data.</p>',
            'sections' => [
                [
                    'title' => 'Information We Collect',
                    'content' => '<p>We collect essential information to process your orders smoothly:</p>
                    <ul>
                        <li><strong>Account Details:</strong> Your name, mobile number, and encrypted password.</li>
                        <li><strong>Delivery Address:</strong> House number, street name, city, state, and pincode.</li>
                        <li><strong>Payment Records:</strong> Order summary and payment gateway reference IDs. <em>We never store card numbers, CVVs, or UPI PINs.</em></li>
                        <li><strong>Support & Feedback:</strong> Support messages, WhatsApp delivery updates, and product reviews.</li>
                    </ul>'
                ],
                [
                    'title' => 'How We Use Your Data',
                    'content' => '<p>Your information is used strictly to provide you with a great shopping experience:</p>
                    <ul>
                        <li>Processing and delivering your orders to your doorstep.</li>
                        <li>Sending live delivery updates and tracking links via WhatsApp and SMS.</li>
                        <li>Assisting you with customer support, pre-dispatch cancellations, and instant wallet refunds.</li>
                        <li>Generating official GST tax invoices for your purchases.</li>
                    </ul>'
                ],
                [
                    'title' => 'Data Sharing with Trusted Partners',
                    'content' => '<p><strong>We never sell your data to third parties.</strong> We only share necessary details with trusted service partners:</p>
                    <ul>
                        <li><strong>Delivery Couriers:</strong> Your name, phone, and address are shared with verified couriers (Delhivery, BlueDart, Shiprocket) solely for delivery.</li>
                        <li><strong>Payment Gateways:</strong> Payments are processed directly through RBI-authorized, PCI-DSS compliant gateways (Razorpay/PhonePe).</li>
                        <li><strong>Order Alerts:</strong> Your phone number is used to send automated WhatsApp order notifications.</li>
                    </ul>'
                ],
                [
                    'title' => 'Data Security',
                    'content' => '<p>We use strong security measures to keep your data safe:</p>
                    <ul>
                        <li><strong>256-Bit SSL Encryption:</strong> All connections between your browser and our servers are encrypted.</li>
                        <li><strong>Password Protection:</strong> Passwords are stored using secure, irreversible one-way hashing.</li>
                        <li><strong>Restricted Access:</strong> Only authorized server systems have access to order records.</li>
                    </ul>'
                ],
                [
                    'title' => 'Your Privacy Rights',
                    'content' => '<p>You have full control over your personal account information:</p>
                    <ul>
                        <li>View and update your saved addresses and order history anytime from your Account Dashboard.</li>
                        <li>Manage your communication preferences or opt out of promotional messages.</li>
                        <li>Request account deletion by reaching out to our customer support team.</li>
                    </ul>'
                ],
                [
                    'title' => 'Grievance & Privacy Contact',
                    'content' => '<p>If you have any questions or concerns about your data, contact our support team:</p>
                    <div class="p-3 bg-light rounded-3 border">
                        <p class="mb-1"><strong>Support Desk:</strong> ' . $companyName . '</p>
                        <p class="mb-1"><strong>Email:</strong> <a href="mailto:' . $grievanceEmail . '">' . $grievanceEmail . '</a></p>
                        <p class="mb-1"><strong>Helpline:</strong> +91 81973 16916 (Mon - Sat: 9:00 AM - 6:00 PM IST)</p>
                        <p class="mb-0"><strong>Response Time:</strong> Acknowledged within 48 hours; resolved promptly.</p>
                    </div>'
                ]
            ]
        ];

        // 2. TERMS & CONDITIONS
        $termsData = [
            'desc' => 'Simple rules and guidelines for shopping with us.',
            'intro' => '<p class="lead">Welcome to <strong>' . $storeName . '</strong>. By browsing our website or placing an order, you agree to these straightforward terms and conditions.</p>',
            'sections' => [
                [
                    'title' => 'Account & Registration',
                    'content' => '<p>A quick guide to managing your account:</p>
                    <ul>
                        <li>Provide an accurate mobile number and delivery address for smooth order fulfillment.</li>
                        <li>Keep your login credentials and OTP codes confidential.</li>
                        <li>Accounts found engaging in fraudulent behavior or false orders may be suspended.</li>
                    </ul>'
                ],
                [
                    'title' => 'Clear & Transparent Pricing',
                    'content' => '<p>Honest pricing with no surprises at checkout:</p>
                    <ul>
                        <li><strong>All-Inclusive Prices:</strong> All listed prices are in Indian Rupees (₹) and include all applicable GST taxes.</li>
                        <li><strong>Zero Hidden Fees:</strong> Any delivery fee or discount coupon is clearly shown before you pay.</li>
                        <li><strong>Pricing Accuracy:</strong> In the rare event of a technical pricing glitch, affected orders may be cancelled and fully refunded.</li>
                    </ul>'
                ],
                [
                    'title' => 'Orders & Cancellations',
                    'content' => '<p>How your orders and cancellations work:</p>
                    <ul>
                        <li><strong>Instant Confirmation:</strong> You will receive order confirmation via WhatsApp, SMS, and email.</li>
                        <li><strong>Free Cancellation:</strong> You can cancel anytime before your package is dispatched with zero cancellation fees.</li>
                        <li><strong>Fast Refunds:</strong> Prepaid orders cancelled before dispatch receive an instant 100% refund to your Wallet or source account.</li>
                    </ul>'
                ],
                [
                    'title' => 'Strict No Return Policy',
                    'content' => '<p>Our return policy is clear and transparent:</p>
                    <ul>
                        <li><strong>Final Sale:</strong> Once an order is delivered to your address, products cannot be returned, exchanged, or replaced.</li>
                        <li><strong>Cancel Before Dispatch:</strong> Changed your mind? Simply cancel before the package leaves our warehouse for a full refund.</li>
                        <li><strong>Quality Inspected:</strong> Every item is checked for quality and securely packaged before handover to couriers.</li>
                    </ul>'
                ],
                [
                    'title' => 'Shipping & Delivery',
                    'content' => '<p>Fast doorstep delivery across India:</p>
                    <ul>
                        <li><strong>Timelines:</strong> Standard delivery takes 2 to 5 business days depending on your location.</li>
                        <li><strong>Live Tracking:</strong> Direct tracking links are sent via WhatsApp and SMS right after dispatch.</li>
                        <li><strong>Secure Handover:</strong> Delivery agents may request an OTP for high-value orders to ensure safe delivery.</li>
                    </ul>'
                ],
                [
                    'title' => 'Governing Law',
                    'content' => '<p>These terms are governed by the laws of India. Any legal matters or disputes will fall under the exclusive jurisdiction of the courts in Bengaluru, Karnataka.</p>'
                ]
            ]
        ];

        // 3. RETURN & REFUND POLICY (STRICT NO RETURN & NO REPLACEMENT)
        $returnData = [
            'desc' => 'Our transparent No Return & No Replacement policy guidelines.',
            'intro' => '<p class="lead">Thank you for shopping at <strong>' . $storeName . '</strong>. Please review our clear policy on returns, cancellations, and refunds before placing an order.</p>',
            'sections' => [
                [
                    'title' => 'Strict No Return & No Replacement Policy',
                    'content' => '<p>At <strong>' . $storeName . '</strong>, we maintain a strict <strong>No Return & No Replacement Policy</strong> across all categories:</p>
                    <ul>
                        <li><strong>No Post-Delivery Returns:</strong> Once an order has been delivered to your address, items cannot be returned, exchanged, or replaced.</li>
                        <li><strong>Free Cancellation Before Dispatch:</strong> You can cancel any order free of cost before it leaves our fulfillment warehouse.</li>
                        <li><strong>Quality Assurance:</strong> Every product is checked for quality and sealed with tamper-proof packaging before dispatch.</li>
                    </ul>'
                ],
                [
                    'title' => 'Damaged or Wrong Product Transit Exceptions',
                    'content' => '<p>In the rare event that an item is damaged during courier transit or an incorrect product is delivered:</p>
                    <ul>
                        <li>Report the issue within <strong>24 hours</strong> of delivery by emailing <a href="mailto:' . $supportEmail . '">' . $supportEmail . '</a> or reaching our support team.</li>
                        <li>Provide an <strong>unboxing video</strong> clearly showing the shipping label and outer package condition.</li>
                        <li>Our team will inspect the claim and arrange an appropriate resolution or replacement.</li>
                    </ul>'
                ],
                [
                    'title' => 'Refund Processing for Cancelled Orders',
                    'content' => '<p>For eligible orders cancelled <strong>before dispatch</strong>:</p>
                    <ul>
                        <li><strong>ShopCalm Wallet:</strong> Instant 100% credit for immediate store purchases.</li>
                        <li><strong>Original Payment Method:</strong> Refunded to your UPI, card, or bank account within 3 to 5 business days via Razorpay.</li>
                    </ul>'
                ]
            ]
        ];

        // 4. SHIPPING POLICY
        $shippingData = [
            'desc' => 'Delivery timelines, shipping fees, and live courier tracking across India.',
            'intro' => '<p class="lead">We deliver your favorite products quickly and safely across India. Here is everything you need to know about our shipping process.</p>',
            'sections' => [
                [
                    'title' => 'Dispatch & Delivery Timelines',
                    'content' => '<ul>
                        <li><strong>Quick Dispatch:</strong> Orders are verified and dispatched within 24 hours of placement.</li>
                        <li><strong>Metro Cities:</strong> Delivered within 2 to 3 business days.</li>
                        <li><strong>Rest of India:</strong> Delivered within 3 to 5 business days.</li>
                        <li><strong>Live Tracking:</strong> Real-time tracking link sent via SMS and WhatsApp as soon as your package is dispatched.</li>
                    </ul>'
                ],
                [
                    'title' => 'Shipping Fees & Cash on Delivery (COD)',
                    'content' => '<ul>
                        <li><strong>Free Shipping:</strong> Free delivery on all orders above ₹499 across all serviceable pincodes.</li>
                        <li><strong>Standard Shipping:</strong> A flat ₹49 fee applies on orders under ₹499.</li>
                        <li><strong>Cash on Delivery (COD):</strong> Available across most serviceable pincodes with simple OTP verification at delivery.</li>
                    </ul>'
                ]
            ]
        ];

        // 5. CANCELLATION POLICY
        $cancellationData = [
            'desc' => '1-click order cancellations before dispatch and instant refunds.',
            'intro' => '<p class="lead">Plans change, and we understand. At <strong>' . $storeName . '</strong>, we offer a simple, 1-click cancellation process before your order is dispatched.</p>',
            'sections' => [
                [
                    'title' => 'Cancellation Before Dispatch',
                    'content' => '<p>You can cancel your order free of charge at any time before it leaves our warehouse:</p>
                    <ul>
                        <li>Go to <strong>My Account &rarr; My Orders</strong>.</li>
                        <li>Click <strong>Cancel Order</strong> on your order details page.</li>
                        <li>Select a reason and confirm.</li>
                        <li>Prepaid orders receive an <strong>instant 100% refund</strong> to your ShopCalm Wallet or original payment method.</li>
                    </ul>'
                ],
                [
                    'title' => 'Cancellation After Dispatch',
                    'content' => '<p>If your order is already in transit with couriers:</p>
                    <ul>
                        <li>Cancellations cannot be made once dispatched due to our No Return policy.</li>
                        <li>If a package is rejected at the doorstep, it will be marked as returned to origin (RTO).</li>
                    </ul>'
                ]
            ]
        ];

        // 6. ABOUT US DATA
        $aboutData = [
            'hero_title' => 'About ' . $storeName,
            'tagline' => 'Simple. Transparent. Trustworthy.',
            'supporting_text' => 'Verified products, honest pricing, and fast doorstep delivery.',
            'stats' => [
                ['value' => '50K+', 'label' => 'Happy Customers', 'color' => '#6366f1'],
                ['value' => '10K+', 'label' => 'Verified Products', 'color' => '#10b981'],
                ['value' => '99.8%', 'label' => 'On-Time Delivery', 'color' => '#0284c7'],
                ['value' => '4.9★', 'label' => 'Customer Rating', 'color' => '#f59e0b'],
            ],
            'our_story' => [
                'badge' => 'Our Journey',
                'title' => 'How ' . $storeName . ' Came to Life',
                'subtitle' => 'Built to make online shopping simpler and calmer.',
                'paragraphs' => [
                    'Shopping online should be simple and stress-free. We created ' . $storeName . ' to replace clutter and confusing pricing with clear terms, 100% genuine products, and dependable delivery.',
                    'Every product is verified for quality before dispatch, giving customers across India a smooth and worry-free shopping experience.'
                ],
                'quote_title' => 'Built for Peace of Mind',
                'quote_text' => 'Making everyday shopping simpler, clearer, and calmer for you.',
                'guarantee_badge' => 'Customer-First Guarantee'
            ],
            'mission' => '<p class="mb-2">To deliver an effortless shopping experience with genuine products, fair prices, and zero hidden fees.</p><p class="fw-bold text-primary mb-0">' . $storeName . ' — Shop More. Worry Less.</p>',
            'trust_commitments' => [
                'badge' => 'Customer Guarantee',
                'title' => 'Our 4 Core Customer Commitments',
                'subtitle' => 'Quality and trust backed into every single order.',
                'items' => [
                    ['title' => '100% Genuine Products', 'desc' => 'Direct from verified brands and distributors.', 'icon' => 'bi-patch-check-fill', 'color' => '#10b981'],
                    ['title' => 'Zero Hidden Fees', 'desc' => 'Clear checkout prices with no surprise charges.', 'icon' => 'bi-tag-fill', 'color' => '#6366f1'],
                    ['title' => 'Insured Safe Delivery', 'desc' => 'Secure packaging with live courier tracking.', 'icon' => 'bi-shield-fill-check', 'color' => '#06b6d4'],
                    ['title' => 'Instant Refund Assurance', 'desc' => 'Quick refunds directly to your source account.', 'icon' => 'bi-arrow-clockwise', 'color' => '#8b5cf6']
                ]
            ],
            'focus_areas' => [
                ['title' => 'Curated Authenticity', 'desc' => 'Verified product specifications and brand authenticity.'],
                ['title' => 'Frictionless Checkout', 'desc' => 'Fast browsing, smart search, and instant checkout.'],
                ['title' => 'Bank-Grade Security', 'desc' => '256-bit SSL encryption protecting payments and data.'],
                ['title' => 'Fast Dispatch & Tracking', 'desc' => 'Prompt dispatch with live SMS and WhatsApp tracking.'],
                ['title' => 'Easy Cancellations', 'desc' => '1-click cancellation before dispatch with instant refund.'],
                ['title' => '24/7 Dedicated Helpdesk', 'desc' => 'Prompt support whenever you need help with an order.']
            ],
            'cta' => [
                'title' => 'Ready to Experience Great Shopping?',
                'subtitle' => 'Discover thousands of verified products with fast doorstep delivery.'
            ]
        ];

        // 7. CONTACT US DATA
        $contactData = [
            'hero_title' => 'Get in Touch with ' . $storeName,
            'hero_subtitle' => 'Have questions about your order, tracking, or products? Our dedicated support team is here to assist you 24/7.',
            'info_title' => 'Customer Care & Support',
            'info_subtitle' => 'Reach out through any channel below. Our customer support agents respond promptly.',
            'form_title' => 'Send us a message'
        ];

        // 8. COOKIE POLICY DATA
        $cookieData = [
            'desc' => 'How we use essential cookies to keep your shopping experience smooth.',
            'intro' => '<p class="lead">This Cookie Policy explains how <strong>' . $storeName . '</strong> uses simple cookies to make your browsing and checkout experience seamless.</p>',
            'sections' => [
                [
                    'title' => 'What Are Cookies?',
                    'content' => '<p>Cookies are small text files stored in your browser to remember your cart items, login sessions, and preferences.</p>'
                ],
                [
                    'title' => 'Types of Cookies We Use',
                    'content' => '<ul>
                        <li><strong>Essential Cookies:</strong> Required to keep you signed in, remember your cart items, and complete secure checkout.</li>
                        <li><strong>Preference Cookies:</strong> Remember your delivery pincode and store display preferences.</li>
                        <li><strong>Security Cookies:</strong> Protect against unauthorized access and keep payment processing safe.</li>
                    </ul>'
                ],
                [
                    'title' => 'Managing Your Cookie Preferences',
                    'content' => '<p>You can disable non-essential cookies via your browser settings. Please note that disabling essential cookies may prevent you from adding items to your cart or placing an order.</p>'
                ]
            ]
        ];

        // 9. DISCLAIMER DATA
        $disclaimerData = [
            'desc' => 'General information regarding product listings, warranties, and platform use.',
            'intro' => '<p class="lead">General terms regarding product listings and platform usage at <strong>' . $storeName . '</strong>.</p>',
            'sections' => [
                [
                    'title' => 'Brand Trademarks & Warranties',
                    'content' => '<p>All brand names and logos belong to their respective trademark holders. ' . $storeName . ' operates as an authorized retailer. Product warranties, where applicable, are serviced directly by original brand partners.</p>'
                ],
                [
                    'title' => 'Listing Accuracy',
                    'content' => '<p>We take every reasonable measure to ensure that product images, specs, and prices are accurate. In the rare case of an unintentional technical or inventory error, we reserve the right to cancel the order and provide a 100% refund.</p>'
                ]
            ]
        ];

        // 10. FAQ DATA (Categorized to match UI filter chips)
        $faqData = [
            'hero_title' => 'Frequently Asked Questions',
            'hero_subtitle' => 'Quick answers to common questions about orders, shipping, returns, and payments.',
            'faqs' => [
                // Orders & Tracking
                [
                    'question' => 'How can I track my order status?',
                    'answer' => '<p>Once your order is dispatched, a live tracking link is sent directly to your registered <strong>WhatsApp number and SMS</strong>. You can also track your package anytime under <strong>My Account &rarr; My Orders</strong>.</p>',
                    'category' => 'Orders & Tracking',
                    'icon' => 'bi-truck'
                ],
                [
                    'question' => 'How do I cancel my order before dispatch?',
                    'answer' => '<p>You can cancel free of charge before your order is dispatched. Go to <strong>My Account &rarr; My Orders</strong> and click <em>Cancel Order</em>. Prepaid orders receive an instant 100% refund.</p>',
                    'category' => 'Orders & Tracking',
                    'icon' => 'bi-x-circle'
                ],
                [
                    'question' => 'Can I modify my delivery address after placing an order?',
                    'answer' => '<p>If your package has not been dispatched, our team can help update your address. Please contact us via WhatsApp or email at <a href="mailto:' . $supportEmail . '">' . $supportEmail . '</a> with your Order ID.</p>',
                    'category' => 'Orders & Tracking',
                    'icon' => 'bi-geo-alt'
                ],
                [
                    'question' => 'How do I know if my order has been confirmed?',
                    'answer' => '<p>You will receive an immediate confirmation SMS and WhatsApp message with your Order ID right after checkout. You can also view it anytime under <strong>My Orders</strong>.</p>',
                    'category' => 'Orders & Tracking',
                    'icon' => 'bi-check-circle'
                ],

                // Shipping & Delivery
                [
                    'question' => 'What are the delivery timelines and shipping charges?',
                    'answer' => '<p>We offer <strong>Free Shipping on all orders above ₹499</strong> across all serviceable pincodes. Orders below ₹499 incur a flat ₹49 fee. Metro orders deliver within 2 to 3 business days; rest of India delivers within 3 to 5 business days.</p>',
                    'category' => 'Shipping & Delivery',
                    'icon' => 'bi-box-seam'
                ],
                [
                    'question' => 'Which courier partners handle deliveries?',
                    'answer' => '<p>We partner with leading, verified logistics carriers including <strong>Delhivery, Shiprocket, BlueDart, and Shadowfax</strong> for fast and safe doorstep delivery.</p>',
                    'category' => 'Shipping & Delivery',
                    'icon' => 'bi-send'
                ],
                [
                    'question' => 'What should I do if my package delivery is delayed?',
                    'answer' => '<p>Check your live tracking link on WhatsApp for courier updates. If your package is delayed past the estimated date, email <a href="mailto:' . $supportEmail . '">' . $supportEmail . '</a> for immediate escalation.</p>',
                    'category' => 'Shipping & Delivery',
                    'icon' => 'bi-hourglass-split'
                ],

                // Returns & Quality
                [
                    'question' => 'What is ShopCalm\'s Return and Replacement Policy?',
                    'answer' => '<p>ShopCalm maintains a <strong>Strict No Return & No Replacement Policy</strong> once an order is delivered. Free 100% cancellation is available anytime before your package is dispatched.</p>',
                    'category' => 'Returns & Quality',
                    'icon' => 'bi-shield-check'
                ],
                [
                    'question' => 'What if I receive a damaged or incorrect product?',
                    'answer' => '<p>In the rare event of transit damage or an incorrect item, report it within <strong>24 hours of delivery</strong> to <a href="mailto:' . $supportEmail . '">' . $supportEmail . '</a> with an unboxing video. Our team will verify and resolve it promptly.</p>',
                    'category' => 'Returns & Quality',
                    'icon' => 'bi-exclamation-triangle'
                ],
                [
                    'question' => 'Are all products sold on ShopCalm authentic?',
                    'answer' => '<p>Yes, 100%. All products are sourced directly from authorized brands and distributors with genuine quality and tamper-evident packaging.</p>',
                    'category' => 'Returns & Quality',
                    'icon' => 'bi-patch-check'
                ],

                // Payments & COD
                [
                    'question' => 'What payment methods do you accept?',
                    'answer' => '<p>We accept <strong>UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards, NetBanking</strong> via Razorpay, <strong>ShopCalm Wallet balance</strong>, and <strong>Cash on Delivery (COD)</strong>.</p>',
                    'category' => 'Payments & COD',
                    'icon' => 'bi-credit-card-2-front'
                ],
                [
                    'question' => 'How does ShopCalm Wallet balance work?',
                    'answer' => '<p>Cancelled prepaid orders and promotional credits are deposited instantly to your Wallet. Wallet balance can be used at checkout with 1-click instant confirmation.</p>',
                    'category' => 'Payments & COD',
                    'icon' => 'bi-wallet2'
                ],
                [
                    'question' => 'How long does a refund take for cancelled orders?',
                    'answer' => '<p><strong>ShopCalm Wallet refunds are instant.</strong> Refunds to your original payment method (UPI/Card/Bank) reflect within 3 to 5 business days via Razorpay.</p>',
                    'category' => 'Payments & COD',
                    'icon' => 'bi-arrow-clockwise'
                ],
                [
                    'question' => 'Is Cash on Delivery (COD) available for my area?',
                    'answer' => '<p>Yes, COD is available across most pincodes in India with simple OTP verification at the time of delivery.</p>',
                    'category' => 'Payments & COD',
                    'icon' => 'bi-cash-coin'
                ]
            ]
        ];

        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => json_encode($aboutData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'About Us - ' . $storeName,
                'meta_description' => 'Learn about ' . $storeName . ', our mission, vision, and customer-first shopping promise.',
                'is_active' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => json_encode($contactData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Contact Us & Customer Support | ' . $storeName,
                'meta_description' => 'Get in touch with the ' . $storeName . ' support team. We are available 24/7 to help you with orders, inquiries, and assistance.',
                'is_active' => true,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'content' => json_encode($termsData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Terms & Conditions - Customer Agreement | ' . $storeName,
                'meta_description' => 'Clear and simple terms for shopping at ' . $storeName . '. Pricing, order guidelines, and customer policies.',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => json_encode($privacyData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Privacy Policy | ' . $storeName,
                'meta_description' => 'Learn how ' . $storeName . ' protects your personal data and privacy. Safe, secure, and encrypted.',
                'is_active' => true,
            ],
            [
                'title' => 'Return Policy',
                'slug' => 'return-refund-policy',
                'content' => json_encode($returnData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Strict No Return & No Replacement Policy | ' . $storeName,
                'meta_description' => 'Read our clear No Return and No Replacement Policy at ' . $storeName . '. Easy pre-dispatch cancellations and instant refunds.',
                'is_active' => true,
            ],
            [
                'title' => 'Shipping Policy',
                'slug' => 'shipping-policy',
                'content' => json_encode($shippingData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Shipping & Delivery Policy | ' . $storeName,
                'meta_description' => 'Fast 2-5 day delivery across India. Free shipping on orders above ₹499 with real-time WhatsApp tracking.',
                'is_active' => true,
            ],
            [
                'title' => 'Cancellation Policy',
                'slug' => 'cancellation-policy',
                'content' => json_encode($cancellationData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Order Cancellation Policy | ' . $storeName,
                'meta_description' => 'Free order cancellation before dispatch with instant refund processing at ' . $storeName . '.',
                'is_active' => true,
            ],
            [
                'title' => 'Help & Frequently Asked Questions',
                'slug' => 'faq',
                'content' => json_encode($faqData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Help & Frequently Asked Questions (FAQ) | ' . $storeName,
                'meta_description' => 'Find quick answers to common questions about orders, shipping, payments, returns, and wallet balance at ' . $storeName . '.',
                'is_active' => true,
            ],
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookie-policy',
                'content' => json_encode($cookieData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Cookie Policy | ' . $storeName,
                'meta_description' => 'Learn how ' . $storeName . ' uses cookies and session management to provide a secure and customized shopping experience.',
                'is_active' => true,
            ],
            [
                'title' => 'Disclaimer',
                'slug' => 'disclaimer',
                'content' => json_encode($disclaimerData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Disclaimer | ' . $storeName,
                'meta_description' => 'Official disclaimer regarding product authenticity, manufacturer warranties, and platform usage at ' . $storeName . '.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
