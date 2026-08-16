<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses;
        return view('customer.account.addresses', compact('addresses'));
    }

    public function create()
    {
        return view('customer.account.addresses.create');
    }

    public function store(StoreAddressRequest $request)
    {
        Auth::user()->addresses()->create($request->validated());
        return redirect()->route('account.addresses.index')->with('success', 'Address saved successfully.');
    }

    public function edit(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(404);
        }
        return view('customer.account.addresses.edit', compact('address'));
    }

    public function update(StoreAddressRequest $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(404);
        }
        $address->update($request->validated());
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
