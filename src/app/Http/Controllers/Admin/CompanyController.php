<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request)
    {
        $query = Company::query()
            ->withCount('users')
            ->when($request->search, function ($q, $s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            })
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Companies/Index', [
            'companies' => $query,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return Inertia::render('Admin/Companies/Create');
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:20|unique:companies,code',
            'email' => 'nullable|email|unique:companies,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
            'locale' => 'required|in:ar,en',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $company = Company::create($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company)
    {
        return Inertia::render('Admin/Companies/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('companies')->ignore($company->id)],
            'email' => ['nullable', 'email', Rule::unique('companies')->ignore($company->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
            'locale' => 'required|in:ar,en',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Toggle company active status.
     */
    public function toggle(Request $request, Company $company)
    {
        $company->update(['is_active' => !$company->is_active]);

        return back()->with('success', 'Company status updated.');
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        // Prevent deleting company if it has users
        if ($company->users()->count() > 0) {
            return back()->with('error', 'Cannot delete company with existing users.');
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
