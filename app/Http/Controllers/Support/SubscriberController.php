<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    /**
     * Display newsletter subscribers list and lead stats.
     */
    public function index(Request $request): View
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

        return view('support.subscribers.index', compact('subscribers', 'stats'));
    }

    /**
     * Toggle subscription status.
     */
    public function toggleStatus(Subscriber $subscriber): RedirectResponse
    {
        $newStatus = $subscriber->status === 'Subscribed' ? 'Unsubscribed' : 'Subscribed';
        $subscriber->update(['status' => $newStatus]);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Status Updated',
            'message' => "Subscriber marked as {$newStatus}.",
        ]);
    }

    /**
     * Delete a subscriber.
     */
    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Subscriber Removed',
            'message' => 'Subscriber record deleted successfully.',
        ]);
    }

    /**
     * Bulk actions (unsubscribe / delete).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $ids = $request->input('selected_subscribers', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'No Selection',
                'message' => 'Please select at least one subscriber.',
            ]);
        }

        if ($action === 'unsubscribe') {
            Subscriber::whereIn('id', $ids)->update(['status' => 'Unsubscribed']);
            $msg = 'Selected subscribers marked as unsubscribed.';
        } elseif ($action === 'subscribe') {
            Subscriber::whereIn('id', $ids)->update(['status' => 'Subscribed']);
            $msg = 'Selected subscribers marked as subscribed.';
        } elseif ($action === 'delete') {
            Subscriber::whereIn('id', $ids)->delete();
            $msg = 'Selected subscribers deleted successfully.';
        } else {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'Invalid Action',
                'message' => 'Invalid bulk action requested.',
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Bulk Action Completed',
            'message' => $msg,
        ]);
    }

    /**
     * Export subscribers list to CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'newsletter_subscribers_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Email Address', 'Status', 'IP Address', 'Subscribed Date']);

            Subscriber::latest()->chunk(500, function ($subscribers) use ($handle) {
                foreach ($subscribers as $sub) {
                    fputcsv($handle, [
                        $sub->id,
                        $sub->email,
                        $sub->status,
                        $sub->ip_address ?? 'N/A',
                        $sub->created_at ? $sub->created_at->format('Y-m-d H:i:s') : 'N/A',
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
