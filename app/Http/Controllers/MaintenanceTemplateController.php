<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceTemplate;
use Illuminate\Http\Request;

class MaintenanceTemplateController extends Controller
{
    /**
     * BEREICH 1: ÜBERSICHT DER VORLAGEN
     * ==========================================
     */
    public function index()
    {
        // Wir trennen in "Bestätigt" und "Neu/Ungeprüft"
        $unconfirmedTemplates = MaintenanceTemplate::where('is_confirmed', false)->get();
        $confirmedTemplates = MaintenanceTemplate::where('is_confirmed', true)->get();

        return view('maintenance_templates.index', compact('unconfirmedTemplates', 'confirmedTemplates'));
    }

    /**
     * BEREICH 2: VORLAGE BESTÄTIGEN ODER ÜBERSCHREIBEN
     * ==========================================
     */
     public function confirm(Request $request, MaintenanceTemplate $template)
 {
     // 1. Template-Basisdaten updaten
     $template->update([
         'is_confirmed' => true
     ]);

     // 2. Bestehende Items löschen (um sie sauber neu zu schreiben)
     $template->items()->delete();

     // 3. Neue Multi-Intervalle anlegen (aus dem Formular-Array)
     if($request->has('tasks')) {
         foreach($request->tasks as $index => $taskName) {
             $template->items()->create([
                 'task_name'       => $taskName,
                 'interval_km'     => $request->km[$index] ?? null,
                 'interval_months' => $request->months[$index] ?? null,
             ]);
         }
     }

     return redirect()->back()->with('success', 'Wartungsplan mit allen Intervallen verifiziert.');
 }

    /**
     * BEREICH 3: NEUE VORLAGE MANUELL ANLEGEN
     * ==========================================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'manufacturer' => 'required',
            'model_series' => 'required',
            'interval_km' => 'required|integer',
            'interval_months' => 'required|integer',
        ]);

        $validated['is_confirmed'] = true; // Manuell angelegte sind sofort bestätigt
        MaintenanceTemplate::create($validated);

        return redirect()->back()->with('success', 'Neuer Wartungsplan angelegt.');
    }
}
