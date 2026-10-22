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

    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'authenticated' => false,
                    'redirect' => route('login'),
                    'message' => 'Please sign in to save items to your wishlist.',
                ], 401);
            }
            return redirect()->route('login')->with('info', 'Please sign in to save items to your wishlist.');
        }

        $request->validate(['product_id' => 'required|exists:products,id']);

        $user = Auth::user();
        $wishlist = $user->wishlist()->firstOrCreate(['user_id' => $user->id]);

        $item = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($item) {
            $item->delete();
            $isWishlisted = false;
            $message = 'Removed from wishlist.';
        } else {
            WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_id' => $request->product_id,
            ]);
            $isWishlisted = true;
            $message = 'Added to wishlist!';
        }

        $count = WishlistItem::where('wishlist_id', $wishlist->id)->count();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'action' => $isWishlisted ? 'added' : 'removed',
                'is_in_wishlist' => $isWishlisted,
                'wishlist_count' => $count,
                'product_id' => (int) $request->product_id,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function add(Request $request)
    {
        return $this->toggle($request);
    }

    public function remove(WishlistItem $wishlistItem, Request $request)
    {
        // Ensure the item belongs to the authenticated user's wishlist
        if ($wishlistItem->wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $wishlistId = $wishlistItem->wishlist_id;
        $productId = $wishlistItem->product_id;
        $wishlistItem->delete();

        $count = WishlistItem::where('wishlist_id', $wishlistId)->count();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'action' => 'removed',
                'is_in_wishlist' => false,
                'product_id' => $productId,
                'wishlist_count' => $count,
                'message' => 'Product removed from wishlist.',
            ]);
        }

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
