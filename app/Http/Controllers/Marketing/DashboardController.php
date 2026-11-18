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
     * Display the Marketing & Push Notifications Workspace.
     */
    public function index(): View
    {
        $user = auth('marketing')->user() ?? auth('admin')->user() ?? auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

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

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('marketing.dashboard', compact(
            'notifications',
            'totalActiveDevices',
            'maleDevices',
            'femaleDevices',
            'totalBroadcasts',
            'totalDelivered',
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
            'target_gender'    => 'required|string|in:all,Male,Female',
            'target_age_group' => 'required|string|in:all,birthday_month,under_25,25_40,over_40',
            'target_activity'  => 'required|string|in:all,dormant',
        ]);

        $user = auth('marketing')->user() ?? auth('admin')->user() ?? auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $actorId = $user ? $user->id : null;

        if ($isSuperAdmin) {
            // 1. Build targeted audience query on registered active devices
            $devicesQuery = UserDevice::where('is_active', true)->whereNotNull('fcm_token');

            if ($request->target_gender !== 'all') {
                $devicesQuery->whereHas('user.profile', fn($q) => $q->where('gender', $request->target_gender));
            }

            if ($request->target_age_group === 'birthday_month') {
                $devicesQuery->whereHas('user.profile', fn($q) => $q->whereRaw('MONTH(date_of_birth) = ?', [now()->month]));
            } elseif ($request->target_age_group === 'under_25') {
                $devicesQuery->whereHas('user.profile', fn($q) => $q->where('date_of_birth', '>', now()->subYears(25)));
            } elseif ($request->target_age_group === '25_40') {
                $devicesQuery->whereHas('user.profile', fn($q) => $q->whereBetween('date_of_birth', [now()->subYears(40), now()->subYears(25)]));
            } elseif ($request->target_age_group === 'over_40') {
                $devicesQuery->whereHas('user.profile', fn($q) => $q->where('date_of_birth', '<', now()->subYears(40)));
            }

            if ($request->target_activity === 'dormant') {
                $devicesQuery->whereHas('user', fn($q) => $q->whereDoesntHave('orders', fn($oq) => $oq->where('created_at', '>=', now()->subDays(30))));
            }

            $tokens = $devicesQuery->pluck('fcm_token')->unique()->values();
            $sentCount = 0;

            // 2. Prepare Flutter deep link payload
            $payload = [
                'type'   => 'broadcast',
                'screen' => $request->destination_type,
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
                'target_gender'    => $request->target_gender,
                'target_age_group' => $request->target_age_group,
                'target_activity'  => $request->target_activity,
                'recipient_count'  => $sentCount,
                'status'           => 'Approved',
                'approved_by'      => $actorId,
                'approved_at'      => now(),
                'sent_by'          => $actorId,
            ]);

            return redirect()->route('marketing.dashboard')
                ->with('success', "Push alert successfully broadcast to {$sentCount} active device(s)!");
        }

        // Non-super-admin (Marketing Staff): save as draft awaiting Super Admin approval
        AppNotification::create([
            'title'            => $request->title,
            'body'             => $request->body,
            'banner_image'     => $request->banner_image,
            'destination_type' => $request->destination_type,
            'destination_id'   => $request->category_id,
            'target_gender'    => $request->target_gender,
            'target_age_group' => $request->target_age_group,
            'target_activity'  => $request->target_activity,
            'recipient_count'  => 0,
            'status'           => 'Pending_Approval',
            'sent_by'          => $actorId,
        ]);

        return redirect()->route('marketing.dashboard')
            ->with('success', 'Push notification draft submitted for Super Admin review & approval. Once approved, it will be broadcast to customer devices.');
    }
}
