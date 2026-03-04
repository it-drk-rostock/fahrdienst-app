<?php

namespace App\Http\Controllers;

use App\Models\WorkshopAppointment;
use App\Models\Vehicle;
use App\Models\CostCenter;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
        $mode = $request->input('mode', 'month');

        if ($mode === 'week') {
            $startOfPeriod = $date->copy()->startOfWeek();
            $endOfPeriod = $date->copy()->endOfWeek();
        } else {
            $startOfPeriod = $date->copy()->startOfMonth()->startOfWeek();
            $endOfPeriod = $date->copy()->endOfMonth()->endOfWeek();
        }

        // 1. Echte Termine laden
        $appointments = WorkshopAppointment::with(['vehicle.damages', 'damages', 'serviceProvider'])
            ->where(function($q) use ($startOfPeriod, $endOfPeriod) {
                $q->whereBetween('start_time', [$startOfPeriod, $endOfPeriod])
                  ->orWhereBetween('planned_end_time', [$startOfPeriod, $endOfPeriod])
                  ->orWhere(function($sub) use ($startOfPeriod, $endOfPeriod) {
                      $sub->where('start_time', '<', $startOfPeriod)
                          ->where('planned_end_time', '>', $endOfPeriod);
                  });
            })->get();

        // 2. Virtuelle (Planbare) Termine laden
        $virtualEvents = $this->getVirtualEvents($startOfPeriod, $endOfPeriod, $appointments);
        $allEvents = $appointments->merge($virtualEvents);

        $this->applyColors($allEvents);

        if ($mode === 'week') {
            $this->calculateWeekLayout($allEvents);
        }

        // 4. Kalenderstruktur bauen
        $specialDays = $this->getSpecialDaysMV($startOfPeriod, $endOfPeriod);
        $calendar = [];
        $current = $startOfPeriod->copy();

        while ($current <= $endOfPeriod) {
            $dateStr = $current->format('Y-m-d');

            $dayEvents = $allEvents->filter(function($app) use ($dateStr) {
                $start = $app->start_time->format('Y-m-d');
                $end = $app->planned_end_time->format('Y-m-d');
                return $dateStr >= $start && $dateStr <= $end;
            });

            $dayType = 'regular';
            $dayLabel = '';
            if ($current->isWeekend()) $dayType = 'weekend';
            if (isset($specialDays['vacations'][$dateStr])) { $dayType = 'vacation'; $dayLabel = $specialDays['vacations'][$dateStr]; }
            if (isset($specialDays['holidays'][$dateStr])) { $dayType = 'holiday'; $dayLabel = $specialDays['holidays'][$dateStr]; }

            $calendar[] = [
                'date' => $current->copy(),
                'isCurrentMonth' => $current->month === $date->month,
                'isToday' => $current->isToday(),
                'events' => $dayEvents,
                'type' => $dayType,
                'typeLabel' => $dayLabel
            ];
            $current->addDay();
        }

        $costCenters = CostCenter::orderBy('code')->get();
        $providers = ServiceProvider::orderBy('name')->get();

        // NEU: Fahrzeuge laden inkl. offener Mängel und offener Termine für die Schnellerfassung
        $vehicles = Vehicle::with([
            'damages' => function($q) {
                $q->where('status', 'open')->whereNull('workshop_appointment_id');
            },
            'workshopAppointments' => function($q) {
                $q->whereIn('status', ['planned', 'active']);
            }
        ])->select('id', 'license_plate', 'model')->orderBy('license_plate')->get();

        return view('calendar.index', [
            'calendar' => $calendar,
            'date' => $date,
            'mode' => $mode,
            'costCenters' => $costCenters,
            'providers' => $providers,
            'vehicles' => $vehicles
        ]);
    }

    // --- HELPER FUNKTIONEN ---
    private function getVirtualEvents($start, $end, $existingAppointments) {
        $vehicles = Vehicle::whereBetween('next_hu_date', [$start, $end])->orWhereBetween('next_uvv_date', [$start, $end])->get();
        $virtuals = collect();
        foreach ($vehicles as $vehicle) {
            $hasAppointment = $existingAppointments->where('vehicle_id', $vehicle->id)->count() > 0;
            if ($hasAppointment) continue;
            if ($vehicle->next_hu_date && $vehicle->next_hu_date >= $start && $vehicle->next_hu_date <= $end) {
                $virtuals->push($this->createVirtualEvent($vehicle, 'HU fällig', $vehicle->next_hu_date));
            }
            if ($vehicle->next_uvv_date && $vehicle->next_uvv_date >= $start && $vehicle->next_uvv_date <= $end) {
                $virtuals->push($this->createVirtualEvent($vehicle, 'UVV fällig', $vehicle->next_uvv_date));
            }
        }
        return $virtuals;
    }

    private function createVirtualEvent($vehicle, $label, $date) {
        $app = new WorkshopAppointment();
        $app->id = 'virtual_' . $vehicle->id . '_' . rand(100,999);
        $app->vehicle_id = $vehicle->id;
        $app->status = 'virtual';
        $app->start_time = $date->copy()->setTime(8, 0);
        $app->planned_end_time = $date->copy()->setTime(9, 0);
        $app->setRelation('vehicle', $vehicle);
        $app->workshop_name = 'Planung nötig';
        $app->virtual_label = $label;
        $app->setRelation('damages', collect());
        $app->setRelation('services', []);
        return $app;
    }

    private function calculateWeekLayout($events) {
        $eventsByDay = $events->groupBy(fn($e) => $e->start_time->format('Y-m-d'));
        foreach ($eventsByDay as $day => $dayEvents) {
            $sortedEvents = $dayEvents->sortBy('start_time');
            $lanes = [];
            foreach ($sortedEvents as $event) {
                $placed = false;
                foreach ($lanes as $laneIndex => $endTime) {
                    if ($event->start_time >= $endTime) {
                        $lanes[$laneIndex] = $event->planned_end_time;
                        $event->setAttribute('visual_col', $laneIndex);
                        $placed = true; break;
                    }
                }
                if (!$placed) {
                    $lanes[] = $event->planned_end_time;
                    $event->setAttribute('visual_col', count($lanes) - 1);
                }
            }
            $totalCols = count($lanes);
            foreach ($sortedEvents as $event) $event->setAttribute('visual_total_cols', $totalCols);
        }
    }

    private function applyColors($events) {
        $palette = ['#fca5a5', '#fdba74', '#fcd34d', '#bef264', '#6ee7b7', '#67e8f9', '#93c5fd', '#c4b5fd', '#fda4af', '#cbd5e1'];
        foreach ($events as $app) {
            if ($app->status === 'virtual') { $app->setAttribute('calendar_color', '#ffffff'); continue; }
            if (!$app->vehicle) { $app->setAttribute('calendar_color', '#e5e7eb'); continue; }
            $dbColor = $app->vehicle->calendar_color;
            $app->setAttribute('calendar_color', (!empty($dbColor) && $dbColor !== '#3B82F6') ? $dbColor : $palette[$app->vehicle_id % count($palette)]);
        }
    }

    private function getSpecialDaysMV(Carbon $start, Carbon $end) {
        $year = $start->year;
        $holidays = [ $year.'-01-01' => 'Neujahr', $year.'-03-08' => 'Frauentag', $year.'-05-01' => 'Tag der Arbeit', $year.'-10-03' => 'Tag der Dt. Einheit', $year.'-10-31' => 'Reformationstag', $year.'-12-25' => '1. Weihnachten', $year.'-12-26' => '2. Weihnachten' ];
        $easter = Carbon::createFromDate($year, 3, 21)->addDays(easter_days($year));
        $holidays[$easter->copy()->subDays(2)->format('Y-m-d')] = 'Karfreitag';
        $holidays[$easter->copy()->addDay()->format('Y-m-d')] = 'Ostermontag';
        $holidays[$easter->copy()->addDays(39)->format('Y-m-d')] = 'Himmelfahrt';
        $holidays[$easter->copy()->addDays(50)->format('Y-m-d')] = 'Pfingstmontag';
        $vacationRanges = [
            ['start' => '2026-02-02', 'end' => '2026-02-14', 'name' => 'Winter'], ['start' => '2026-03-30', 'end' => '2026-04-08', 'name' => 'Ostern'], ['start' => '2026-05-22', 'end' => '2026-05-26', 'name' => 'Pfingsten'], ['start' => '2026-07-13', 'end' => '2026-08-22', 'name' => 'Sommer'], ['start' => '2026-10-19', 'end' => '2026-10-24', 'name' => 'Herbst'], ['start' => '2026-12-21', 'end' => '2027-01-02', 'name' => 'Weihnachten'], ['start' => '2025-02-03', 'end' => '2025-02-15', 'name' => 'Winter'], ['start' => '2025-04-14', 'end' => '2025-04-23', 'name' => 'Ostern'],
        ];
        $result = ['holidays' => [], 'vacations' => []];
        $curr = $start->copy();
        while($curr <= $end) {
            $d = $curr->format('Y-m-d');
            if (isset($holidays[$d])) $result['holidays'][$d] = $holidays[$d];
            foreach($vacationRanges as $range) { if ($d >= $range['start'] && $d <= $range['end']) { $result['vacations'][$d] = $range['name']; break; } }
            $curr->addDay();
        }
        return $result;
    }
}
