<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $query = User::query()
            ->with(['roles', 'company', 'branch']);

        // Company scoping: non-super admins can only see their company's users
        if (!$isSuperAdmin) {
            $query->where('company_id', $user->company_id);
        }

        $query->when($request->search, function ($q, $s) {
            $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%");
        })
            ->when($request->role, fn ($q, $r) => $q->whereHas('roles', fn ($q) => $q->where('name', $r)))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->company_id && $isSuperAdmin, fn ($q, $c) => $q->where('company_id', $c))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $companies = $isSuperAdmin ? Company::where('is_active', true)->orderBy('name')->get() : collect();

        return Inertia::render('Admin/Users/Index', [
            'users' => $query,
            'roles' => Role::pluck('name'),
            'companies' => $companies,
            'filters' => $request->only(['search', 'role', 'is_active', 'company_id']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $companies = $isSuperAdmin
            ? Company::where('is_active', true)->orderBy('name')->get()
            : collect([$user->company]);

        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::pluck('name'),
            'branches' => \App\Models\Branch::select('id', 'name', 'name_ar')->get(),
            'companies' => $companies,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'sometimes|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'locale' => 'required|in:ar,en',
            'is_active' => 'boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        // Company scoping: non-super admins can only create users in their own company
        $companyId = $isSuperAdmin ? $validated['company_id'] : $user->company_id;

        $user = User::create([
            'name' => $validated['name'],
            'name_ar' => $validated['name_ar'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'company_id' => $companyId,
            'branch_id' => $validated['branch_id'] ?? null,
            'locale' => $validated['locale'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $targetUser = User::with('roles')->findOrFail(request('user'));

        // Company scoping: non-super admins can only edit their company's users
        if (!$isSuperAdmin && $targetUser->company_id !== $user->company_id) {
            abort(403, 'Unauthorized to edit this user.');
        }

        $companies = $isSuperAdmin
            ? Company::where('is_active', true)->orderBy('name')->get()
            : collect([$user->company]);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $targetUser,
            'roles' => Role::pluck('name'),
            'branches' => \App\Models\Branch::select('id', 'name', 'name_ar')->get(),
            'companies' => $companies,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        // Company scoping: non-super admins can only edit their company's users
        if (!$isSuperAdmin && $user->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized to edit this user.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'company_id' => $isSuperAdmin ? 'sometimes|exists:companies,id' : 'sometimes|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'locale' => 'required|in:ar,en',
            'is_active' => 'boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Company scoping: non-super admins cannot change company_id
        if (!$isSuperAdmin) {
            unset($validated['company_id']);
        }

        $user->update($validated);
        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user = auth()->user();

        // Company scoping: non-super admins can only delete their company's users
        if (!$user->hasRole('super-admin') && $user->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized to delete this user.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status.
     */
    public function toggle(Request $request, User $user)
    {
        $user = auth()->user();

        // Company scoping: non-super admins can only toggle their company's users
        if (!$user->hasRole('super-admin') && $user->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized to toggle this user.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    /**
     * Reset user password (admin-initiated).
     */
    public function resetPassword(Request $request, User $user)
    {
        $user = auth()->user();

        // Company scoping: non-super admins can only reset their company's users
        if (!$user->hasRole('super-admin') && $user->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized to reset this user\'s password.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password reset successfully.');
    }
}
