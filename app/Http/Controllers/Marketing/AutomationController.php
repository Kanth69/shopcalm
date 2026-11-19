<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingAutomation;
use App\Models\WalletTransaction;
use App\Services\MarketingAutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    protected MarketingAutomationService $automationService;

    public function __construct(MarketingAutomationService $automationService)
    {
        $this->automationService = $automationService;
    }

    /**
     * Display the Marketing Automation Studio & Rules list.
     */
    public function index(): View
    {
        $automations = MarketingAutomation::orderBy('id', 'asc')->get();

        $orderAutomations = $automations->where('category', 'order');
        $growthAutomations = $automations->where('category', 'growth');

        $activeCount = $automations->where('is_active', true)->count();
        $totalSent = (int) $automations->sum('total_sent');
        $orderActiveCount = $orderAutomations->where('is_active', true)->count();
        $growthActiveCount = $growthAutomations->where('is_active', true)->count();

        return view('marketing.automations.index', compact(
            'automations',
            'orderAutomations',
            'growthAutomations',
            'activeCount',
            'totalSent',
            'orderActiveCount',
            'growthActiveCount'
        ));
    }

    /**
     * 1-Click Toggle Active / Paused status.
     */
    public function toggle(MarketingAutomation $automation): JsonResponse
    {
        $automation->is_active = !$automation->is_active;
        $automation->save();

        return response()->json([
            'success'   => true,
            'is_active' => $automation->is_active,
            'message'   => "{$automation->name} is now " . ($automation->is_active ? 'Active' : 'Paused') . '.',
        ]);
    }

    /**
     * Update an automation rule's settings, rewards, and message copy.
     */
    public function update(Request $request, MarketingAutomation $automation)
    {
        $validated = $request->validate([
            'delay_minutes' => 'nullable|integer|min:0|max:10080',
            'title'         => 'required|string|max:200',
            'body'          => 'required|string|max:500',
            'is_active'     => 'nullable|boolean',
        ]);

        $title = trim($validated['title']);
        $body  = trim($validated['body']);

        $automation->delay_minutes = (int) ($validated['delay_minutes'] ?? 0);
        
        // Single unified copy
        $automation->title_template = [
            'default' => $title,
            'en'      => $title,
        ];
        $automation->body_template = [
            'default' => $body,
            'en'      => $body,
        ];

        if ($request->has('is_active')) {
            $automation->is_active = $request->boolean('is_active');
        }

        $automation->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => "Automation '{$automation->name}' updated successfully!",
                'automation' => $automation,
            ]);
        }

        return redirect()->route('marketing.automations.index')->with('success', "Playbook '{$automation->name}' updated successfully!");
    }
}
