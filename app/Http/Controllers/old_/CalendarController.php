<?php

namespace App\Http\Controllers;

use App\Models\WorkshopAppointment;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Termine laden
        $appointments = WorkshopAppointment::with(['vehicle', 'damages', 'serviceProvider'])
            ->where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_time', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('planned_end_time', [$startOfMonth, $endOfMonth])
                  ->orWhere(function($sub) use ($startOfMonth, $endOfMonth) {
                      $sub->where('start_time', '<', $startOfMonth)
                          ->where('planned_end_time', '>', $endOfMonth);
                  });
            })
            ->get();

        // --- PASTELL-PALETTE ---
        // Diese Farben werden genutzt, wenn das Auto keine eigene 'gute' Farbe hat
        $colorPalette = [
            '#fca5a5', // Rot
            '#fdba74', // Orange
            '#fcd34d', // Gelb
            '#bef264', // Lime
            '#6ee7b7', // Smaragd
            '#67e8f9', // Cyan
            '#7dd3fc', // Himmelblau
            '#93c5fd', // Blau
            '#a5b4fc', // Indigo
            '#c4b5fd', // Violett
            '#f0abfc', // Fuchsia
            '#fda4af', // Rose
            '#cbd5e1', // Schiefer
            '#d6d3d1', // Stein
            '#86efac', // Grün
            '#5eead4', // Teal
            '#e9d5ff', // Hell-Lila
            '#f9a8d4', // Pink
            '#fdba74', // Orange-Hell
            '#94a3b8', // Blau-Grau
        ];

        foreach ($appointments as $app) {
            // Sicherstellen, dass ein Fahrzeug verknüpft ist
            if (!$app->vehicle) {
                $app->setAttribute('calendar_color', '#e5e7eb');
                continue;
            }

            // 1. Farbe aus Datenbank holen
            $dbColor = $app->vehicle->calendar_color;

            // 2. Prüfen: Ist die Farbe gültig und NICHT das Standard-Blau (#3B82F6)?
            if (!empty($dbColor) && str_starts_with($dbColor, '#') && $dbColor !== '#3B82F6') {
                // Wenn eine EIGENE Farbe gesetzt ist (z.B. knallrot für Feuerwehr), nutzen wir die
                $finalColor = $dbColor;
            }
            else {
                // 3. Fallback: Wir berechnen die Farbe aus der ID
                // ID 108 % 20 = Index 8  (#a5b4fc - Indigo)
                // ID 106 % 20 = Index 6  (#7dd3fc - Himmelblau)
                // -> Sie MÜSSEN unterschiedlich sein!

                $colorIndex = $app->vehicle_id % count($colorPalette);
                $finalColor = $colorPalette[$colorIndex];
            }

            // WICHTIG: Farbe ins Model schreiben, damit Blade sie sieht
            $app->setAttribute('calendar_color', $finalColor);
        }

        // Kalender-Raster aufbauen
        $calendar = [];
        $startOfWeek = $startOfMonth->copy()->startOfWeek();
        $endOfWeek = $endOfMonth->copy()->endOfWeek();
        $current = $startOfWeek->copy();

        while ($current <= $endOfWeek) {
            $dayEvents = $appointments->filter(function($app) use ($current) {
                return $current->format('Y-m-d') >= $app->start_time->format('Y-m-d') &&
                       $current->format('Y-m-d') <= $app->planned_end_time->format('Y-m-d');
            });

            $calendar[] = [
                'date' => $current->copy(),
                'isCurrentMonth' => $current->month === $date->month,
                'isToday' => $current->isToday(),
                'events' => $dayEvents
            ];
            $current->addDay();
        }

        $costCenters = CostCenter::orderBy('code')->get();

        return view('calendar.index', [ // WICHTIG: View heißt 'index', nicht 'index.blade'
            'calendar' => $calendar,
            'date' => $date,
            'costCenters' => $costCenters
        ]);
    }
}
