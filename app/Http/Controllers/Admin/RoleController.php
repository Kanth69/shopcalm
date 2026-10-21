<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('manage-admins', User::class);
        $admins = User::whereIn('role_id', [
            User::ROLE_SUPER_ADMIN, 
            User::ROLE_ADMIN, 
            User::ROLE_PRODUCT_MANAGER, 
            User::ROLE_ORDER_MANAGER, 
            User::ROLE_SUPPORT,
            User::ROLE_DELIVERY_PARTNER,
        ])->where('status', '!=', 'Deleted')->latest()->get();
        return view('admin.roles.index', compact('admins'));
    }

    public function create()
    {
        $this->authorize('manage-admins', User::class);
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-admins', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'required|string|unique:users,mobile_number',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role_id' => 'required|in:' . implode(',', [
                User::ROLE_SUPER_ADMIN, 
                User::ROLE_ADMIN, 
                User::ROLE_PRODUCT_MANAGER, 
                User::ROLE_ORDER_MANAGER, 
                User::ROLE_SUPPORT,
                User::ROLE_DELIVERY_PARTNER,
            ]),
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'role_id' => (int) $request->role_id,
            'status' => 'Active',
        ]);

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Staff account created successfully.']);
    }

    public function edit($role)
    {
        $this->authorize('manage-admins', User::class);
        $admin = $role instanceof User ? $role : User::findOrFail($role);
        return view('admin.roles.edit', compact('admin'));
    }

    public function update(Request $request, $role)
    {
        $this->authorize('manage-admins', User::class);
        $admin = $role instanceof User ? $role : User::findOrFail($role);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'mobile_number' => 'required|string|unique:users,mobile_number,' . $admin->id,
            'role_id' => 'required|in:' . implode(',', [
                User::ROLE_SUPER_ADMIN, 
                User::ROLE_ADMIN, 
                User::ROLE_PRODUCT_MANAGER, 
                User::ROLE_ORDER_MANAGER, 
                User::ROLE_SUPPORT,
                User::ROLE_DELIVERY_PARTNER,
            ]),
            'status' => 'nullable|in:Active,Blocked',
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        // If demoting from Super Admin to another role, ensure at least one active Super Admin remains
        if ($admin->isSuperAdmin() && (int) $request->role_id !== User::ROLE_SUPER_ADMIN) {
            $otherActiveSuperAdmins = User::where('role_id', User::ROLE_SUPER_ADMIN)
                ->where('status', 'Active')
                ->where('id', '!=', $admin->id)
                ->count();
            if ($otherActiveSuperAdmins < 1) {
                return back()->withInput()->with('toast', [
                    'type' => 'error', 
                    'title' => 'Action Denied', 
                    'message' => 'At least one Super Admin must remain active in the system.'
                ]);
            }
        }

        // Status change checks
        if ($request->filled('status')) {
            if ($request->status === 'Blocked') {
                if ($admin->id === auth()->id()) {
                    return back()->withInput()->with('toast', [
                        'type' => 'error',
                        'title' => 'Action Denied',
                        'message' => 'You cannot block your own account.'
                    ]);
                }
                if ($admin->isSuperAdmin()) {
                    $otherActiveSuperAdmins = User::where('role_id', User::ROLE_SUPER_ADMIN)
                        ->where('status', 'Active')
                        ->where('id', '!=', $admin->id)
                        ->count();
                    if ($otherActiveSuperAdmins < 1) {
                        return back()->withInput()->with('toast', [
                            'type' => 'error',
                            'title' => 'Action Denied',
                            'message' => 'At least one Super Admin must remain active in the system.'
                        ]);
                    }
                }
            }
            $admin->status = $request->status;
        }

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->mobile_number = $request->mobile_number;
        $admin->role_id = (int) $request->role_id;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Admin details updated successfully.']);
    }

    /**
     * Toggle the status of a staff/admin account between Active and Blocked.
     */
    public function toggleStatus($role)
    {
        $this->authorize('manage-admins', User::class);
        $user = $role instanceof User ? $role : User::findOrFail($role);

        if ($user->id === auth()->id()) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Action Denied',
                'message' => 'You cannot block your own account.'
            ]);
        }

        if ($user->isSuperAdmin() && $user->status === 'Active') {
            $otherActiveSuperAdmins = User::where('role_id', User::ROLE_SUPER_ADMIN)
                ->where('status', 'Active')
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherActiveSuperAdmins < 1) {
                return back()->with('toast', [
                    'type' => 'error',
                    'title' => 'Action Denied',
                    'message' => 'At least one Super Admin must remain active in the system.'
                ]);
            }
        }

        $newStatus = ($user->status === 'Active') ? 'Blocked' : 'Active';
        $user->status = $newStatus;
        $user->save();

        $actionWord = ($newStatus === 'Blocked') ? 'blocked' : 'unblocked';
        return redirect()->route('admin.roles.index')->with('toast', [
            'type' => 'success',
            'title' => ($newStatus === 'Blocked') ? 'Account Blocked' : 'Account Unblocked',
            'message' => "Staff member \"{$user->name}\" has been {$actionWord} successfully."
        ]);
    }

    public function destroy($role)
    {
        $this->authorize('manage-admins', User::class);
        $user = $role instanceof User ? $role : User::findOrFail($role);

        if ($user->id === auth()->id()) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Action Denied', 'message' => 'You cannot delete your own account.']);
        }

        if ($user->isSuperAdmin()) {
            $otherSuperAdmins = User::where('role_id', User::ROLE_SUPER_ADMIN)
                ->where('id', '!=', $user->id)
                ->where('status', '!=', 'Deleted')
                ->count();
            if ($otherSuperAdmins < 1) {
                return back()->with('toast', ['type' => 'error', 'title' => 'Action Denied', 'message' => 'At least one Super Admin must remain in the system.']);
            }
        }

        $userName = $user->name;

        try {
            $user->delete();
        } catch (\Throwable $e) {
            $user->status = 'Deleted';
            $user->email = 'deleted_' . $user->id . '_' . time() . '@wisekart.internal';
            $user->mobile_number = null;
            $user->password = Hash::make(\Illuminate\Support\Str::random(32));
            $user->save();
        }

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => "Admin account for \"{$userName}\" has been deleted successfully."]);
    }
}
