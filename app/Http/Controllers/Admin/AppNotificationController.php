<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Category;
use App\Models\UserDevice;
use App\Services\FcmNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppNotificationController extends Controller
{
    /**
     * Display push notification history and device demographics.
     */
    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $pendingNotifications = AppNotification::with(['sender', 'category'])
            ->where('status', 'Pending_Approval')
            ->latest()
            ->get();

        $notifications = AppNotification::with(['sender', 'category', 'approver'])
            ->latest()
            ->paginate(15);

        $totalActiveDevices = UserDevice::where('is_active', true)->whereNotNull('fcm_token')->count();
        $maleDevices = UserDevice::where('is_active', true)
            ->whereHas('user.profile', fn($q) => $q->where('gender', 'Male'))
            ->count();
        $femaleDevices = UserDevice::where('is_active', true)
            ->whereHas('user.profile', fn($q) => $q->where('gender', 'Female'))
            ->count();
        $totalBroadcasts = AppNotification::where('status', 'Approved')->count();
        $totalDelivered = (int) AppNotification::where('status', 'Approved')->sum('recipient_count');

        return view('admin.notifications.index', compact(
            'pendingNotifications',
            'notifications',
            'totalActiveDevices',
            'maleDevices',
            'femaleDevices',
            'totalBroadcasts',
            'totalDelivered'
        ));
    }

    /**
     * Show form to compose and target a new app push notification.
     */
    public function create(): View
    {
        $this->authorizeSuperAdmin();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $totalActiveDevices = UserDevice::where('is_active', true)->whereNotNull('fcm_token')->count();

        return view('admin.notifications.create', compact('categories', 'totalActiveDevices'));
    }

    /**
     * Filter audience and broadcast the push notification to active devices.
     */
    public function send(Request $request, FcmNotificationService $fcmService): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'title'            => 'required|string|max:255',
            'body'             => 'required|string|max:1000',
            'banner_image'     => 'nullable|url|max:500',
            'destination_type' => 'required|string|in:home,offers,category,cart',
            'category_id'      => 'nullable|required_if:destination_type,category|exists:categories,id',
            'campaign_type'    => 'nullable|string|in:custom,birthday,seasonal,flash_sale,dormant,wallet_expiry',
            'target_gender'    => 'required|string|in:all,Male,Female',
            'target_age_group' => 'required|string|in:all,birthday_month,under_25,25_40,over_40',
            'target_activity'  => 'required|string|in:all,dormant',
            'target_language'  => 'nullable|string|in:all,en,te,hi,ta,kn',
        ]);

        $adminId = auth('admin')->id() ?? auth()->id();

        $notification = AppNotification::create([
            'title'            => $request->title,
            'body'             => $request->body,
            'banner_image'     => $request->banner_image,
            'destination_type' => $request->destination_type,
            'destination_id'   => $request->category_id,
            'campaign_type'    => $request->campaign_type ?? 'custom',
            'target_gender'    => $request->target_gender,
            'target_age_group' => $request->target_age_group,
            'target_activity'  => $request->target_activity,
            'target_language'  => $request->target_language ?? 'all',
            'recipient_count'  => 0,
            'status'           => 'Approved',
            'approved_by'      => $adminId,
            'approved_at'      => now(),
            'sent_by'          => $adminId,
        ]);

        $sentCount = $this->broadcastNotification($notification, $fcmService);
        $notification->update(['recipient_count' => $sentCount]);

        return redirect()->route('admin.notifications.index')
            ->with('success', "Push broadcast sent successfully to {$sentCount} active device(s)!");
    }

    /**
     * Approve a pending notification and broadcast it to targeted customer devices.
     */
    public function approve(AppNotification $notification, FcmNotificationService $fcmService): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $sentCount = $this->broadcastNotification($notification, $fcmService);

        $notification->update([
            'status'           => 'Approved',
            'recipient_count'  => $sentCount,
            'approved_by'      => auth('admin')->id() ?? auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', "Notification '{$notification->title}' approved and broadcast to {$sentCount} active device(s)!");
    }

    /**
     * Reject a pending notification draft with mandatory feedback reason.
     */
    public function reject(Request $request, AppNotification $notification): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ], [
            'rejection_reason.required' => 'Please provide feedback explaining why this notification was rejected.',
            'rejection_reason.min'      => 'Rejection reason must be at least 5 characters.',
        ]);

        $notification->update([
            'status'           => 'Rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => null,
            'approved_at'      => null,
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', "Notification draft '{$notification->title}' was rejected with feedback.");
    }

    /**
     * Helper: Filter audience and broadcast FCM push to matching devices.
     */
    protected function broadcastNotification(AppNotification $notification, FcmNotificationService $fcmService): int
    {
        $tokens = UserDevice::targetAudience(
            $notification->target_gender,
            $notification->target_age_group,
            $notification->target_activity,
            $notification->target_language ?? 'all'
        )->pluck('fcm_token')->unique()->values();

        $sentCount = 0;

        $payload = [
            'type'          => 'broadcast',
            'screen'        => $notification->destination_type,
            'campaign_type' => $notification->campaign_type ?? 'custom',
        ];

        if ($notification->destination_type === 'category' && $notification->destination_id) {
            $payload['category_id'] = (string) $notification->destination_id;
        }

        foreach ($tokens as $token) {
            if ($fcmService->sendToToken($token, $notification->title, $notification->body, $payload, $notification->banner_image)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Strict Super Admin Access Authorization.
     */
    protected function authorizeSuperAdmin(): void
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin clearance required for App Push Notifications.');
        }
    }
}
