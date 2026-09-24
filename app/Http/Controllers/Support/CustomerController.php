<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display searchable & filterable customer directory.
     */
    public function index(Request $request): View
    {
        $query = User::where('role_id', User::ROLE_CUSTOMER)->withCount('orders');

        // 1. Search by name, email, or mobile
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        // 2. Filter by status (Active / Blocked)
        if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
            $query->where('status', ucfirst($request->status));
        }

        // 3. Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'orders_desc':
                $query->orderBy('orders_count', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $customers = $query->paginate(15)->withQueryString();

        // 4. Quick Customer Statistics
        $totalCustomers = User::where('role_id', User::ROLE_CUSTOMER)->count();
        $activeCustomers = User::where('role_id', User::ROLE_CUSTOMER)->where('status', 'Active')->count();
        $blockedCustomers = User::where('role_id', User::ROLE_CUSTOMER)->where('status', 'Blocked')->count();

        return view('support.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'blockedCustomers'
        ));
    }

    /**
     * Display 360° view of customer profile, past orders, addresses, and inquiries.
     */
    public function show(User $customer): View
    {
        if ($customer->role_id !== User::ROLE_CUSTOMER) {
            abort(404);
        }

        $customer->load([
            'orders' => fn($q) => $q->latest()->with('items.product'),
            'addresses',
        ]);

        $enquiries = ContactEnquiry::where('email', $customer->email)->latest()->get();
        $lifetimeSpend = $customer->orders->where('status', '!=', 'cancelled')->sum('total_amount');

        return view('support.customers.show', compact('customer', 'enquiries', 'lifetimeSpend'));
    }

    /**
     * Toggle Customer Account Status (Block / Unblock & Reactivate) with Support Audit Note.
     */
    public function toggleStatus(Request $request, User $customer): RedirectResponse|JsonResponse
    {
        if ($customer->role_id !== User::ROLE_CUSTOMER) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Customer record not found.'], 404);
            }
            abort(404);
        }

        $previousStatus = $customer->status ?? 'Active';
        $newStatus = ($previousStatus === 'Active') ? 'Blocked' : 'Active';
        $reason = $request->input('reason', 'Customer Support manual status update.');

        $customer->update(['status' => $newStatus]);

        $msg = ($newStatus === 'Active')
            ? "Customer account #{$customer->id} ({$customer->name}) has been UNBLOCKED and reactivated."
            : "Customer account #{$customer->id} ({$customer->name}) has been BLOCKED.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status'  => $customer->status,
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => ($newStatus === 'Active') ? 'Account Unblocked' : 'Account Blocked',
            'message' => $msg,
        ]);
    }
}

