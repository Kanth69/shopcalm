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
        $admins = User::whereIn('role_id', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])->get();
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
            'role_id' => 'required|in:0,1',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'User created successfully.']);
    }

    public function destroy(User $user)
    {
        $this->authorize('manage-admins', User::class);

        if ($user->id === auth()->id()) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'You cannot delete yourself.']);
        }

        if ($user->isSuperAdmin() && User::where('role_id', User::ROLE_SUPER_ADMIN)->count() <= 1) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'At least one Super Admin must exist.']);
        }

        $user->delete();

        return redirect()->route('admin.roles.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'User deleted successfully.']);
    }
}
