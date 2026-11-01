<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AddressController extends Controller
{
    public function index()
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        $addresses = $user ? $user->addresses()->latest()->get() : collect();
        return view('customer.account.addresses', compact('addresses'));
    }

    public function create()
    {
        return view('customer.account.addresses.create');
    }

    public function store(StoreAddressRequest $request)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Please log in to save your address.'], 401);
            }
            return redirect()->route('login');
        }

        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'India';
        unset($data['is_default']);

        // 1. Smart Deduplication: Check if identical address already exists
        $houseNo = trim($data['house_no'] ?? '');
        $zip = trim($data['zip'] ?? '');
        $fullAddress = trim($data['address'] ?? '');

        $duplicateQuery = $user->addresses()->where('zip', $zip);
        if (!empty($houseNo)) {
            $duplicateQuery->where(function ($q) use ($houseNo, $fullAddress) {
                $q->where('house_no', $houseNo)
                  ->orWhere('address', $fullAddress);
            });
        } else {
            $duplicateQuery->where('address', $fullAddress);
        }

        $existingAddress = $duplicateQuery->first();
        if ($existingAddress) {
            $existingAddress->update($data);
            Cache::forget("user_{$user->id}_addresses");

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success'       => true,
                    'message'       => 'Address already existed and was updated with latest details.',
                    'address'       => $existingAddress->fresh(),
                    'all_addresses' => $user->addresses()->latest()->get(),
                ]);
            }
            return redirect()->route('account.addresses.index')->with('success', 'Address already existed and was updated with latest details.');
        }

        // 2. Strict Limit: Max 5 Addresses
        if ($user->addresses()->count() >= 5) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached the maximum limit of 5 saved addresses. Please edit or delete an existing address.'
                ], 422);
            }
            return redirect()->route('account.addresses.index')->with('error', 'You have reached the maximum limit of 5 saved addresses. Please edit or delete an existing address.');
        }

        $address = $user->addresses()->create($data);
        Cache::forget("user_{$user->id}_addresses");

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success'       => true,
                'message'       => 'Address saved successfully.',
                'address'       => $address,
                'all_addresses' => $user->addresses()->latest()->get(),
            ]);
        }

        return redirect()->route('account.addresses.index')->with('success', 'Address saved successfully.');
    }

    public function edit(Address $address)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user || (int) $address->user_id !== (int) $user->id) {
            abort(404);
        }
        return view('customer.account.addresses.edit', compact('address'));
    }

    public function update(StoreAddressRequest $request, Address $address)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user || (int) $address->user_id !== (int) $user->id) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(404);
        }

        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'India';
        unset($data['is_default']);
        $address->update($data);
        Cache::forget("user_{$user->id}_addresses");

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully.',
                'address' => $address->fresh(),
                'all_addresses' => $user->addresses()->latest()->get(),
            ]);
        }

        return redirect()->route('account.addresses.index')->with('success', 'Address updated successfully.');
    }

    public function destroy(Request $request, Address $address)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user || $address->user_id !== $user->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(404);
        }

        $address->delete();
        Cache::forget("user_{$user->id}_addresses");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.',
                'remaining_count' => $user->addresses()->count()
            ]);
        }

        return back()->with('success', 'Address deleted successfully.');
    }
}
