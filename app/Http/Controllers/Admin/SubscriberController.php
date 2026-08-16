<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscriber::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stats = [
            'all'          => Subscriber::count(),
            'subscribed'   => Subscriber::where('status', 'Subscribed')->count(),
            'unsubscribed' => Subscriber::where('status', 'Unsubscribed')->count(),
        ];

        $subscribers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.subscribers.index', compact('subscribers', 'stats'));
    }

    public function destroy(Request $request, Subscriber $subscriber)
    {
        $subscriber->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subscriber deleted successfully.'
            ]);
        }

        return back()->with('success', 'Subscriber deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No subscribers selected.'], 400);
        }

        if ($action === 'unsubscribe') {
            Subscriber::whereIn('id', $ids)->update(['status' => 'Unsubscribed']);
            $msg = 'Selected subscribers marked as unsubscribed.';
        } elseif ($action === 'delete') {
            Subscriber::whereIn('id', $ids)->delete();
            $msg = 'Selected subscribers deleted successfully.';
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }
}
