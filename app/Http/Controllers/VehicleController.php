<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Area;
use App\Models\CostCenter;
use App\Models\HuReport;
use App\Models\ManagerAudit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['costCenter.area', 'damages', 'maintenanceTemplate']);

        if ($term = trim((string) $request->input('search'))) {
            $query->where(function($q) use ($term) {
                if (is_numeric($term)) {
                    $q->where('license_plate', 'LIKE', "%{$term}%");
                } else {
                    $q->where('license_plate', 'LIKE', "%{$term}%")
                      ->orWhere('manufacturer', 'LIKE', "%{$term}%")
                      ->orWhere('model', 'LIKE', "%{$term}%")
                      ->orWhere('vin', 'LIKE', "%{$term}%");
                }
            });
        }

        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', $request->cost_center_id);
        }

        if ($request->filled('show_only')) {
            if ($request->show_only == 'damages') {
                $query->whereHas('damages', function($q) {
                    $q->where('status', '!=', 'resolved');
                });
            } elseif ($request->show_only == 'inspections') {
                 $limit = Carbon::now()->addDays(60);
                 $query->where(function($q) use ($limit) {
                    $q->where('next_hu_date', '<=', $limit)
                      ->orWhere('next_uvv_date', '<=', $limit)
                      ->orWhere('next_lift_uvv_date', '<=', $limit);
                 });
            }
        }

        $query->orderBy('license_plate', 'asc');

        $perPage = $request->input('per_page', 'all');
        $limit = ($perPage === 'all') ? 1000 : (int)$perPage;

        $vehicles = $query->paginate($limit)->appends($request->query());

        return view('dashboard', [
            'vehicles' => $vehicles,
            'costCenters' => CostCenter::orderBy('code')->get(),
        ]);
    }

    public function create() {
        $areas = Area::with('costCenters')->get();
        return view('vehicles.create', compact('areas'));
    }

    public function store(Request $request) {
        $validated = $this->validateVehicleData($request);

        $checkboxes = ['has_lift', 'has_chair', 'has_smartfloor', 'is_electric', 'has_home_cable', 'is_bokraft'];
        foreach ($checkboxes as $box) $validated[$box] = $request->has($box);

        if ($validated['is_bokraft']) $validated['private_use_scope'] = null;

        $vehicle = Vehicle::create($validated);

        if ($vehicle->cost_center_id) {
            $vehicle->history()->create([
                'cost_center_id' => $vehicle->cost_center_id,
                'assigned_from' => now(),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Fahrzeug erfolgreich angelegt.');
    }

    public function show(Vehicle $vehicle) {
        $vehicle->load(['costCenter.area', 'maintenanceTemplate.items', 'huReports', 'damages', 'managerAudits', 'history.costCenter']);
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle) {
        $areas = Area::with('costCenters')->get();
        return view('vehicles.edit', compact('vehicle', 'areas'));
    }

    public function update(Request $request, Vehicle $vehicle) {
        $validated = $this->validateVehicleData($request, $vehicle->id);

        $checkboxes = ['has_lift', 'has_chair', 'has_smartfloor', 'is_electric', 'has_home_cable', 'is_bokraft'];
        foreach ($checkboxes as $box) $validated[$box] = $request->has($box);

        if ($validated['is_bokraft']) $validated['private_use_scope'] = null;

        $newCostCenterId = $validated['cost_center_id'] ?? null;
        unset($validated['cost_center_id']);

        $vehicle->fill($validated);

        if ($vehicle->cost_center_id != $newCostCenterId) {
            $vehicle->assignCostCenter($newCostCenterId);
        } else {
            $vehicle->save();
        }

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Fahrzeugdaten gespeichert.');
    }

    public function storeAudit(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'checked_at' => 'required|date',
            'mileage' => 'required|integer',
            'notes' => 'nullable|string',
            'tire_pressure_front_left' => 'nullable|numeric',
            'tire_pressure_front_right' => 'nullable|numeric',
            'tire_pressure_rear_left' => 'nullable|numeric',
            'tire_pressure_rear_right' => 'nullable|numeric',
            'tire_tread_front_left' => 'nullable|numeric',
            'tire_tread_front_right' => 'nullable|numeric',
            'tire_tread_rear_left' => 'nullable|numeric',
            'tire_tread_rear_right' => 'nullable|numeric',
        ]);

        $minTread = 2.0;
        $warnings = [];

        foreach(['front_left', 'front_right', 'rear_left', 'rear_right'] as $pos) {
            $val = $validated['tire_tread_'.$pos] ?? null;
            if ($val && $val < $minTread) {
                $warnings[] = "Profil $pos ($val mm) kritisch!";
            }
        }

        $vehicle->managerAudits()->create($validated);

        if (count($warnings) > 0) {
            return redirect()->back()->with('warning', 'ACHTUNG: ' . implode(' ', $warnings));
        }

        return redirect()->back()->with('success', 'Prüfung/Reifen gespeichert.');
    }

    public function storeDamage(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'positions' => 'required|array|min:1',
            'positions.*.title' => 'required|string|max:100',
            'positions.*.description' => 'nullable|string',
            'positions.*.severity' => 'required|in:low,medium,high,critical',
            'positions.*.damage_type' => 'required|in:wear,found,accident_own,accident_other',
        ]);

        $count = 0;
        foreach ($validated['positions'] as $pos) {
            $vehicle->damages()->create([
                'title' => $pos['title'],
                'description' => $pos['description'] ?? '',
                'severity' => $pos['severity'],
                'damage_type' => $pos['damage_type'],
                'status' => 'open',
                'user_id' => auth()->id(),
                'reporter_name' => auth()->user() ? auth()->user()->name : 'System',
                'source' => 'Fahrzeugakte',
            ]);
            $count++;
        }

        $hasCritical = collect($validated['positions'])->contains('severity', 'critical');
        if ($hasCritical) {
            session()->flash('warning', 'ACHTUNG: Kritischer Mangel erfasst! Fahrzeug ggf. sperren.');
        }

        return redirect()->back()->with('success', "$count Position(en) erfolgreich angelegt.");
    }

    // --- BERICHTE (HU/UVV/LIFT/BOKRAFT/KABEL) SPEICHERN & MÄNGEL GENERIEREN ---
        public function storeHu(Request $request, Vehicle $vehicle)
        {
            $validated = $request->validate([
                'inspection_date' => 'required|date',
                'organization' => 'required|string',
                'result' => 'required|string',
                'defects' => 'nullable|array',
                'defects.*.title' => 'required|string|max:255',
                'defects.*.severity' => 'required|in:low,medium,high,critical',
            ]);

            // 1. Bericht als Historie speichern
            $vehicle->huReports()->create([
                'inspection_date' => $validated['inspection_date'],
                'organization' => $validated['organization'],
                'result' => $validated['result'],
            ]);

            // 2. Mängel / Hinweise verarbeiten
            $defectsAdded = 0;
            if ($request->has('defects')) {
                foreach ($request->defects as $defect) {
                    if (empty($defect['title'])) continue;

                    // Prüfen ob die Checkbox "sofort behoben" gesetzt war
                    $isResolved = isset($defect['is_resolved']) && $defect['is_resolved'];

                    $vehicle->damages()->create([
                        'title' => $defect['title'],
                        'description' => 'Festgestellt bei Prüfung: ' . $validated['organization'],
                        'severity' => $defect['severity'],
                        'damage_type' => 'found',
                        'status' => $isResolved ? 'resolved' : 'open',
                        'resolved_at' => $isResolved ? now() : null,
                        'user_id' => auth()->id(),
                        'reporter_name' => auth()->user() ? auth()->user()->name : 'Prüfer',
                        'source' => 'Prüfbericht',
                    ]);
                    $defectsAdded++;
                }
            }

            // 3. Fallback: Wenn das Auto durchfällt, aber keine konkreten Mängel eingetippt wurden
            if (in_array($validated['result'], ['minor', 'unsafe']) && $defectsAdded === 0) {
                $severity = ($validated['result'] == 'unsafe') ? 'critical' : 'high';
                $vehicle->damages()->create([
                    'title' => 'Durchgefallen: Prüfbericht (' . $validated['organization'] . ')',
                    'description' => 'Fahrzeug hat die Prüfung nicht bestanden. Prüfbericht einsehen!',
                    'severity' => $severity,
                    'damage_type' => 'wear',
                    'status' => 'open',
                    'user_id' => auth()->id(),
                    'reporter_name' => auth()->user() ? auth()->user()->name : 'System',
                    'source' => 'Prüfbericht',
                ]);
            }

            // 4. Fristen im Fahrzeug updaten (Nur wenn bestanden)
            if (in_array($validated['result'], ['pass', 'note'])) {
                $inspectDate = Carbon::parse($validated['inspection_date']);

                if ($request->has('update_hu')) {
                    $vehicle->next_hu_date = $vehicle->is_bokraft ? $inspectDate->copy()->addYear() : $inspectDate->copy()->addYears(2);
                }
                if ($request->has('update_uvv')) $vehicle->next_uvv_date = $inspectDate->copy()->addYear();
                if ($request->has('update_bokraft') && $vehicle->is_bokraft) $vehicle->next_bokraft_date = $inspectDate->copy()->addYear();
                if ($request->has('update_lift') && $vehicle->has_lift) $vehicle->next_lift_uvv_date = $inspectDate->copy()->addYear();
                if ($request->has('update_cable') && $vehicle->is_electric) $vehicle->next_cable_uvv_date = $inspectDate->copy()->addYear();

                $vehicle->save();
            }

            return redirect()->back()->with('success', 'Prüfbericht & Positionen erfolgreich erfasst.');
        }

        // --- NEU: BERICHT BEARBEITEN (STATT LÖSCHEN) ---
        public function updateHu(Request $request, Vehicle $vehicle, HuReport $huReport)
        {
            $validated = $request->validate([
                'inspection_date' => 'required|date',
                'organization' => 'required|string',
                'result' => 'required|string',
            ]);

            $huReport->update([
                'inspection_date' => $validated['inspection_date'],
                'organization' => $validated['organization'],
                'result' => $validated['result'],
            ]);

            return redirect()->back()->with('success', 'Prüfbericht wurde aktualisiert.');
        }

    private function validateVehicleData(Request $request, $id = null)
    {
        return $request->validate([
            'license_plate' => 'required|string|unique:vehicles,license_plate,' . $id,
            'manufacturer' => 'required|string',
            'model' => 'required|string',
            'vin' => 'nullable|string',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'first_registration_date' => 'nullable|date',
            'next_hu_date' => 'nullable|date',
            'next_uvv_date' => 'nullable|date',
            'next_lift_uvv_date' => 'nullable|date',
            'next_bokraft_date' => 'nullable|date',
            'next_cable_uvv_date' => 'nullable|date', // Wichtig: E-Kabel Prüfung!
            'fuel_type' => 'nullable|string',
            'power_kw' => 'nullable|integer',
            'power_ps' => 'nullable|integer',
            'mileage' => 'nullable|integer',
        ]);
    }
}
