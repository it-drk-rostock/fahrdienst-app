<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\ManagerAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerAuditController extends Controller
{
  public function storeTires(Request $request, Vehicle $vehicle)
  {
      // 1. EINGABEN BEREINIGEN (Komma zu Punkt)
      $input = $request->all();
      $tireFields = ['tire_depth_fl', 'tire_depth_fr', 'tire_depth_rl', 'tire_depth_rr'];

      foreach ($tireFields as $field) {
          if (isset($input[$field])) {
              $input[$field] = str_replace(',', '.', $input[$field]);
          }
      }

      $request->replace($input);

      // 2. VALIDIEREN (ACHTUNG: 'winter_tires' hier NICHT prüfen, das machen wir unten)
      $validated = $request->validate([
          'tire_depth_fl' => 'required|numeric|min:0|max:20',
          'tire_depth_fr' => 'required|numeric|min:0|max:20',
          'tire_depth_rl' => 'required|numeric|min:0|max:20',
          'tire_depth_rr' => 'required|numeric|min:0|max:20',
          'mileage'       => 'required|integer|min:0',
      ]);

      // 3. SPEICHERN
      ManagerAudit::create([
          'vehicle_id' => $vehicle->id,
          'user_id' => Auth::id(),
          'checked_at' => now(),
          'mileage' => $validated['mileage'],
          'tire_depth_fl' => $validated['tire_depth_fl'],
          'tire_depth_fr' => $validated['tire_depth_fr'],
          'tire_depth_rl' => $validated['tire_depth_rl'],
          'tire_depth_rr' => $validated['tire_depth_rr'],

          // Hier prüfen wir einfach: Ist der Haken da? Ja/Nein.
          'winter_tires'  => $request->has('winter_tires'),

          'remarks' => 'Schnell-Update Reifen',
      ]);

      $vehicle->update([
          'current_mileage' => $validated['mileage']
      ]);

      return redirect()->route('vehicles.show', $vehicle->id)
          ->with('success', 'Reifendaten aktualisiert.');
  }
}
