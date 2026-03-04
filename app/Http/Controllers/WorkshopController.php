<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Damage;
use App\Models\WorkshopAppointment;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        // ... (Index bleibt unverändert) ...
        $preselectId = $request->input('preselect_vehicle');
        $preselectServices = $request->input('services', []);
        $preselectDamageIds = $request->input('damages', []);

        $allVehicles = Vehicle::with(['costCenter', 'damages' => function($q) {
            $q->where('status', 'open');
        }])->get();

        $providers = ServiceProvider::orderBy('name')->get();

        $drivers = WorkshopAppointment::whereNotNull('transport_driver_name')
                    ->distinct()->pluck('transport_driver_name');

        $dispatchList = [];

        foreach ($allVehicles as $vehicle) {
            $tasks = [];
            $isPreselectedVehicle = ($vehicle->id == $preselectId);

            foreach ($vehicle->damages as $damage) {
                $isSelected = $isPreselectedVehicle && in_array($damage->id, $preselectDamageIds);
                $tasks[] = [
                    'type' => 'damage', 'id' => $damage->id, 'label' => 'Mangel: ' . $damage->title,
                    'date' => $damage->created_at, 'urgent' => in_array($damage->severity, ['high', 'critical']),
                    'preselected' => $isSelected
                ];
            }

            $limit = now()->addDays(60);
            $huSelected = $isPreselectedVehicle && in_array('HU', $preselectServices);
            if ($huSelected || ($vehicle->next_hu_date && $vehicle->next_hu_date <= $limit)) {
                $tasks[] = ['type' => 'service', 'id' => 'HU', 'label' => 'HU fällig', 'date' => $vehicle->next_hu_date, 'urgent' => ($vehicle->next_hu_date && $vehicle->next_hu_date->isPast()), 'preselected' => $huSelected];
            }
            $uvvSelected = $isPreselectedVehicle && in_array('UVV', $preselectServices);
            if ($uvvSelected || ($vehicle->next_uvv_date && $vehicle->next_uvv_date <= $limit)) {
                $tasks[] = ['type' => 'service', 'id' => 'UVV', 'label' => 'UVV fällig', 'date' => $vehicle->next_uvv_date, 'urgent' => ($vehicle->next_uvv_date && $vehicle->next_uvv_date->isPast()), 'preselected' => $uvvSelected];
            }

            if ($isPreselectedVehicle && !empty($preselectServices)) {
                foreach($preselectServices as $serviceName) {
                    if(!in_array($serviceName, ['HU', 'UVV'])) {
                        $tasks[] = ['type' => 'adhoc_service', 'id' => $serviceName, 'label' => 'Beauftragt: ' . $serviceName, 'date' => now(), 'urgent' => true, 'preselected' => true];
                    }
                }
            }

            if (count($tasks) > 0 || $isPreselectedVehicle) {
                $vehicle->setAttribute('todo_list', $tasks);
                $vehicle->setAttribute('is_preselected', $isPreselectedVehicle);
                $dispatchList[] = $vehicle;
            }
        }

        usort($dispatchList, fn($a, $b) => $b->is_preselected <=> $a->is_preselected);

        return view('workshop.dispatch', ['vehicles' => $dispatchList, 'providers' => $providers, 'drivers' => $drivers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'provider_name' => 'required|string',
            'start_time' => 'required|date',
            'planned_end_time' => 'required|date|after_or_equal:start_time',
            'selected_damages' => 'nullable|array',
            'selected_services' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_transport_organized' => 'nullable',
            'transport_method' => 'nullable|string',
            'transport_driver_name' => 'nullable|string',
            'transport_driver_status' => 'nullable|string',
            'transport_billing_department' => 'nullable|string',
            'has_rental_car' => 'nullable',
            'pickup_method' => 'nullable|string',
            'pickup_driver_name' => 'nullable|string',
            'pickup_driver_status' => 'nullable|string',
        ]);

        $provider = ServiceProvider::firstOrCreate(['name' => trim($validated['provider_name'])], ['type' => 'workshop']);

        // --- REINIGUNGSLOGIK START ---
        // Wenn Werkstatt holt -> Kein Fahrer nötig
        if (($validated['transport_method'] ?? '') === 'replacement') {
            $validated['transport_driver_status'] = null;
            $validated['transport_driver_name'] = null;
        }
        // Wenn Werkstatt bringt -> Kein Abholer nötig
        if (($validated['pickup_method'] ?? '') === 'workshop') {
            $validated['pickup_driver_status'] = null;
            $validated['pickup_driver_name'] = null;
        }
        // --- REINIGUNGSLOGIK ENDE ---

        $appointment = WorkshopAppointment::create([
            'vehicle_id' => $validated['vehicle_id'],
            'service_provider_id' => $provider->id,
            'workshop_name' => $provider->name,
            'start_time' => $validated['start_time'],
            'planned_end_time' => $validated['planned_end_time'],
            'status' => 'planned',
            'services' => $request->selected_services ?? [],
            'notes' => $validated['notes'] ?? null,

            'is_transport_organized' => $request->has('is_transport_organized'),
            'transport_method' => $validated['transport_method'],
            'transport_driver_name' => $validated['transport_driver_name'],
            'transport_driver_status' => $validated['transport_driver_status'],
            'transport_billing_department' => $validated['transport_billing_department'],
            'transport_start_time' => $request->has('is_transport_organized') ? $validated['start_time'] : null,
            'has_rental_car' => $request->has('has_rental_car'),

            'is_pickup_needed' => $request->has('is_pickup_needed'),
            'pickup_method' => $validated['pickup_method'],
            'pickup_driver_name' => $validated['pickup_driver_name'],
            'pickup_driver_status' => $validated['pickup_driver_status'],
        ]);

        if (!empty($request->selected_damages)) {
            Damage::whereIn('id', $request->selected_damages)->update([
                'workshop_appointment_id' => $appointment->id,
                'status' => 'commissioned'
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Auftrag erstellt.');
    }

    public function update(Request $request, $id)
    {
        $workshopAppointment = WorkshopAppointment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:planned,active,resolved',
            'start_time' => 'required|date',
            'planned_end_time' => 'required|date',
            'actual_end_time' => 'nullable|date',
            'notes' => 'nullable|string',

            'transport_method' => 'nullable|string',
            'transport_driver_name' => 'nullable|string',
            'transport_driver_status' => 'nullable|string',
            'transport_billing_department' => 'nullable|string',
            'has_rental_car' => 'nullable',

            'pickup_method' => 'nullable|string',
            'pickup_driver_name' => 'nullable|string',
            'pickup_driver_status' => 'nullable|string',

            'new_positions' => 'nullable|array',
            'remove_positions' => 'nullable|array',
            'update_hu_date' => 'nullable|date',
            'update_uvv_date' => 'nullable|date',
        ]);

        // Checkboxen
        $validated['is_transport_organized'] = $request->has('is_transport_organized');
        $validated['is_pickup_needed'] = $request->has('is_pickup_needed');
        $validated['has_rental_car'] = $request->has('has_rental_car');

        // --- REINIGUNGSLOGIK START (Auch beim Update wichtig!) ---
        // Hinfahrt bereinigen
        if (($validated['transport_method'] ?? '') === 'replacement') {
            $validated['transport_driver_status'] = null; // Status löschen
            $validated['transport_driver_name'] = null;
        }

        // Rückfahrt bereinigen
        if (($validated['pickup_method'] ?? '') === 'workshop') {
            $validated['pickup_driver_status'] = null; // Status löschen
            $validated['pickup_driver_name'] = null;
        }
        // --- REINIGUNGSLOGIK ENDE ---

        $workshopAppointment->update($validated);

        // Positionen Logik
        if (!empty($validated['remove_positions'])) {
            $currentServices = $workshopAppointment->services ?? [];
            foreach ($validated['remove_positions'] as $removeItem) {
                if (str_contains($removeItem, ':')) {
                    [$type, $itemId] = explode(':', $removeItem, 2);
                    if ($type === 'damage') {
                        Damage::where('id', $itemId)->update(['workshop_appointment_id' => null, 'status' => 'open']);
                    } elseif ($type === 'service') {
                        $currentServices = array_values(array_diff($currentServices, [$itemId]));
                    }
                }
            }
            $workshopAppointment->update(['services' => $currentServices]);
        }

        if (!empty($validated['new_positions'])) {
            $currentServices = $workshopAppointment->services ?? [];
            foreach ($validated['new_positions'] as $pos) {
                if (str_starts_with($pos, 'damage_id:')) {
                    $damageId = substr($pos, 10);
                    Damage::where('id', $damageId)->update(['workshop_appointment_id' => $workshopAppointment->id, 'status' => 'commissioned']);
                } elseif ($pos === 'HU' || $pos === 'UVV') {
                    if (!in_array($pos, $currentServices)) $currentServices[] = $pos;
                } else {
                    Damage::create([
                        'vehicle_id' => $workshopAppointment->vehicle_id,
                        'title' => $pos,
                        'status' => 'commissioned',
                        'workshop_appointment_id' => $workshopAppointment->id,
                        'description' => 'Manuell im Kalender hinzugefügt',
                        'severity' => 'medium',
                        'damage_type' => 'wear',
                        'user_id' => auth()->id(),
                        'reporter_name' => auth()->user() ? auth()->user()->name : 'Kalender',
                        'source' => 'Kalender',
                    ]);
                }
            }
            $workshopAppointment->update(['services' => $currentServices]);
        }

        if ($validated['status'] === 'resolved') {
            $workshopAppointment->damages()->update(['status' => 'resolved', 'resolved_at' => now()]);
            if ($request->filled('update_hu_date')) $workshopAppointment->vehicle->update(['next_hu_date' => $request->update_hu_date]);
            if ($request->filled('update_uvv_date')) $workshopAppointment->vehicle->update(['next_uvv_date' => $request->update_uvv_date]);
        }

        return redirect()->back()->with('success', 'Termin aktualisiert.');
    }

    public function destroy($id)
    {
        $appointment = WorkshopAppointment::findOrFail($id);
        if ($appointment->status == 'resolved') return redirect()->back()->with('error', 'Erledigte Termine nicht löschbar.');
        DB::transaction(function () use ($appointment) {
            $appointment->damages()->update(['status' => 'open', 'workshop_appointment_id' => null]);
            $appointment->delete();
        });
        return redirect()->back()->with('success', 'Termin gelöscht.');
    }
}
