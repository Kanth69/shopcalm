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
        $storeName = config('app.name', 'ShopCalm');

        // 1. PRIVACY POLICY
        $privacyData = [
            'intro' => '<p class="lead">At <strong>' . $storeName . '</strong> (operated by WiseKart E-Commerce Solutions), accessible from shopcalm.in, protecting your personal data and upholding your digital privacy is our topmost priority. This Privacy Policy outlines how we collect, process, store, and safeguard your information in accordance with the <strong>Digital Personal Data Protection Act, 2023 (DPDP Act)</strong>, the <strong>Information Technology Act, 2000 (Section 43A)</strong>, and the <strong>Consumer Protection (E-Commerce) Rules, 2020</strong> of India.</p>',
            'sections' => [
                [
                    'title' => 'Personal Data We Collect',
                    'content' => '<p>We collect digital personal data that you explicitly provide to us when creating an account, placing an order, or interacting with our store:</p>
                    <ul>
                        <li><strong>Identity & Account Details:</strong> Full Name, Primary Mobile Number, Email Address (Optional), and Hashed Password (`Bcrypt`).</li>
                        <li><strong>Delivery & Logistics Data:</strong> Recipient Name, Contact Phone Number, Flat/House No., Street Address, Landmark, City, State, Pincode, and Geolocation coordinates (for pincode serviceability).</li>
                        <li><strong>Transaction & Payment Logs:</strong> Order purchase history, items bought, total bill breakdown, payment method (COD or Online), and Gateway Transaction Reference IDs (Razorpay/PhonePe IDs). <em>Note: We do NOT store credit card numbers, CVVs, netbanking passwords, or UPI PINs. All payment authentication is processed securely by RBI-approved payment gateways.</em></li>
                        <li><strong>Communication & Review Data:</strong> WhatsApp OTP verification logs, customer support queries, written product reviews, ratings, and uploaded review images.</li>
                    </ul>'
                ],
                [
                    'title' => 'Purpose & Legal Basis of Processing',
                    'content' => '<p>Under Section 4 of the DPDP Act 2023, your personal data is processed strictly for specified, lawful, and necessary business purposes:</p>
                    <ul>
                        <li><strong>Order Fulfillment & Delivery:</strong> To process your orders, generate GST tax invoices, and dispatch products via our authorized courier logistics partners.</li>
                        <li><strong>WhatsApp & SMS Notifications:</strong> To send real-time order status updates, WhatsApp OTPs, tracking links, and delivery notifications.</li>
                        <li><strong>Customer Support & Grievance Redressal:</strong> To assist you with order inquiries, pre-dispatch cancellations, wallet credits, and support queries.</li>
                        <li><strong>Legal & Tax Compliance:</strong> To comply with mandatory record retention under the Central Goods and Services Tax (CGST) Act, 2017 and Income Tax Act, 1961.</li>
                    </ul>'
                ],
                [
                    'title' => 'Third-Party Data Sharing & Logistics',
                    'content' => '<p>We strictly respect your privacy. <strong>We NEVER sell, rent, or trade your personal data to third-party marketing companies.</strong> Data is shared only with trusted operational partners under strict data processing agreements:</p>
                    <ul>
                        <li><strong>Logistics & Courier Partners:</strong> Delivery details (Name, Address, Phone) are shared with verified logistics partners (e.g., Shiprocket, Delhivery, BlueDart) solely for order delivery.</li>
                        <li><strong>Payment Gateways:</strong> Order amounts and reference numbers are passed to PCI-DSS compliant payment gateways (Razorpay/PhonePe) for secure payment processing.</li>
                        <li><strong>Messaging Service Providers:</strong> Phone numbers are processed through Meta Cloud API for sending official WhatsApp notifications and OTP codes.</li>
                        <li><strong>Law Enforcement & Statutory Bodies:</strong> Information may be disclosed if required by law, court order, or government authority under applicable Indian legal statutes.</li>
                    </ul>'
                ],
                [
                    'title' => 'Data Retention & Tax Compliance',
                    'content' => '<p>We retain your personal data only as long as necessary to fulfill the purposes for which it was collected or to comply with statutory legal requirements:</p>
                    <ul>
                        <li><strong>Account Data:</strong> Retained while your account remains active. If you request account closure, active profile records are removed or anonymized within 30 days.</li>
                        <li><strong>Order & Invoicing Records:</strong> Under <strong>Section 36 of the CGST Act 2017</strong> and Income Tax regulations, order tax invoices and transaction ledgers must be retained for a mandatory period of <strong>6 Years</strong> for statutory tax audits, even if a user deletes their account.</li>
                    </ul>'
                ],
                [
                    'title' => 'Your Rights Under DPDP Act 2023',
                    'content' => '<p>As a Data Principal under the Digital Personal Data Protection Act, 2023, you hold the following statutory rights regarding your personal data:</p>
                    <ul>
                        <li><strong>Right to Access Information:</strong> You can view your stored profile data, saved addresses, wallet balance, and order history at any time from your Account Dashboard.</li>
                        <li><strong>Right to Correction & Updating:</strong> You can edit or correct incomplete or inaccurate personal information directly from your profile settings.</li>
                        <li><strong>Right to Withdrawal of Consent:</strong> You may unsubscribe from marketing newsletters or promotional communications at any time.</li>
                        <li><strong>Right to Erasure / Account Deletion:</strong> You have the right to request the deletion of your account and associated personal data by contacting our Grievance Officer.</li>
                    </ul>'
                ],
                [
                    'title' => 'Data Security Safeguards',
                    'content' => '<p>We employ robust technical and organizational security measures to protect your digital personal data against unauthorized access, loss, or disclosure:</p>
                    <ul>
                        <li><strong>256-Bit SSL Encryption:</strong> All data transmitted between your browser/app and our servers is encrypted using TLS/SSL security protocols.</li>
                        <li><strong>Password Hashing:</strong> User passwords are encrypted using one-way `Bcrypt` hashing algorithms and are never accessible in plain text to anyone, including store administrators.</li>
                        <li><strong>Access Control:</strong> Database access is strictly restricted to authorized server processes behind secure firewalls.</li>
                    </ul>'
                ],
                [
                    'title' => 'Grievance Redressal Officer (E-Commerce Rules 2020)',
                    'content' => '<p>In compliance with Rule 5(9) of the <strong>Consumer Protection (E-Commerce) Rules, 2020</strong>, the name and contact details of our designated Grievance Officer for privacy and customer concerns are provided below:</p>
                    <div class="p-3 bg-light rounded border">
                        <p class="mb-1"><strong>Grievance Redressal Officer:</strong> Nodal Officer - Customer Experience</p>
                        <p class="mb-1"><strong>Entity:</strong> ' . $storeName . ' E-Commerce Solutions</p>
                        <p class="mb-1"><strong>Email:</strong> <a href="mailto:grievance@shopcalm.in">grievance@shopcalm.in</a></p>
                        <p class="mb-1"><strong>Support Helpline:</strong> +91 98765 43210 (Mon - Sat: 9:00 AM - 6:00 PM IST)</p>
                        <p class="mb-0"><strong>Response Turnaround:</strong> Acknowledgment within 48 hours; resolution within 30 days.</p>
                    </div>'
                ]
            ]
        ];

        // 2. TERMS & CONDITIONS
        $termsData = [
            'intro' => '<p class="lead">Welcome to <strong>' . $storeName . '</strong>. These Terms & Conditions constitute a legally binding agreement between you ("Customer", "User") and ' . $storeName . ' E-Commerce Solutions regarding your access to and use of shopcalm.in and our mobile application. By accessing our platform or making a purchase, you agree to be bound by these Terms and our Privacy Policy.</p>',
            'sections' => [
                [
                    'title' => 'Account Registration & User Eligibility',
                    'content' => '<p>To purchase products on ' . $storeName . ', you must be at least 18 years of age or accessing under the supervision of a parent or legal guardian:</p>
                    <ul>
                        <li>You agree to provide accurate, current, and complete mobile number and address information during registration and checkout.</li>
                        <li>You are responsible for maintaining the confidentiality of your account credentials and OTP codes.</li>
                        <li>' . $storeName . ' reserves the right to suspend or terminate accounts that provide false information or engage in fraudulent activities.</li>
                    </ul>'
                ],
                [
                    'title' => 'Product Information, Pricing & Tax Disclosures',
                    'content' => '<p>In compliance with the Consumer Protection (E-Commerce) Rules 2020:</p>
                    <ul>
                        <li><strong>Transparent Pricing:</strong> All product prices displayed on the store are listed in Indian Rupees (₹ INR) and are inclusive of all applicable Goods and Services Tax (GST).</li>
                        <li><strong>No Hidden Charges:</strong> Total order costs, including delivery fees and applied discount coupons, are clearly broken down prior to order confirmation.</li>
                        <li><strong>Pricing Errors:</strong> In the rare event of a technical pricing error or typographical mistake, ' . $storeName . ' reserves the right to cancel affected orders and issue a full refund.</li>
                    </ul>'
                ],
                [
                    'title' => 'Order Placement & Pre-Dispatch Cancellation',
                    'content' => '<p>When you place an order on ' . $storeName . ':</p>
                    <ul>
                        <li><strong>Order Confirmation:</strong> Order confirmation is sent via SMS, WhatsApp, and email upon successful checkout.</li>
                        <li><strong>Free Pre-Dispatch Cancellation:</strong> You may cancel your order free of cost anytime before it is marked as "Dispatched" from your account order dashboard.</li>
                        <li><strong>Prepaid Refund for Cancelled Orders:</strong> Prepaid orders cancelled prior to dispatch will receive an instant 100% refund credited to your WiseKart Wallet or original payment method within 3 to 5 business days.</li>
                    </ul>'
                ],
                [
                    'title' => 'Strict No Return & No Replacement Policy',
                    'content' => '<p>In compliance with Consumer Protection (E-Commerce) Rules 2020 disclosures:</p>
                    <ul>
                        <li><strong>All Sales Are Final:</strong> ' . $storeName . ' maintains a strict <strong>No Return and No Replacement Policy</strong> once an order is dispatched and delivered.</li>
                        <li><strong>Post-Delivery Non-Returnable:</strong> Once a package has been successfully delivered to the customer address, returns, exchanges, or replacements are not accepted under any circumstances.</li>
                        <li><strong>Quality Assurance:</strong> All items undergo strict multi-point quality inspection and tamper-proof packaging prior to dispatch.</li>
                    </ul>'
                ],
                [
                    'title' => 'Delivery & Shipping Terms',
                    'content' => '<p>We aim to deliver your orders quickly and reliably:</p>
                    <ul>
                        <li><strong>Serviceability Check:</strong> Delivery is subject to pincode serviceability verified during checkout.</li>
                        <li><strong>Delivery Timelines:</strong> Standard orders are typically delivered within 2 to 5 business days across India depending on delivery location.</li>
                        <li><strong>Delivery Verification:</strong> Delivery partners may request OTP verification at the time of handing over high-value packages.</li>
                    </ul>'
                ],
                [
                    'title' => 'Governing Law & Jurisdiction',
                    'content' => '<p>These Terms & Conditions shall be governed by and construed in accordance with the laws of India. Any legal disputes or claims arising out of the use of ' . $storeName . ' shall be subject to the exclusive jurisdiction of the competent courts in Bengaluru, Karnataka, India.</p>'
                ]
            ]
        ];

        // 3. RETURN & REFUND POLICY (STRICT NO RETURN & NO REPLACEMENT)
        $returnData = [
            'intro' => '<p class="lead">Thank you for shopping at <strong>' . $storeName . '</strong>. Please read our policy carefully regarding returns, replacements, and refunds before placing an order.</p>',
            'sections' => [
                [
                    'title' => 'Strict No Return & No Replacement Policy',
                    'content' => '<p>At <strong>' . $storeName . '</strong>, we follow a strict <strong>No Return & No Replacement Policy</strong> across all product categories:</p>
                    <ul>
                        <li><strong>All Sales Final:</strong> Once an order is successfully dispatched and delivered to your address, products cannot be returned, exchanged, or replaced under any circumstances.</li>
                        <li><strong>Pre-Dispatch Cancellations Only:</strong> Returns are not accepted post-delivery. If you wish to cancel your purchase, you must cancel the order before it is dispatched from our fulfillment warehouse.</li>
                        <li><strong>Quality Check Guarantee:</strong> Every item is thoroughly inspected for quality and securely packed with tamper-evident seals before handing over to courier partners.</li>
                    </ul>'
                ],
                [
                    'title' => 'Damaged or Wrong Product Transit Exceptions',
                    'content' => '<p>In the rare event that you receive a physically damaged package or wrong product delivered due to transit error:</p>
                    <ul>
                        <li>You must report the issue to our support team within <strong>24 hours</strong> of package delivery by emailing <a href="mailto:support@shopcalm.in">support@shopcalm.in</a> or contacting our support helpline.</li>
                        <li>An <strong>unboxing video</strong> clearly showing the shipping label and package opening must be provided for claim verification.</li>
                        <li>Upon verification by our audit team, an appropriate resolution or replacement approval will be processed on a case-by-case basis.</li>
                    </ul>'
                ],
                [
                    'title' => 'Refund Processing for Pre-Dispatch Cancellations',
                    'content' => '<p>For eligible cancellations initiated <strong>before package dispatch</strong>:</p>
                    <ul>
                        <li><strong>WiseKart Wallet Refund:</strong> Instant 100% credit to your WiseKart Wallet balance for immediate store shopping.</li>
                        <li><strong>Original Payment Method:</strong> Prepaid card, UPI, or netbanking payments will be refunded back to the source account within 3 to 5 business days.</li>
                    </ul>'
                ]
            ]
        ];

        // 4. SHIPPING POLICY
        $shippingData = [
            'intro' => '<p class="lead">We strive to deliver your orders safely and swiftly across India. Below are the terms and conditions governing our shipping and delivery processes at <strong>' . $storeName . '</strong>.</p>',
            'sections' => [
                [
                    'title' => 'Dispatch & Delivery Timelines',
                    'content' => '<ul>
                        <li><strong>Order Processing:</strong> All orders are verified and processed within 24 hours of placement.</li>
                        <li><strong>Metro Cities:</strong> Delivery within 2 to 3 business days.</li>
                        <li><strong>Rest of India:</strong> Delivery within 3 to 5 business days.</li>
                        <li><strong>Tracking:</strong> Real-time shipment tracking details with direct courier link are sent via SMS and WhatsApp as soon as your order is dispatched.</li>
                    </ul>'
                ],
                [
                    'title' => 'Shipping Charges & Cash on Delivery (COD)',
                    'content' => '<ul>
                        <li><strong>Free Shipping:</strong> Enjoy Free Shipping on orders above ₹499 across all serviceable pincodes.</li>
                        <li><strong>Standard Shipping Fee:</strong> Nominal ₹49 shipping fee applies to orders below ₹499.</li>
                        <li><strong>Cash on Delivery (COD):</strong> COD is available across most serviceable pincodes with simple OTP delivery verification.</li>
                    </ul>'
                ]
            ]
        ];

        // 5. CANCELLATION POLICY
        $cancellationData = [
            'intro' => '<p class="lead">We understand that plans change. At <strong>' . $storeName . '</strong>, we offer a transparent and hassle-free cancellation process prior to order dispatch.</p>',
            'sections' => [
                [
                    'title' => 'Cancellation Before Dispatch',
                    'content' => '<p>You can cancel your order free of charge at any time before it has been dispatched from our fulfillment center:</p>
                    <ul>
                        <li>Go to <strong>My Account &rarr; My Orders</strong>.</li>
                        <li>Click the <strong>Cancel Order</strong> button on the order details page.</li>
                        <li>Select a reason for cancellation and confirm.</li>
                        <li>Prepaid orders cancelled before dispatch receive an <strong>instant 100% refund</strong>.</li>
                    </ul>'
                ],
                [
                    'title' => 'Cancellation After Dispatch',
                    'content' => '<p>If your order has already been dispatched and is in transit:</p>
                    <ul>
                        <li>Cancellations and returns are not permitted once dispatched due to our strict No Return Policy.</li>
                        <li>If package delivery is refused, the order will be marked as RTO (Returned to Origin) subject to applicable return courier fees.</li>
                    </ul>'
                ]
            ]
        ];

        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h1>About ' . $storeName . '</h1><p>Welcome to ' . $storeName . '. We are dedicated to providing you the best products at unbeatable prices.</p>',
                'meta_title' => 'About Us | ' . $storeName,
                'meta_description' => 'Learn about ' . $storeName . ', our mission, and our commitment to quality e-commerce.',
                'is_active' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>Get in touch with the ' . $storeName . ' team. We are here to help!</p>',
                'meta_title' => 'Contact Support | ' . $storeName,
                'meta_description' => 'Get in touch with ' . $storeName . ' customer support team.',
                'is_active' => true,
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'content' => json_encode($termsData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Terms & Conditions - Customer Agreement | ' . $storeName,
                'meta_description' => 'Terms and conditions for shopping at ' . $storeName . '. Transparent pricing, order terms, and e-commerce consumer compliance.',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => json_encode($privacyData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Privacy Policy - DPDP Act 2023 Compliant | ' . $storeName,
                'meta_description' => 'Read our DPDP Act 2023 compliant Privacy Policy. Learn how WiseKart securely collects, stores, and protects your personal data.',
                'is_active' => true,
            ],
            [
                'title' => 'Return Policy',
                'slug' => 'return-refund-policy',
                'content' => json_encode($returnData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Strict No Return & No Replacement Policy | ' . $storeName,
                'meta_description' => 'Read our No Return and No Replacement Policy at ' . $storeName . '. Pre-dispatch cancellation terms and order guidelines.',
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
                'content' => json_encode([
                    'faqs' => [
                        [
                            'question' => 'How can I track my order status?',
                            'answer' => '<p>Once your order is dispatched, real-time tracking updates are sent directly to your registered <strong>WhatsApp number and Email</strong>. You can also view live order progress anytime from your Account Dashboard under <strong>My Account &rarr; My Orders</strong>.</p>',
                            'category' => 'Orders & Tracking',
                            'icon' => 'bi-truck'
                        ],
                        [
                            'question' => 'What are the delivery timelines and shipping charges?',
                            'answer' => '<p>We offer <strong>Free Shipping on all orders above ₹499</strong>. Orders below ₹499 incur a nominal standard shipping fee of ₹49. Orders in metro cities deliver within 2-3 business days, while rest of India delivers within 3-5 business days.</p>',
                            'category' => 'Shipping & Delivery',
                            'icon' => 'bi-box-seam'
                        ],
                        [
                            'question' => 'What is your Return and Replacement Policy?',
                            'answer' => '<p>WiseKart (ShopCalm) follows a <strong>Strict No Return & No Replacement Policy</strong> once a package is dispatched and delivered. All items undergo rigorous multi-point quality inspection prior to packing. Free cancellation is permitted anytime before package dispatch.</p>',
                            'category' => 'Returns & Quality',
                            'icon' => 'bi-shield-check'
                        ],
                        [
                            'question' => 'How do I cancel my order before dispatch?',
                            'answer' => '<p>You can cancel your order free of cost anytime before dispatch by navigating to <strong>My Account &rarr; My Orders</strong> and clicking <em>Cancel Order</em>. Prepaid cancellations are refunded 100% instantly to your WiseKart Wallet or source payment account.</p>',
                            'category' => 'Orders & Tracking',
                            'icon' => 'bi-x-circle'
                        ],
                        [
                            'question' => 'What payment methods do you accept?',
                            'answer' => '<p>We accept all major <strong>UPI apps (GPay, PhonePe, Paytm, BHIM), Credit/Debit Cards, NetBanking</strong> via PCI-DSS compliant Cashfree payment gateway, <strong>WiseKart Wallet balance</strong>, and <strong>Cash on Delivery (COD)</strong>.</p>',
                            'category' => 'Payments & COD',
                            'icon' => 'bi-credit-card-2-front'
                        ],
                        [
                            'question' => 'How does WiseKart Wallet balance work?',
                            'answer' => '<p>Your WiseKart Wallet receives instant 100% credits for pre-dispatch cancellations and referral rewards. Wallet balance can be used at checkout with 1-click zero transaction fee checkout.</p>',
                            'category' => 'Payments & COD',
                            'icon' => 'bi-wallet2'
                        ]
                    ]
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Help & Frequently Asked Questions (FAQ) | ' . $storeName,
                'meta_description' => 'Find quick answers to common questions about orders, shipping, payments, returns, and wallet balance at ' . $storeName . '.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
