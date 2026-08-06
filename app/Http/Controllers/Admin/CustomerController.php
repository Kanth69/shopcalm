<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::where('role_id', User::ROLE_CUSTOMER);

        // 1. Filtering by Date Range
        $filter = $request->input('filter', 'all');
        $query = $this->applyDateFilter($query, $filter, $request);

        // 2. Searching
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        // 3. Sorting
        $sort = $request->input('sort', 'newest');
        $query = $this->applySorting($query, $sort);

        // Stats Calculation (Strictly users table)
        $stats = $this->getCustomerStats();

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function show(User $customer)
    {
        $this->authorize('view', $customer);

        if ($customer->role_id !== User::ROLE_CUSTOMER) {
            abort(404);
        }

        return view('admin.customers.show', compact('customer'));
    }

    public function toggleStatus(User $customer)
    {
        $this->authorize('update', $customer);

        if ($customer->role_id !== User::ROLE_CUSTOMER) {
            abort(404);
        }

        $customer->status = ($customer->status === 'Active') ? 'Blocked' : 'Active';
        $customer->save();

        $message = $customer->status === 'Active' ? 'Customer unblocked successfully.' : 'Customer blocked successfully.';
        return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => $message]);
    }

    public function destroy(User $customer)
    {
        $this->authorize('delete', $customer);

        if ($customer->role_id !== User::ROLE_CUSTOMER) {
            abort(404);
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Customer account deleted successfully.']);
    }

    private function applyDateFilter($query, $filter, $request)
    {
        switch ($filter) {
            case 'today':
                return $query->whereDate('created_at', Carbon::today());
            case 'yesterday':
                return $query->whereDate('created_at', Carbon::yesterday());
            case 'this_week':
                return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            case 'this_month':
                return $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            case 'last_month':
                return $query->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year);
            case 'this_year':
                return $query->whereYear('created_at', Carbon::now()->year);
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    return $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
                }
                return $query;
            default:
                return $query;
        }
    }

    private function applySorting($query, $sort)
    {
        switch ($sort) {
            case 'oldest':
                return $query->orderBy('created_at', 'asc');
            case 'az':
                return $query->orderBy('name', 'asc');
            case 'za':
                return $query->orderBy('name', 'desc');
            default:
                return $query->orderBy('created_at', 'desc');
        }
    }

    private function getCustomerStats()
    {
        $baseQuery = User::where('role_id', User::ROLE_CUSTOMER);

        return [
            'total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', Carbon::today())->count(),
            'yesterday' => (clone $baseQuery)->whereDate('created_at', Carbon::yesterday())->count(),
            'this_week' => (clone $baseQuery)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'this_month' => (clone $baseQuery)->whereMonth('created_at', Carbon::now()->month)->count(),
            'last_month' => (clone $baseQuery)->whereMonth('created_at', Carbon::now()->subMonth()->month)->count(),
        ];
    }
}
