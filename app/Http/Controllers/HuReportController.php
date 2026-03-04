<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\HuReport;
use App\Models\Damage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HuReportController extends Controller
{
    /**
     * Speichert einen neuen HU-Bericht und generiert automatisch Schäden aus der Mängelliste.
     */
    public function store(Request $request, Vehicle $vehicle)
    {
        // 1. Validierung der Eingaben
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'result'          => 'required|in:pass,minor,major,note', // pass=OK, minor=GM, major=EM, note=Info
            'report_number'   => 'nullable|string|max:50',
            'org_selection'   => 'required|string',
            'org_custom'      => 'nullable|string|required_if:org_selection,Sonstige',

            // Die Arrays aus der dynamischen Liste im Frontend (HU Box)
            'hu_defects_text'      => 'nullable|array',
            'hu_defects_text.*'    => 'nullable|string|max:255',
            'hu_defects_severity'  => 'nullable|array',
            'hu_defects_severity.*'=> 'nullable|string|in:note,minor,major',
        ]);

        // 2. Organisation bestimmen (Dropdown oder Textfeld "Sonstige")
        $organization = $request->org_selection === 'Sonstige'
            ? $request->org_custom
            : $request->org_selection;

        // 3. HU Bericht in der Datenbank anlegen
        $huReport = $vehicle->huReports()->create([
            'inspection_date' => $request->inspection_date,
            'result'          => $request->result,
            'report_number'   => $request->report_number,
            'organization'    => $organization,
            // 'notes' lassen wir leer oder null, da wir Einzelschäden erzeugen
        ]);

        // 4. Mängel verarbeiten und als Schäden anlegen
        // Das passiert nur, wenn das Ergebnis NICHT "Bestanden" ist und Texte gesendet wurden
        if ($request->result !== 'pass' && $request->has('hu_defects_text')) {

            $texts = $request->hu_defects_text;
            $severities = $request->hu_defects_severity ?? []; // Fallback, falls leer

            foreach ($texts as $index => $text) {
                // Leere Zeilen überspringen
                if (empty($text) || trim($text) === '') {
                    continue;
                }

                // Schweregrad für diese spezifische Zeile holen (Fallback: minor/GM)
                $severity = $severities[$index] ?? 'minor';

                // Präfix generieren für bessere Lesbarkeit in der Schadensakte
                $prefix = match($severity) {
                    'major' => '[HU-EM] ', // Erheblicher Mangel
                    'minor' => '[HU-GM] ', // Geringfügiger Mangel
                    'note'  => '[HU-Info] ', // Hinweis
                    default => '[HU] ',
                };

                // Eintrag in der 'damages' Tabelle erstellen
                $vehicle->damages()->create([
                    'description'     => $prefix . $text,
                    'status'          => 'open',   // HU Mängel sind immer erst mal offen
                    'source'          => 'HU',     // WICHTIG: Herkunft ist HU
                    'damage_type'     => 'wear',   // Standardmäßig als Verschleiß/Allgemein werten
                    'insurance_cover' => false,    // HU Mängel zahlt i.d.R. keine Versicherung
                ]);
            }
        }

        // 5. Nächste HU aktualisieren (nur bei Bestanden)
        if ($request->result === 'pass') {
            $inspectionDate = Carbon::parse($request->inspection_date);

            // Logik: Hier +1 Jahr (typisch für Gewerbe). Für Privat-PKW wäre addYears(2) richtig.
            $vehicle->update([
                'next_hu_date' => $inspectionDate->addYear(),
            ]);
        }

        return redirect()->back()->with('success', 'HU-Bericht gespeichert und Mängel übernommen.');
    }
}
