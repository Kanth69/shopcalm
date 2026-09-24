<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Customer Support & Helpdesk Dashboard.
     */
    public function index(): View
    {
        $stats = [
            'open_enquiries'   => ContactEnquiry::where('status', 'unread')->count(),
            'in_progress'      => ContactEnquiry::where('status', 'in_progress')->count(),
            'resolved_today'   => ContactEnquiry::whereDate('resolved_at', today())->count(),
            'total_customers'  => User::where('role_id', User::ROLE_CUSTOMER)->count(),
            'total_enquiries'  => ContactEnquiry::count(),
        ];

        // Priority unresolved inquiries
        $urgentEnquiries = ContactEnquiry::whereIn('status', ['unread', 'in_progress'])
            ->latest()
            ->take(8)
            ->get();

        // Recently resolved inquiries
        $recentlyResolved = ContactEnquiry::with('resolver')
            ->where('status', 'resolved')
            ->latest('resolved_at')
            ->take(5)
            ->get();

        // Recent Customers
        $recentCustomers = User::where('role_id', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->latest()
            ->take(5)
            ->get();

        return view('support.dashboard', compact('stats', 'urgentEnquiries', 'recentlyResolved', 'recentCustomers'));
    }
}
