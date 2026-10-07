<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\EmailService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('admin.integrations.index', compact(
            'settings',
            'brevoQuota',
            'whatsAppStatus',
            'hasWhatsAppToken',
            'hasWhatsAppPhoneId',
            'isRazorpayConfigured',
            'isRazorpayLive',
            'todayOnlinePayments'
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

        Setting::set('brevo_api_key', $request->brevo_api_key);
        Setting::set('brevo_sender_email', $request->brevo_sender_email);
        Setting::set('brevo_sender_name', $request->brevo_sender_name);

        return back()->with('toast', ['type' => 'success', 'title' => 'Brevo Settings Updated', 'message' => 'Brevo REST API credentials saved successfully.'])->with('success', 'Brevo REST API credentials saved successfully.');
    }

    /**
     * Dedicated WhatsApp Cloud API Hub.
     */
    public function whatsapp()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $settings = Setting::getAll();

        // Recent WhatsApp Notification Logs from Order Status History
        $recentWhatsAppLogs = Order::with(['user', 'fulfillment'])
            ->whereHas('statusHistories', function ($q) {
                $q->where('notes', 'like', '%assigned%')
                  ->orWhere('notes', 'like', '%dispatched%')
                  ->orWhere('notes', 'like', '%delivered%')
                  ->orWhere('notes', 'like', '%cancelled%');
            })
            ->latest()
            ->take(15)
            ->get();

        return view('admin.integrations.whatsapp', compact('settings', 'recentWhatsAppLogs'));
    }

    /**
     * Update WhatsApp Cloud API Credentials & Templates.
     */
    public function updateWhatsApp(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }

        $validated = $request->validate([
            'whatsapp_phone_number_id'           => 'nullable|string|max:255',
            'whatsapp_api_token'                 => 'nullable|string|max:500',
            'whatsapp_template_name'             => 'nullable|string|max:255',
            'whatsapp_template_order_confirmed'  => 'nullable|string|max:255',
            'whatsapp_template_out_for_delivery_local' => 'nullable|string|max:255',
            'whatsapp_template_courier_dispatched'     => 'nullable|string|max:255',
            'whatsapp_template_order_delivered'  => 'nullable|string|max:255',
            'whatsapp_template_order_cancelled'  => 'nullable|string|max:255',
            'whatsapp_template_refund_processed' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'WhatsApp Config Saved', 'message' => 'Meta WhatsApp Business API credentials & template registry updated.'])->with('success', 'Meta WhatsApp Business API credentials & template registry updated.');
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

        Setting::set('razorpay_key_id', $request->razorpay_key_id);
        Setting::set('razorpay_key_secret', $request->razorpay_key_secret);

        return back()->with('toast', ['type' => 'success', 'title' => 'Razorpay Keys Saved', 'message' => 'Razorpay API credentials updated successfully.'])->with('success', 'Razorpay API credentials updated successfully.');
    }
}
