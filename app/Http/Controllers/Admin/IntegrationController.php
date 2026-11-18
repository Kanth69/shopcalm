<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pincode;
use App\Models\Setting;
use App\Services\DeliveryService;
use App\Services\EmailService;
use App\Services\FulfillmentService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    /**
     * Integrations Hub Overview Dashboard.
     */
    public function index()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();

        // 1. Brevo Health
        $brevoQuota = app(EmailService::class)->getAccountQuota();

        // 2. WhatsApp Health
        $hasWhatsAppToken = !empty($settings['whatsapp_api_token'] ?? env('WHATSAPP_API_TOKEN'));
        $hasWhatsAppPhoneId = !empty($settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID'));
        $whatsAppStatus = ($hasWhatsAppToken && $hasWhatsAppPhoneId) ? 'ACTIVE' : 'CONFIG_NEEDED';

        // 3. Razorpay Health
        $rzpKeyId = $settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', '');
        $isRazorpayLive = str_starts_with($rzpKeyId, 'rzp_live');
        $isRazorpayConfigured = !empty($rzpKeyId);
        $todayOnlinePayments = Payment::whereIn('status', ['SUCCESS', 'paid'])
            ->whereDate('created_at', today())
            ->where(fn($q) => $q->where('gateway', 'razorpay')->orWhere('payment_method_group', 'online')->orWhere('payment_method_group', 'upi'))
            ->sum('amount');

        // 4. LocationIQ Maps Health
        $locationiqKey = $settings['locationiq_api_key'] ?? config('services.locationiq.key');
        $isLocationiqConfigured = !empty($locationiqKey);
        $hasAddressCoords = \Illuminate\Support\Facades\Schema::hasColumn('addresses', 'latitude');
        $geocodedAddressesCount = $hasAddressCoords ? Address::whereNotNull('latitude')->whereNotNull('longitude')->count() : 0;
        $totalAddressesCount = Address::count();

        // 5. Postal Pincodes & Serviceability Health
        $totalPincodesCount = Pincode::count();
        $serviceablePincodesCount = Pincode::where('is_serviceable', true)->count();

        return view('admin.integrations.index', compact(
            'settings',
            'brevoQuota',
            'whatsAppStatus',
            'hasWhatsAppToken',
            'hasWhatsAppPhoneId',
            'isRazorpayConfigured',
            'isRazorpayLive',
            'todayOnlinePayments',
            'isLocationiqConfigured',
            'locationiqKey',
            'geocodedAddressesCount',
            'totalAddressesCount',
            'totalPincodesCount',
            'serviceablePincodesCount'
        ));
    }

    /**
     * Dedicated Brevo Email Service Hub.
     */
    public function brevo()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();
        $brevoQuota = app(EmailService::class)->getAccountQuota();

        // Fetch recent orders that triggered email invoices
        $recentEmailOrders = Order::with('user')
            ->whereNotNull('shipping_email')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.integrations.brevo', compact('settings', 'brevoQuota', 'recentEmailOrders'));
    }

    /**
     * Dispatch security audit log & email alert for sensitive integration changes.
     */
    protected function recordSecurityAudit(Request $request, string $actionTitle, array $changedFields): void
    {
        $user = auth('admin')->user() ?? auth()->user();
        $adminName = $user ? $user->name : 'Super Admin';
        $adminEmail = $user ? $user->email : 'superadmin@shopcalm.in';
        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? 'Unknown Agent';

        // 1. Permanent System Log
        \Illuminate\Support\Facades\Log::warning("[SECURITY AUDIT] {$actionTitle} performed by {$adminName} ({$adminEmail}) from IP: {$ip}", [
            'admin_id'    => $user?->id,
            'admin_email' => $adminEmail,
            'ip'          => $ip,
            'user_agent'  => $userAgent,
            'fields'      => $changedFields,
            'timestamp'   => now()->toIso8601String(),
        ]);

        // 2. Instant Email Alert (Non-blocking)
        try {
            app(EmailService::class)->sendSecurityAlert($actionTitle, [
                'admin_name'  => $adminName,
                'admin_email' => $adminEmail,
                'ip_address'  => $ip,
                'user_agent'  => $userAgent,
                'timestamp'   => now()->format('d M Y, h:i A T'),
                'changes'     => $changedFields,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("[SECURITY AUDIT] Failed to dispatch security alert email: " . $e->getMessage());
        }
    }

    /**
     * Update Brevo Credentials & Sender Profile.
     */
    public function updateBrevo(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $request->validate([
            'brevo_api_key'      => 'nullable|string|max:255',
            'brevo_sender_email' => 'nullable|email|max:255',
            'brevo_sender_name'  => 'nullable|string|max:255',
        ]);

        $changed = [];

        // Masked Secret: Only update API key if user typed a new key
        if ($request->filled('brevo_api_key')) {
            Setting::set('brevo_api_key', trim($request->brevo_api_key));
            $changed[] = 'Brevo v3 REST API Key (Updated & Encrypted)';
        }

        if ($request->has('brevo_sender_email') && $request->brevo_sender_email !== Setting::get('brevo_sender_email')) {
            Setting::set('brevo_sender_email', $request->brevo_sender_email);
            $changed[] = 'Brevo Sender Email (' . $request->brevo_sender_email . ')';
        }

        if ($request->has('brevo_sender_name') && $request->brevo_sender_name !== Setting::get('brevo_sender_name')) {
            Setting::set('brevo_sender_name', $request->brevo_sender_name);
            $changed[] = 'Brevo Sender Name (' . $request->brevo_sender_name . ')';
        }

        if (!empty($changed)) {
            $this->recordSecurityAudit($request, 'Brevo Email Service Credentials Updated', $changed);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Brevo Settings Updated', 'message' => 'Brevo REST API credentials saved successfully.'])->with('success', 'Brevo REST API credentials saved successfully.');
    }

    /**
     * Dedicated WhatsApp Cloud API Hub (Registration & Password Reset OTP).
     */
    public function whatsapp()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();

        // Recent WhatsApp Authentication OTP Logs
        $recentOtpLogs = \App\Models\OtpVerification::latest('updated_at')
            ->take(20)
            ->get();

        return view('admin.integrations.whatsapp', compact('settings', 'recentOtpLogs'));
    }

    /**
     * Update WhatsApp Cloud API Credentials & OTP Template.
     */
    public function updateWhatsApp(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $validated = $request->validate([
            'whatsapp_phone_number_id'          => 'nullable|string|max:255',
            'whatsapp_api_token'                => 'nullable|string|max:500',
            'whatsapp_template_name'            => 'nullable|string|max:255',
            'whatsapp_template_order_confirmed' => 'nullable|string|max:255',
        ]);

        $changed = [];
        foreach ($validated as $key => $value) {
            if ($key === 'whatsapp_api_token') {
                // Masked Secret: Only update if a new token was provided
                if ($request->filled('whatsapp_api_token')) {
                    Setting::set($key, trim($value));
                    $changed[] = 'WhatsApp Access Token (Updated & Encrypted)';
                }
            } else {
                if ($value !== Setting::get($key)) {
                    Setting::set($key, $value);
                    $changed[] = ucwords(str_replace('_', ' ', $key));
                }
            }
        }

        if (!empty($changed)) {
            $this->recordSecurityAudit($request, 'WhatsApp Cloud API Configuration Updated', $changed);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'WhatsApp Config Saved', 'message' => 'Meta WhatsApp Business API credentials & OTP template updated.'])->with('success', 'Meta WhatsApp Business API credentials & OTP template updated.');
    }

    /**
     * Dedicated Razorpay Payment Gateway Hub.
     */
    public function razorpay()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();

        $stats = [
            'total_online_revenue' => Payment::where('status', 'SUCCESS')->whereIn('payment_method_group', ['online', 'upi', 'razorpay'])->sum('amount'),
            'today_online_revenue' => Payment::where('status', 'SUCCESS')->whereDate('created_at', today())->whereIn('payment_method_group', ['online', 'upi', 'razorpay'])->sum('amount'),
            'successful_count'     => Payment::where('status', 'SUCCESS')->count(),
            'doorstep_upi_count'   => Payment::where('payment_method_group', 'upi')->count(),
        ];

        $recentPayments = Payment::with('order.user')
            ->latest()
            ->paginate(15);

        return view('admin.integrations.razorpay', compact('settings', 'stats', 'recentPayments'));
    }

    /**
     * Update Razorpay API Keys.
     */
    public function updateRazorpay(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $request->validate([
            'razorpay_key_id'     => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
        ]);

        $changed = [];

        if ($request->has('razorpay_key_id') && $request->razorpay_key_id !== Setting::get('razorpay_key_id')) {
            Setting::set('razorpay_key_id', trim((string) $request->razorpay_key_id));
            $changed[] = 'Razorpay Key ID';
        }

        // Masked Secret: Only update secret if user entered a new secret
        if ($request->filled('razorpay_key_secret')) {
            Setting::set('razorpay_key_secret', trim((string) $request->razorpay_key_secret));
            $changed[] = 'Razorpay Key Secret (Updated & Encrypted)';
        }

        if (!empty($changed)) {
            $this->recordSecurityAudit($request, 'Razorpay Payment Gateway Credentials Updated', $changed);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Razorpay Keys Saved', 'message' => 'Razorpay API credentials updated successfully.'])->with('success', 'Razorpay API credentials updated successfully.');
    }

    /**
     * Dedicated LocationIQ & Maps Geocoding Hub.
     */
    public function locationiq()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();
        $locationiqKey = $settings['locationiq_api_key'] ?? config('services.locationiq.key');
        $isConfigured = !empty($locationiqKey);

        $hasAddressCoords = \Illuminate\Support\Facades\Schema::hasColumn('addresses', 'latitude');
        $hasOrderCoords = \Illuminate\Support\Facades\Schema::hasColumn('orders', 'shipping_latitude');

        $stats = [
            'geocoded_addresses' => $hasAddressCoords ? Address::whereNotNull('latitude')->whereNotNull('longitude')->count() : 0,
            'total_addresses'    => Address::count(),
            'geocoded_orders'    => $hasOrderCoords ? Order::whereNotNull('shipping_latitude')->whereNotNull('shipping_longitude')->count() : 0,
            'total_orders'       => Order::count(),
        ];

        // Sample recent customer addresses with coordinates for verification
        $recentGeocodedAddresses = $hasAddressCoords 
            ? Address::with('user')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest()
                ->take(8)
                ->get()
            : collect();

        return view('admin.integrations.locationiq', compact('settings', 'locationiqKey', 'isConfigured', 'stats', 'recentGeocodedAddresses'));
    }

    /**
     * Validate LocationIQ access token against live API.
     */
    private function validateLocationiqToken(string $token): array
    {
        $token = trim($token);

        if (empty($token)) {
            return [
                'valid' => false,
                'error' => 'LocationIQ API Token cannot be empty.',
            ];
        }

        // LocationIQ public tokens start with "pk."
        if (!str_starts_with($token, 'pk.')) {
            return [
                'valid' => false,
                'error' => 'Invalid token format. LocationIQ public client access tokens must start with "pk." (e.g. pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx).',
            ];
        }

        if (strlen($token) < 20) {
            return [
                'valid' => false,
                'error' => 'Token is too short to be a valid LocationIQ key.',
            ];
        }

        // Test with live reverse geocode against LocationIQ US & EU clusters
        $endpoints = [
            'https://us1.locationiq.com/v1/reverse',
            'https://eu1.locationiq.com/v1/reverse',
        ];

        $lastError = 'LocationIQ connection failed';

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::timeout(6)->get($endpoint, [
                    'key'             => $token,
                    'lat'             => 12.9716,
                    'lon'             => 77.5946,
                    'format'          => 'json',
                    'accept-language' => 'en',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'valid'        => true,
                        'display_name' => $data['display_name'] ?? 'Bengaluru, Karnataka, India',
                    ];
                }

                $error = $response->json('error') ?? ('HTTP ' . $response->status() . ': ' . Str::limit($response->body(), 80));
                return [
                    'valid' => false,
                    'error' => $error,
                ];
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return [
            'valid' => false,
            'error' => 'Connection timed out: ' . $lastError,
        ];
    }

    /**
     * Update LocationIQ Access Token with strict live API verification.
     */
    public function updateLocationiq(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $request->validate([
            'locationiq_api_key' => 'nullable|string|max:255',
        ]);

        $token = trim((string) $request->locationiq_api_key);

        // Check if user submitted blank while already having a configured token (preserve it)
        $existingToken = Setting::get('locationiq_api_key') ?: config('services.locationiq.key');
        if (empty($token) && !empty($existingToken) && !$request->boolean('clear_token')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Existing LocationIQ Token preserved.',
                    'display_name' => 'Configured Token Active',
                ]);
            }
            return back()->with('toast', [
                'type'    => 'info',
                'title'   => 'Token Preserved',
                'message' => 'Your existing LocationIQ token was preserved unchanged.',
            ])->with('info', 'Your existing LocationIQ token was preserved unchanged.');
        }

        // Allow explicitly clearing the token (falls back to OpenStreetMap)
        if (empty($token)) {
            Setting::set('locationiq_api_key', '');
            $this->recordSecurityAudit($request, 'LocationIQ Maps Access Token Cleared', ['Token removed -> fallback to OpenStreetMap']);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'LocationIQ API Token cleared. Switched to OpenStreetMap Nominatim fallback.',
                    'display_name' => 'OpenStreetMap Fallback Active',
                ]);
            }

            return back()->with('toast', [
                'type'    => 'info',
                'title'   => 'Token Cleared',
                'message' => 'LocationIQ API Token removed. System will use free OpenStreetMap Nominatim.',
            ])->with('info', 'LocationIQ API Token removed. System will use free OpenStreetMap Nominatim.');
        }

        // Live validation against LocationIQ
        $validation = $this->validateLocationiqToken($token);

        if (!$validation['valid']) {
            $errorMessage = $validation['error'] ?? 'Invalid token';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'LocationIQ token verification failed: ' . $errorMessage,
                ], 422);
            }

            return back()->withInput()->with('toast', [
                'type'    => 'error',
                'title'   => 'Token Verification Failed',
                'message' => 'LocationIQ rejected this token: ' . $errorMessage . '. The token was NOT saved.',
            ])->with('error', 'LocationIQ Token Verification Failed: ' . $errorMessage . ' (Token was NOT saved). Please check your token from LocationIQ Dashboard.');
        }

        // Token is 100% verified and active! Save to settings
        Setting::set('locationiq_api_key', $token);
        $displayName = $validation['display_name'] ?? 'Bengaluru, India';

        $this->recordSecurityAudit($request, 'LocationIQ Maps Access Token Updated', ['LocationIQ Public Token Verified & Saved (' . Str::limit($token, 8, '...') . ')']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'LocationIQ Token Verified & Saved! Live reverse geocoding is working.',
                'display_name' => $displayName,
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'LocationIQ Token Verified & Saved',
            'message' => 'Token verified successfully with LocationIQ! Live reverse geocoding works: ' . Str::limit($displayName, 60),
        ])->with('success', 'LocationIQ Access Token verified and saved successfully! (' . Str::limit($displayName, 60) . ')');
    }

    /**
     * Test LocationIQ Connection via live reverse geocode.
     */
    public function testLocationiq(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $key = trim((string) $request->locationiq_api_key) 
            ?: (Setting::get('locationiq_api_key') ?: config('services.locationiq.key'));

        if (empty($key)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a LocationIQ API token first.',
            ], 422);
        }

        $validation = $this->validateLocationiqToken($key);

        if ($validation['valid']) {
            return response()->json([
                'success'      => true,
                'message'      => 'Connection Verified! Successfully reverse-geocoded sample coordinates with LocationIQ.',
                'display_name' => $validation['display_name'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'LocationIQ Error: ' . ($validation['error'] ?? 'Verification failed'),
        ], 400);
    }

    /**
     * Test OpenStreetMap Nominatim Fallback Connection.
     */
    public function testOsm(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'ShopCalm-App/1.0 (contact@shopcalm.in)'])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat'             => 12.9716,
                    'lon'             => 77.5946,
                    'format'          => 'json',
                    'accept-language' => 'en',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success'      => true,
                    'message'      => 'OpenStreetMap Nominatim is Active & Reachable (Zero-Downtime Fallback Ready)!',
                    'display_name' => $data['display_name'] ?? 'Bengaluru, Karnataka, India',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nominatim returned HTTP ' . $response->status(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nominatim connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dedicated Postal Pincodes & Delivery Serviceability Hub.
     */
    public function pincodes()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();

        $stats = [
            'total_pincodes'       => Pincode::count(),
            'serviceable_pincodes' => Pincode::where('is_serviceable', true)->count(),
            'cod_available'        => Pincode::where('is_cod_available', true)->count(),
            'total_cities'         => Pincode::distinct('city')->count('city'),
            'total_states'         => Pincode::distinct('state')->count('state'),
        ];

        // Sample 10 recently updated pincodes
        $recentPincodes = Pincode::latest('updated_at')->take(10)->get();

        return view('admin.integrations.pincodes', compact('settings', 'stats', 'recentPincodes'));
    }

    /**
     * Test Pincode Lookup via India Post API + Local Database Serviceability.
     */
    public function testPincodeLookup(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $pincode = trim($request->pincode);
        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 6-digit Indian PIN code (e.g. 560001, 500001).',
            ], 422);
        }

        // 1. Fetch from India Post Postal Pincode API
        $postalInfo = null;
        try {
            $postRes = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept'     => 'application/json'
                ])
                ->get("https://api.postalpincode.in/pincode/{$pincode}");

            if ($postRes->successful()) {
                $data = $postRes->json();
                if (is_array($data) && ($data[0]['Status'] ?? '') === 'Success' && !empty($data[0]['PostOffice'])) {
                    $firstPo = $data[0]['PostOffice'][0];
                    $postalInfo = [
                        'source'             => 'India Post National Directory (api.postalpincode.in)',
                        'district'           => $firstPo['District'] ?? '',
                        'state'              => $firstPo['State'] ?? '',
                        'post_office'        => $firstPo['Name'] ?? '',
                        'post_offices_count' => count($data[0]['PostOffice']),
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently try backup Zippopotam
        }

        // Fallback to Zippopotam if India Post was unreachable
        if (!$postalInfo) {
            try {
                $zipRes = Http::timeout(5)->get("https://api.zippopotam.us/IN/{$pincode}");
                if ($zipRes->successful()) {
                    $zipData = $zipRes->json();
                    if (!empty($zipData['places'][0])) {
                        $postalInfo = [
                            'source'             => 'Global Postal Directory (api.zippopotam.us)',
                            'district'           => $zipData['places'][0]['place name'] ?? '',
                            'state'              => $zipData['places'][0]['state'] ?? '',
                            'post_office'        => $zipData['places'][0]['place name'] ?? '',
                            'post_offices_count' => 1,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // 2. Check local store serviceability
        $deliveryCheck = app(DeliveryService::class)->checkServiceability($pincode);
        $zone = app(FulfillmentService::class)->detectZone($pincode, $deliveryCheck['city'] ?? ($postalInfo['district'] ?? ''));

        return response()->json([
            'success'        => true,
            'pincode'        => $pincode,
            'postal_info'    => $postalInfo,
            'serviceability' => [
                'is_serviceable'   => $deliveryCheck['is_serviceable'] ?? false,
                'message'          => $deliveryCheck['message'] ?? '',
                'city'             => $deliveryCheck['city'] ?? ($postalInfo['district'] ?? 'Unknown'),
                'state'            => $deliveryCheck['state'] ?? ($postalInfo['state'] ?? 'Unknown'),
                'delivery_charge'  => $deliveryCheck['delivery_charge'] ?? 0,
                'is_cod_available' => $deliveryCheck['is_cod_available'] ?? false,
                'cod_fee'          => $deliveryCheck['cod_fee'] ?? 0,
                'zone'             => $zone, // 'local_fleet' vs 'national_courier'
            ]
        ]);
    }
}
