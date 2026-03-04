<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\CostCenter;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $areas = Area::with('costCenters')->get();
        return view('organization.index', compact('areas'));
    }

    // --- AREAS (BEREICHE) ---

    public function storeArea(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_code' => 'nullable|string|max:50',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50', // NEU
        ]);

        Area::create($validated);
        return back()->with('success', 'Bereich angelegt.');
    }

    public function updateArea(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_code' => 'nullable|string|max:50',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50', // NEU
        ]);

        $area->update($validated);
        return back()->with('success', 'Bereich aktualisiert.');
    }

    public function destroyArea(Area $area) {
        $area->delete();
        return back()->with('success', 'Bereich gelöscht.');
    }

    // --- COST CENTERS (KOSTENSTELLEN) ---

    public function storeCostCenter(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:cost_centers,code',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50', // NEU
        ]);

        CostCenter::create($validated);
        return back()->with('success', 'Kostenstelle angelegt.');
    }

    public function updateCostCenter(Request $request, CostCenter $costCenter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:cost_centers,code,' . $costCenter->id,
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50', // NEU
        ]);

        $costCenter->update($validated);
        return back()->with('success', 'Kostenstelle aktualisiert.');
    }

    public function destroyCostCenter(CostCenter $costCenter) {
        $costCenter->delete();
        return back()->with('success', 'Kostenstelle gelöscht.');
    }
}
