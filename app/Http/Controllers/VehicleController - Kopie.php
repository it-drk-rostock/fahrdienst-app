<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Area;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehicleController extends Controller
{
    // --- STANDARDS (Index, Create, Store, Show, Edit, Update) ---
    // Diese bleiben fast gleich, aber zur Sicherheit hier der ganze Block

    public function index(Request $request)
    {
        $query = Vehicle::with(['costCenter.area', 'damages']);

        if ($request->filled('quick_search')) {
            $term = trim((string) $request->quick_search);
            $query->where(function($q) use ($term) {
                if (is_numeric($term)) {
                    $q->where('license_plate', 'LIKE', "%-{$term}%");
                } else {
                    $q->where('license_plate', 'LIKE', "%{$term}%")
                      ->orWhere('manufacturer', 'LIKE', "%{$term}%")
                      ->orWhere('model', 'LIKE', "%{$term}%");
                }
                if (strlen($term) > 4) {
                    $q->orWhere('vin', 'LIKE', "%{$term}%");
                }
            });
        }
        if ($request->filled('cost_center_id')) {
            $query->where('cost_center_id', $request->cost_center_id);
        }
        if ($request->filled('filter_status')) {
            if ($request->filter_status == 'damages') {
                $query->has('damages');
            } elseif ($request->filter_status == 'due') {
                $limit = Carbon::now()->addDays(30);
                $query->where(function($q) use ($limit) {
                    $q->where('next_hu_date', '<=', $limit)
                      ->orWhere('next_uvv_date', '<=', $limit)
                      ->orWhere('next_bokraft_date', '<=', $limit)
                      ->orWhere('next_lift_uvv_date', '<=', $limit)
                      ->orWhere('next_chair_uvv_date', '<=', $limit);
                });
            }
        }
        $vehicles = $query->orderBy('license_plate', 'asc')->paginate(25);
        $vehicles->appends($request->all());
        $areas = Area::with('costCenters')->get();
        return view('dashboard', compact('vehicles', 'areas'));
    }

    public function create()
    {
        $areas = Area::with('costCenters')->get();
        return view('vehicles.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateVehicleData($request);
        $checkboxes = ['has_lift', 'has_chair', 'has_smartfloor', 'is_electric', 'has_home_cable', 'is_bokraft'];
        foreach ($checkboxes as $box) {
            $validated[$box] = $request->has($box);
        }
        if ($validated['is_bokraft']) {
            $validated['private_use_scope'] = null;
        }
        $vehicle = Vehicle::create($validated);
        $vehicle->calculateProgress();
        return redirect()->route('dashboard')->with('success', 'Fahrzeug angelegt.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['costCenter.area', 'maintenanceTemplate.items', 'huReports', 'damages', 'managerAudits']);
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $areas = Area::with('costCenters')->get();
        return view('vehicles.edit', compact('vehicle', 'areas'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $this->validateVehicleData($request, $vehicle->id);
        $checkboxes = ['has_lift', 'has_chair', 'has_smartfloor', 'is_electric', 'has_home_cable', 'is_bokraft'];
        foreach ($checkboxes as $box) {
            $validated[$box] = $request->has($box);
        }

        if ($validated['is_bokraft']) {
            $validated['private_use_scope'] = null;
            if (!empty($validated['next_hu_date'])) {
                $lastHuReport = $vehicle->huReports()->latest('inspection_date')->first();
                $referenceDate = $lastHuReport ? $lastHuReport->inspection_date : $vehicle->first_registration_date;
                if (!$referenceDate && $vehicle->next_hu_date) {
                     $referenceDate = Carbon::parse($vehicle->next_hu_date)->subYears(2);
                }
                if ($referenceDate) {
                    $referenceDate = Carbon::parse($referenceDate);
                    $plannedHuDate = Carbon::parse($validated['next_hu_date']);
                    if ($plannedHuDate->diffInDays($referenceDate) > 380) {
                        $correctedDate = $referenceDate->copy()->addYear();
                        if ($correctedDate->isFuture()) {
                            $validated['next_hu_date'] = $correctedDate->format('Y-m-d');
                            session()->flash('warning', '⚠️ HU-Datum auf 12 Monate verkürzt (BOKraft).');
                        }
                    }
                }
            }
        } else {
            $validated['next_bokraft_date'] = null;
            $validated['concession_number'] = null;
        }

        $vehicle->update($validated);
        $vehicle->calculateProgress();
        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Gespeichert.');
    }

    public function toggleStatus(Vehicle $vehicle)
    {
        $vehicle->is_fully_documented = !$vehicle->is_fully_documented;
        $vehicle->save();
        return redirect()->back()->with('success', 'Status aktualisiert.');
    }

    // --- NEUE LOGIK FÜR PRÜFUNGEN & MÄNGEL ---

    /**
     * HU Speichern (Mit Checkbox-Logik & ohne Kosten)
     */
    public function storeHu(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'organization' => 'required|string',
            'result' => 'required|in:pass,note,minor,major,unsafe',
            'report_number' => 'nullable|string',
            'notes' => 'nullable|string',

            // Checkboxen
            'update_uvv' => 'nullable|boolean',
            'update_bokraft' => 'nullable|boolean',
            'update_lift' => 'nullable|boolean',
        ]);

        $vehicle->huReports()->create([
            'inspection_date' => $validated['inspection_date'],
            'organization' => $validated['organization'],
            'result' => $validated['result'],
            'report_number' => $validated['report_number'],
            'notes' => $validated['notes'],
            'fees' => 0 // Kostenfeld ignoriert
        ]);

        // Automatische Updates der Termine
        if (in_array($validated['result'], ['pass', 'note'])) {
            $date = Carbon::parse($validated['inspection_date']);
            $updates = [];

            // 1. HU
            $intervalHu = $vehicle->is_bokraft ? 12 : 24;
            $updates['next_hu_date'] = $date->copy()->addMonths($intervalHu);

            // 2. Optionale Updates
            if ($request->has('update_uvv')) {
                $updates['next_uvv_date'] = $date->copy()->addYear();
            }
            if ($request->has('update_bokraft') && $vehicle->is_bokraft) {
                $updates['next_bokraft_date'] = $date->copy()->addYear();
            }
            if ($request->has('update_lift') && $vehicle->has_lift) {
                $updates['next_lift_uvv_date'] = $date->copy()->addYear();
            }

            $vehicle->update($updates);
        }

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'HU-Bericht gespeichert & Termine aktualisiert.');
    }

    /**
     * Audit Speichern (Mit Reifenwerten!)
     */
    public function storeAudit(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'checked_at' => 'required|date',
            'mileage' => 'required|integer',
            'notes' => 'nullable|string',

            // Reifenwerte (Druck & Profil)
            'tire_pressure_front_left' => 'nullable|numeric',
            'tire_pressure_front_right' => 'nullable|numeric',
            'tire_pressure_rear_left' => 'nullable|numeric',
            'tire_pressure_rear_right' => 'nullable|numeric',

            'tire_tread_front_left' => 'nullable|numeric',
            'tire_tread_front_right' => 'nullable|numeric',
            'tire_tread_rear_left' => 'nullable|numeric',
            'tire_tread_rear_right' => 'nullable|numeric',
        ]);

        $vehicle->managerAudits()->create($validated);
        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Prüfung & Reifenwerte gespeichert.');
    }

    /**
     * Mängel Speichern (Mit Bild-Upload)
     */
    public function storeDamage(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach($request->file('images') as $file) {
                $path = $file->store('damages', 'public');
                $imagePaths[] = $path;
            }
        }

        $vehicle->damages()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'status' => 'open',
            'images' => $imagePaths,

            'user_id' => auth()->id(),
            'reporter_name' => auth()->user() ? auth()->user()->name : 'System',
            'source' => 'Digitale Akte',
        ]);

        if ($validated['severity'] === 'critical') {
            session()->flash('warning', 'ACHTUNG: Kritischer Mangel!');
        }

        return redirect()->back()->with('success', 'Mangel erfasst.');
    }

    public function resolveDamage(Request $request, $id)
    {
        $damage = \App\Models\Damage::findOrFail($id);
        $damage->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'user_id' => auth()->id()
        ]);
        return redirect()->back()->with('success', 'Mangel erledigt.');
    }

    private function validateVehicleData(Request $request, $id = null)
    {
        return $request->validate([
            'license_plate' => 'required|string|unique:vehicles,license_plate,' . $id,
            'vin' => 'nullable|string',
            'manufacturer' => 'required|string',
            'model' => 'required|string',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'first_registration_date' => 'nullable|date',
            'next_hu_date' => 'nullable|date',
            'next_uvv_date' => 'nullable|date',
            'next_bokraft_date' => 'nullable|date',
            'next_lift_uvv_date' => 'nullable|date',
            'next_chair_uvv_date' => 'nullable|date',
            'has_lift' => 'boolean',
            'has_chair' => 'boolean',
            'has_smartfloor' => 'boolean',
            'is_electric' => 'boolean',
            'has_home_cable' => 'boolean',
            'is_bokraft' => 'boolean',
            'concession_number' => 'nullable|string|max:50',
            'private_use_scope' => 'nullable|in:ort,bundesland,deutschland,international',
            'fuel_type' => 'nullable|string',
        ]);
    }
}
