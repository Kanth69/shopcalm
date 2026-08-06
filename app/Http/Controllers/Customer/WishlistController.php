<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = WishlistItem::whereHas('wishlist', function ($query) {
            $query->where('user_id', Auth::id());
        })->with('product')->latest()->get();

        return view('customer.wishlist.index', compact('wishlistItems'));
    }

    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $wishlist = Auth::user()->wishlist()->firstOrCreate();

        WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlist->id,
            'product_id' => $request->product_id,
        ]);

        return back()->with('success', 'Product added to wishlist.');
    }

    public function remove(WishlistItem $wishlistItem)
    {
        // Ensure the item belongs to the authenticated user's wishlist
        if ($wishlistItem->wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $wishlistItem->delete();

        return back()->with('success', 'Product removed from wishlist.');
    }

    public function moveToCart(WishlistItem $wishlistItem, CartService $cartService)
    {
        if ($wishlistItem->wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $cartService->addProduct($wishlistItem->product_id);

        $wishlistItem->delete();

        return redirect()->route('cart.index')->with('success', 'Product moved to cart.');
    }
}
