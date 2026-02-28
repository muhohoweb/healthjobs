<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Packages/Index', [
            'packages'      => Package::orderBy('sort_order')->get(),
            'billingCycles' => $this->billingCycles(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Packages/Create', [
            'billingCycles' => $this->billingCycles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:weekly,bi-weekly,monthly,quarterly,annually',
            'is_active'     => 'boolean',
            'features'      => 'nullable|array',
            'features.*'    => 'string',
            'sort_order'    => 'integer|min:0',
        ]);

        Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    public function edit(Package $package)
    {
        return Inertia::render('Admin/Packages/Edit', [
            'package'       => $package,
            'billingCycles' => $this->billingCycles(),
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:weekly,bi-weekly,monthly,quarterly,annually',
            'is_active'     => 'boolean',
            'features'      => 'nullable|array',
            'features.*'    => 'string',
            'sort_order'    => 'integer|min:0',
        ]);

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted.');
    }

    private function billingCycles(): array
    {
        return [
            ['value' => 'weekly',    'label' => 'Weekly'],
            ['value' => 'bi-weekly', 'label' => 'Bi-Weekly'],
            ['value' => 'monthly',   'label' => 'Monthly'],
            ['value' => 'quarterly', 'label' => 'Quarterly'],
            ['value' => 'annually',  'label' => 'Annually'],
        ];
    }
}