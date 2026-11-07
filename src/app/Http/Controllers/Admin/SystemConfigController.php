<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemConfigController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $configs = SystemConfig::where(function ($q) use ($companyId) {
            $q->whereNull('company_id')->orWhere('company_id', $companyId);
        })->get()->groupBy('config_group');

        return Inertia::render('Admin/Config/Index', [
            'configGroups' => $configs,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.group' => 'required|string',
            'configs.*.key' => 'required|string',
            'configs.*.value' => 'nullable',
        ]);

        $companyId = auth()->user()->company_id;

        foreach ($validated['configs'] as $config) {
            SystemConfig::setValue(
                $config['group'],
                $config['key'],
                $config['value'],
                $companyId,
            );
        }

        return back()->with('success', 'Configuration updated successfully.');
    }
}
