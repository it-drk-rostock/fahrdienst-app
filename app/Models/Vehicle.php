<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'first_registration_date' => 'date',
        'next_hu_date' => 'date',
        'next_uvv_date' => 'date',
        'next_bokraft_date' => 'date',
        'next_lift_uvv_date' => 'date',
        'next_chair_uvv_date' => 'date',
        'next_cable_uvv_date' => 'date', // NEU: E-Kabel Prüfung hinzugefügt!
        'is_electric' => 'boolean',
        'has_lift' => 'boolean',
        'has_chair' => 'boolean',
        'has_smartfloor' => 'boolean',
        'has_home_cable' => 'boolean',
        'is_bokraft' => 'boolean',
        'is_fully_documented' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONEN
    |--------------------------------------------------------------------------
    */

    public function costCenter() { return $this->belongsTo(CostCenter::class); }
    public function maintenanceTemplate() { return $this->belongsTo(MaintenanceTemplate::class); }
    public function huReports() { return $this->hasMany(HuReport::class)->orderBy('inspection_date', 'desc'); }
    public function damages() { return $this->hasMany(Damage::class)->orderBy('created_at', 'desc'); }

    // Sortierung für Historie
    public function managerAudits() { return $this->hasMany(ManagerAudit::class)->orderBy('checked_at', 'desc'); }
    public function workshopAppointments() { return $this->hasMany(WorkshopAppointment::class)->orderBy('start_time', 'desc'); }

    /**
     * Relation zur Kostenstellen-Historie (Wann war das Auto wo?)
     */
    public function history()
    {
        return $this->hasMany(VehicleCostCenterHistory::class)->orderBy('assigned_from', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER / LOGIK
    |--------------------------------------------------------------------------
    */

    /**
     * ZENTRALE LOGIK: Kostenstelle wechseln & Historie schreiben
     */
    public function assignCostCenter($newCostCenterId)
    {
        // Wenn null übergeben wird oder gleich bleibt -> abbrechen
        if (!$newCostCenterId) return;
        if ($this->cost_center_id == $newCostCenterId) return;

        // 1. Alten Eintrag in der Historie abschließen
        $currentHistory = $this->history()->whereNull('assigned_until')->first();
        if ($currentHistory) {
            $currentHistory->update(['assigned_until' => now()]);
        }

        // 2. Neuen Eintrag in Historie starten
        $this->history()->create([
            'cost_center_id' => $newCostCenterId,
            'assigned_from' => now(),
        ]);

        // 3. Im Fahrzeug selbst aktualisieren
        $this->cost_center_id = $newCostCenterId;
        $this->save();
    }

    // Holt den aktuellsten Audit-Eintrag, der Reifendaten enthält
    public function getLatestTireCheckAttribute()
    {
        return $this->managerAudits()
                    ->whereNotNull('tire_tread_front_left')
                    ->first();
    }

    public function getDocumentationPercentageAttribute()
    {
        $fields = ['license_plate', 'vin', 'manufacturer', 'model', 'first_registration_date', 'cost_center_id', 'type', 'fuel_type', 'hsn', 'tsn'];
        $filled = 0;
        foreach ($fields as $field) { if (!empty($this->$field)) $filled++; }
        return round(($filled / count($fields)) * 100);
    }

    // Chronologische Sortierung der anstehenden Prüfungen
      public function getActiveInspections()
      {
          $inspections = [];

          // HU und UVV sind IMMER Pflicht (egal ob ein Datum drin steht oder nicht)
          $inspections['HU'] = $this->next_hu_date;
          $inspections['UVV'] = $this->next_uvv_date;

          // Bedingte Prüfungen: Nur in die Liste nehmen, wenn das Fahrzeug das Kriterium hat
          if ($this->has_lift) $inspections['Lift UVV'] = $this->next_lift_uvv_date;
          if ($this->is_bokraft) $inspections['BOKraft'] = $this->next_bokraft_date;
          if ($this->is_electric) $inspections['DGUV V3'] = $this->next_cable_uvv_date;

          return collect($inspections)->sortBy(function ($date) {
              // Leere Daten (null) kommen ganz nach vorne in die Liste (höchste Priorität)
              return $date ? $date->timestamp : 0;
          });
      }

    // HTML Status für Mängel
    public function getDamageStatusHtml($damage)
    {
        if ($damage->status == 'resolved') return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">Erledigt</span>';

        if ($damage->workshop_appointment_id) {
            $appointment = $damage->workshopAppointment;
            if ($appointment) {
                if ($appointment->status == 'active') return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200 animate-pulse">⚙️ In Arbeit</span>';
                if ($appointment->status == 'planned') {
                     $date = $appointment->start_time ? $appointment->start_time->format('d.m.') : 'Geplant';
                     return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">📅 Terminiert (' . $date . ')</span>';
                }
            }
        }
        return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Offen</span>';
    }

    /*
    |--------------------------------------------------------------------------
    | NEUES ATTRIBUT: TIMELINE (Ersatz für das alte "History" Attribut)
    |--------------------------------------------------------------------------
    */

    /**
     * Sammelt Ereignisse (Mängel, Audits, HU) für die Darstellung in der Akte.
     * Aufrufbar via: $vehicle->timeline
     */
    public function getTimelineAttribute()
    {
        $timeline = collect();

        // 1. Mängel hinzufügen
        if ($this->relationLoaded('damages')) {
            foreach ($this->damages as $damage) {
                $timeline->push([
                    'date' => $damage->created_at,
                    'type' => 'damage',
                    'class' => 'bg-red-50 text-red-700',
                    'icon' => 'exclamation-circle', // Icon Name für Blade Komponente
                    'title' => 'Mangel: ' . $damage->title,
                    'description' => $damage->description . ' (Status: ' . $damage->status . ')',
                    'user' => $damage->reporter_name ?? 'System',
                    'object' => $damage
                ]);
            }
        }

        // 2. Manager-Prüfungen (Audits/Reifen)
        if ($this->relationLoaded('managerAudits')) {
            foreach ($this->managerAudits as $audit) {
                $timeline->push([
                    'date' => $audit->checked_at,
                    'type' => 'audit',
                    'class' => 'bg-blue-50 text-blue-700',
                    'icon' => 'clipboard-check',
                    'title' => 'Fahrzeugprüfung / Check',
                    'description' => 'KM-Stand: ' . $audit->mileage . ($audit->notes ? ' - ' . $audit->notes : ''),
                    'user' => 'Manager',
                    'object' => $audit
                ]);
            }
        }

        // 3. HU Berichte
        if ($this->relationLoaded('huReports')) {
            foreach ($this->huReports as $report) {
                $timeline->push([
                    'date' => $report->inspection_date ?? $report->created_at, // Fallback
                    'type' => 'hu',
                    'class' => 'bg-green-50 text-green-700',
                    'icon' => 'badge-check',
                    'title' => 'HU/UVV Bericht: ' . $report->result,
                    'description' => $report->notes,
                    'user' => 'System', // FIX: examiner_name entfernt, da die Spalte nicht existiert!
                    'object' => $report
                ]);
            }
        }

        // Sortieren: Neueste Ereignisse zuerst
        return $timeline->sortByDesc('date')->values();
    }
}
