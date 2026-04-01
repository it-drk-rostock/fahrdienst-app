<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\CostCenter;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
  public function index()
    {
        // Bereiche nach Buchungskreis (company_code) und dann nach Name sortieren
        $mainAreas = Area::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('company_code')->orderBy('name');
            }, 'children.costCenters' => function($q) {
                $q->orderBy('code');
            }, 'costCenters' => function($q) {
                $q->orderBy('code');
            }])
            ->orderBy('company_code')
            ->orderBy('name')
            ->get();

        $allAreas = Area::orderBy('company_code')->orderBy('name')->get();

        return view('organization.index', compact('mainAreas', 'allAreas'));
    }
    // --- AREAS (BEREICHE) ---

    public function storeArea(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:areas,id', // NEU: Für Unterbereiche
            'company_code' => 'nullable|string|max:50',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
        ]);

        Area::create($validated);
        return back()->with('success', 'Bereich angelegt.');
    }

    public function updateArea(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:areas,id', // NEU: Für Unterbereiche
            'company_code' => 'nullable|string|max:50',
            'manager_name' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
        ]);

        // NEU: Verhindern, dass ein Bereich sich selbst als Elternteil bekommt (Endlosschleife)
        if (isset($validated['parent_id']) && $validated['parent_id'] == $area->id) {
            return back()->withErrors(['parent_id' => 'Ein Bereich kann sich nicht selbst übergeordnet sein.']);
        }

        $area->update($validated);
        return back()->with('success', 'Bereich aktualisiert.');
    }

    public function destroyArea(Area $area) {
        // NEU: Absicherung! Ein Bereich mit Unterbereichen oder Kostenstellen darf nicht blind gelöscht werden.
        if ($area->children()->count() > 0 || $area->costCenters()->count() > 0) {
            return back()->with('warning', 'Bereich kann nicht gelöscht werden, da noch Unterbereiche oder Kostenstellen zugeordnet sind.');
        }

        $area->delete();
        return back()->with('success', 'Bereich gelöscht.');
    }

    // --- COST CENTERS (KOSTENSTELLEN) ---
    // (Hier bleibt alles beim Alten, da sich an der Logik nichts ändert)

    public function storeCostCenter(Request $request)
    {
      $validated = $request->validate([
          'area_id' => 'required|exists:areas,id',
          'name' => 'required|string|max:255',
          'code' => 'required|string|max:20|unique:cost_centers,code' . (isset($costCenter) ? ',' . $costCenter->id : ''),
          'short_name' => 'nullable|string|max:50', // <--- NEU
          'contact_name' => 'nullable|string|max:255',
          'contact_email' => 'nullable|email|max:255',
          'telephone' => 'nullable|string|max:50',
      ]);

        CostCenter::create($validated);
        return back()->with('success', 'Kostenstelle angelegt.');
    }

    public function updateCostCenter(Request $request, CostCenter $costCenter)
    {
      $validated = $request->validate([
          'area_id' => 'required|exists:areas,id',
          'name' => 'required|string|max:255',
          'code' => 'required|string|max:20|unique:cost_centers,code' . (isset($costCenter) ? ',' . $costCenter->id : ''),
          'short_name' => 'nullable|string|max:50', // <--- NEU
          'contact_name' => 'nullable|string|max:255',
          'contact_email' => 'nullable|email|max:255',
          'telephone' => 'nullable|string|max:50',
      ]);

        $costCenter->update($validated);
        return back()->with('success', 'Kostenstelle aktualisiert.');
    }

    public function destroyCostCenter(CostCenter $costCenter) {
        $costCenter->delete();
        return back()->with('success', 'Kostenstelle gelöscht.');
    }
}
