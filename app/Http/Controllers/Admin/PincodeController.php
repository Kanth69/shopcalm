<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pincode;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PincodeController extends Controller
{
    private function checkSuperAdmin(): void
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Super Admin credentials required for Pincodes & Logistics.');
        }
    }

    public function index(Request $request): View
    {
        $this->checkSuperAdmin();

        $query = Pincode::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('pincode', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'serviceable') {
                $query->where('is_serviceable', true);
            } elseif ($request->status === 'unserviceable') {
                $query->where('is_serviceable', false);
            }
        }

        if ($request->filled('cod')) {
            if ($request->cod === 'yes') {
                $query->where('is_cod_available', true);
            } elseif ($request->cod === 'no') {
                $query->where('is_cod_available', false);
            }
        }

        $pincodes = $query->orderBy('state')->orderBy('city')->paginate(20)->withQueryString();

        $stats = [
            'total' => Pincode::count(),
            'serviceable' => Pincode::where('is_serviceable', true)->count(),
            'cod_available' => Pincode::where('is_cod_available', true)->count(),
            'unserviceable' => Pincode::where('is_serviceable', false)->count(),
        ];

        return view('admin.pincodes.index', compact('pincodes', 'stats'));
    }

    public function create(): View
    {
        $this->checkSuperAdmin();
        return view('admin.pincodes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();

        $request->validate([
            'pincode' => 'required|digits:6|unique:pincodes,pincode',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'delivery_days' => 'required|integer|min:1|max:14',
            'delivery_charge' => 'required|numeric|min:0',
            'is_cod_available' => 'boolean',
            'is_serviceable' => 'boolean',
        ]);

        Pincode::create([
            'pincode' => $request->pincode,
            'city' => $request->city,
            'state' => $request->state,
            'delivery_days' => (int)$request->delivery_days,
            'delivery_charge' => (float)$request->delivery_charge,
            'is_cod_available' => $request->boolean('is_cod_available', true),
            'is_serviceable' => $request->boolean('is_serviceable', true),
        ]);

        return redirect()->route('admin.pincodes.index')
            ->with('toast', ['type' => 'success', 'title' => 'Pincode Added', 'message' => "Pincode {$request->pincode} successfully added to database."]);
    }

    public function edit(Pincode $pincode): View
    {
        $this->checkSuperAdmin();
        return view('admin.pincodes.edit', compact('pincode'));
    }

    public function update(Request $request, Pincode $pincode): RedirectResponse
    {
        $this->checkSuperAdmin();

        $request->validate([
            'pincode' => 'required|digits:6|unique:pincodes,pincode,' . $pincode->id,
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'delivery_days' => 'required|integer|min:1|max:14',
            'delivery_charge' => 'required|numeric|min:0',
            'is_cod_available' => 'boolean',
            'is_serviceable' => 'boolean',
        ]);

        $pincode->update([
            'pincode' => $request->pincode,
            'city' => $request->city,
            'state' => $request->state,
            'delivery_days' => (int)$request->delivery_days,
            'delivery_charge' => (float)$request->delivery_charge,
            'is_cod_available' => $request->boolean('is_cod_available', true),
            'is_serviceable' => $request->boolean('is_serviceable', true),
        ]);

        return redirect()->route('admin.pincodes.index')
            ->with('toast', ['type' => 'success', 'title' => 'Pincode Updated', 'message' => "Pincode {$pincode->pincode} has been updated."]);
    }

    public function destroy(Pincode $pincode): RedirectResponse
    {
        $this->checkSuperAdmin();

        $code = $pincode->pincode;
        $pincode->delete();

        return redirect()->route('admin.pincodes.index')
            ->with('toast', ['type' => 'success', 'title' => 'Pincode Removed', 'message' => "Pincode {$code} has been deleted."]);
    }

    public function toggleServiceable(Pincode $pincode): RedirectResponse
    {
        $this->checkSuperAdmin();

        $pincode->update(['is_serviceable' => !$pincode->is_serviceable]);
        $status = $pincode->is_serviceable ? 'Serviceable' : 'Unserviceable';

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'title' => 'Status Updated', 'message' => "Pincode {$pincode->pincode} is now {$status}."]);
    }

    public function toggleCod(Pincode $pincode): RedirectResponse
    {
        $this->checkSuperAdmin();

        $pincode->update(['is_cod_available' => !$pincode->is_cod_available]);
        $status = $pincode->is_cod_available ? 'Enabled' : 'Disabled';

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'title' => 'COD Updated', 'message' => "COD for {$pincode->pincode} is now {$status}."]);
    }

    public function bulkImport(Request $request): RedirectResponse
    {
        $this->checkSuperAdmin();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Read header row

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;

            $pincode = trim($row[0]);
            if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) continue;

            $city = trim($row[1] ?? 'City');
            $state = trim($row[2] ?? 'State');
            $days = isset($row[3]) ? (int)$row[3] : 3;
            $cod = isset($row[4]) ? (bool)(int)$row[4] : true;
            $charge = isset($row[5]) ? (float)$row[5] : 0.00;

            Pincode::updateOrCreate(
                ['pincode' => $pincode],
                [
                    'city' => $city,
                    'state' => $state,
                    'delivery_days' => max(1, $days),
                    'is_cod_available' => $cod,
                    'delivery_charge' => $charge,
                    'is_serviceable' => true,
                ]
            );
            $count++;
        }
        fclose($handle);

        return redirect()->route('admin.pincodes.index')
            ->with('toast', ['type' => 'success', 'title' => 'Bulk Import Completed', 'message' => "Successfully imported/updated {$count} pincodes."]);
    }
}
