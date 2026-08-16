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
        $admins = User::whereIn('role_id', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])->latest()->get();
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
            'role_id' => 'required|in:' . User::ROLE_SUPER_ADMIN . ',' . User::ROLE_ADMIN,
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'role_id' => (int) $request->role_id,
            'status' => 'Active',
        ]);

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Admin account created successfully.']);
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
            'role_id' => 'required|in:' . User::ROLE_SUPER_ADMIN . ',' . User::ROLE_ADMIN,
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        // If demoting from Super Admin to Admin, ensure at least one Super Admin remains
        if ($admin->isSuperAdmin() && (int) $request->role_id === User::ROLE_ADMIN) {
            $otherSuperAdmins = User::where('role_id', User::ROLE_SUPER_ADMIN)->where('id', '!=', $admin->id)->count();
            if ($otherSuperAdmins < 1) {
                return back()->withInput()->with('toast', ['type' => 'error', 'title' => 'Action Denied', 'message' => 'At least one Super Admin must remain active in the system.']);
            }
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

    public function destroy($role)
    {
        $this->authorize('manage-admins', User::class);
        $user = $role instanceof User ? $role : User::findOrFail($role);

        if ($user->id === auth()->id()) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Action Denied', 'message' => 'You cannot delete your own account.']);
        }

        if ($user->isSuperAdmin() && User::where('role_id', User::ROLE_SUPER_ADMIN)->count() <= 1) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Action Denied', 'message' => 'At least one Super Admin must remain in the system.']);
        }

        $user->delete();

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Admin access revoked successfully.']);
    }
}
