<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Category;
use App\Models\UserDevice;
use App\Services\FcmNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Marketing Analytics Overview Dashboard.
     */
    public function index(): View
    {
        $user = auth('marketing')->user() ?? auth('admin')->user() ?? auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        $totalActiveDevices = UserDevice::where('is_active', true)->whereNotNull('fcm_token')->count();
        $maleDevices = UserDevice::where('is_active', true)
            ->whereHas('user.profile', fn($q) => $q->where('gender', 'Male'))
            ->count();
        $femaleDevices = UserDevice::where('is_active', true)
            ->whereHas('user.profile', fn($q) => $q->where('gender', 'Female'))
            ->count();
        $totalBroadcasts = AppNotification::where('status', 'Approved')->count();
        $totalDelivered = (int) AppNotification::where('status', 'Approved')->sum('recipient_count');
        $pendingApprovals = AppNotification::where('status', 'Pending_Approval')->count();

        // Recent campaigns preview
        $recentNotifications = AppNotification::with(['sender', 'category', 'approver'])
            ->latest()
            ->take(6)
            ->get();

        // Breakdown stats
        $typeStats = AppNotification::selectRaw('campaign_type, count(*) as count, sum(recipient_count) as total_delivered')
            ->groupBy('campaign_type')
            ->get();

        $languageStats = AppNotification::selectRaw('target_language, count(*) as count')
            ->groupBy('target_language')
            ->get();

        return view('marketing.dashboard', compact(
            'totalActiveDevices',
            'maleDevices',
            'femaleDevices',
            'totalBroadcasts',
            'totalDelivered',
            'pendingApprovals',
            'recentNotifications',
            'typeStats',
            'languageStats',
            'isSuperAdmin'
        ));
    }

    /**
     * Display the Push Notifications Workspace (Composer & Sent Logs).
     */
    public function notifications(): View
    {
        $user = auth('marketing')->user() ?? auth('admin')->user() ?? auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        $notifications = AppNotification::with(['sender', 'category', 'approver'])
            ->latest()
            ->paginate(15);

        $totalActiveDevices = UserDevice::where('is_active', true)->whereNotNull('fcm_token')->count();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('marketing.notifications', compact(
            'notifications',
            'totalActiveDevices',
            'categories',
            'isSuperAdmin'
        ));
    }

    /**
     * Broadcast an app push notification with audience filtration,
     * or submit it for Super Admin review if logged in as Marketing Staff.
     */
    public function send(Request $request, FcmNotificationService $fcmService): RedirectResponse
    {
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

        $user = auth('marketing')->user() ?? auth('admin')->user() ?? auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $actorId = $user ? $user->id : null;

        if ($isSuperAdmin) {
            $tokens = UserDevice::targetAudience(
                $request->target_gender,
                $request->target_age_group,
                $request->target_activity,
                $request->target_language ?? 'all'
            )->pluck('fcm_token')->unique()->values();

            $sentCount = 0;

            // 2. Prepare Flutter deep link payload
            $payload = [
                'type'          => 'broadcast',
                'screen'        => $request->destination_type,
                'campaign_type' => $request->campaign_type ?? 'custom',
            ];

            if ($request->destination_type === 'category' && $request->filled('category_id')) {
                $payload['category_id'] = (string) $request->category_id;
            }

            // 3. Dispatch FCM Push
            foreach ($tokens as $token) {
                if ($fcmService->sendToToken($token, $request->title, $request->body, $payload, $request->banner_image)) {
                    $sentCount++;
                }
            }

            // 4. Save approved broadcast log
            AppNotification::create([
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
                'recipient_count'  => $sentCount,
                'status'           => 'Approved',
                'approved_by'      => $actorId,
                'approved_at'      => now(),
                'sent_by'          => $actorId,
            ]);

            return redirect()->route('marketing.notifications.index')
                ->with('success', "Push alert successfully broadcast to {$sentCount} active device(s)!");
        }

        // Non-super-admin (Marketing Staff): save as draft awaiting Super Admin approval
        AppNotification::create([
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
            'status'           => 'Pending_Approval',
            'sent_by'          => $actorId,
        ]);

        return redirect()->route('marketing.notifications.index')
            ->with('success', 'Push notification draft submitted for Super Admin review & approval. Once approved, it will be broadcast to customer devices.');
    }
}
