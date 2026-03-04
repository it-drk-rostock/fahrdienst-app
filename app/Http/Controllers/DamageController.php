<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Damage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DamageController extends Controller
{
    /**
     * Speichert Mängel (wird meist über VehicleController aufgerufen,
     * aber falls wir Einzel-Speicherung brauchen, hier der Vollständigkeit halber).
     */
    public function store(Request $request, Vehicle $vehicle)
    {
        // ... (Wird aktuell meist über VehicleController->storeDamage gehandhabt)
    }

    /**
     * Aktualisiert einen bestehenden Schaden & Lädt Bilder hoch
     */
    public function update(Request $request, Damage $damage)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string',
            'status'      => 'required|in:open,deferred,in_repair,resolved',
            'severity'    => 'required|in:low,medium,high,critical',
            'damage_type' => 'required|in:wear,found,accident_own,accident_other',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // Max 10MB pro Bild
        ]);

        // 1. Ordner-Struktur berechnen
$vehicle = $damage->vehicle;
// Kennzeichen bereinigen (Leerzeichen raus für Ordnernamen: "BS-FP 106" -> "BS-FP106")
$plateFolder = str_replace(' ', '', $vehicle->license_plate);

// Datum des Schadens (oder heute, falls null)
$dateStr = $damage->created_at ? $damage->created_at->format('Y-m-d') : now()->format('Y-m-d');

// Mapping der DB-Werte auf deine gewünschten deutschen Ordnernamen
$typeMap = [
    'accident_other' => 'Unfall_Fremd',
    'accident_own'   => 'Unfall_Selbst',
    'found'          => 'Vorfinde',
    'wear'           => 'Allgemein_Schaden', // Für Verschleiß ohne Unfallbezug
];

// Ordnername bauen: z.B. "Unfall_Fremd_2026-01-05_ID12"
// Die ID am Ende garantiert Eindeutigkeit, falls 2 Schäden am gleichen Tag sind.
$subFolder = ($typeMap[$damage->damage_type] ?? 'Sonstiges') . '_' . $dateStr . '_ID' . $damage->id;

// Endgültiger Pfad: storage/app/public/vehicles/BS-FP106/Unfall_Fremd_.../
$storagePath = "vehicles/{$plateFolder}/{$subFolder}";

// 2. Bilder speichern
$currentImages = $damage->images ?? [];

if ($request->hasFile('images')) {
    foreach ($request->file('images') as $file) {
        // Speichert die Datei in der neuen Struktur
        $path = $file->store($storagePath, 'public');
        $currentImages[] = $path;
    }
}

        // 2. Update durchführen
        $damage->update([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'status'      => $validated['status'],
            'severity'    => $validated['severity'],
            'damage_type' => $validated['damage_type'],
            'images'      => $currentImages, // Das aktualisierte Array speichern
        ]);

        return redirect()->back()->with('success', 'Mangel aktualisiert & Bilder hochgeladen.');
    }

    /**
     * Löscht ein einzelnes Bild aus einem Schaden (Optional, für AJAX oder Route)
     */
    public function destroyImage(Request $request, Damage $damage, $index)
    {
        $images = $damage->images ?? [];

        if (isset($images[$index])) {
            // Datei vom Server löschen
            Storage::disk('public')->delete($images[$index]);

            // Aus Array entfernen und Indizes neu ordnen
            unset($images[$index]);
            $damage->update(['images' => array_values($images)]);

            return redirect()->back()->with('success', 'Bild gelöscht.');
        }

        return redirect()->back()->with('error', 'Bild nicht gefunden.');
    }

    public function destroy(Damage $damage)
    {
        $damage->delete();
        return redirect()->back()->with('success', 'Eintrag gelöscht.');
    }
}
