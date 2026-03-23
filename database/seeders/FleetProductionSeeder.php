<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\CostCenter;
use App\Models\Area;
use App\Models\MaintenanceTemplate;
use App\Models\MaintenanceItem; // Wichtig für die neuen Tasks
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FleetProductionSeeder extends Seeder
{
    public function run()
    {
        // 1. Admin User
        // Wir prüfen erst, ob er schon existiert, um Fehler zu vermeiden
        if (!User::where('email', 'admin@fms')->exists()) {
            User::create([
                'name' => 'Fuhrparkadmin',
                'email' => 'admin@fms',
                'password' => Hash::make('password_initial'),
            ]);
        }}
/*
        // ==========================================
        // 2. STRUKTUR AUFBAUEN (Areas & CostCenters)
        // ==========================================

        // Bereich BS (Hauptgesellschaft)
        $areaBS = Area::create(['name' => 'Bereich BS (Haupt)']);
        $ccFD = CostCenter::create(['name' => 'Fahrdienst', 'code' => 'FD', 'area_id' => $areaBS->id]);
        $ccFP = CostCenter::create(['name' => 'Fuhrpark', 'code' => 'FP', 'area_id' => $areaBS->id]);

        // Bereich PD (Tochter)
        $areaPD = Area::create(['name' => 'Tochter PD']);
        $ccPDm1 = CostCenter::create(['name' => 'Pflegedienst 1', 'code' => 'PDm1', 'area_id' => $areaPD->id]);
        $ccPDm2 = CostCenter::create(['name' => 'Pflegedienst 2', 'code' => 'PDm2', 'area_id' => $areaPD->id]);
        $ccPDm3 = CostCenter::create(['name' => 'Pflegedienst 3', 'code' => 'PDm3', 'area_id' => $areaPD->id]);
        $ccSWA1 = CostCenter::create(['name' => 'SWA Standort 1', 'code' => 'SWA1', 'area_id' => $areaPD->id]);
        $ccSWA2 = CostCenter::create(['name' => 'SWA Standort 2', 'code' => 'SWA2', 'area_id' => $areaPD->id]);

        // ==========================================
        // 3. WARTUNGSPLÄNE (TEMPLATES & ITEMS)
        // ==========================================

        // Plan A: Ford Transit (Detailliert)
        $tmpl_Transit = MaintenanceTemplate::create([
            'manufacturer' => 'Ford',
            'model_series' => 'Transit H2/L2',
            'interval_km' => 40000,
            'interval_months' => 24,
            'warranty_months' => 24,
            'is_confirmed' => true
        ]);
        // Tasks dazu
        MaintenanceItem::create(['maintenance_template_id' => $tmpl_Transit->id, 'task_name' => 'Ölwechsel & Filter', 'interval_km' => 40000, 'interval_months' => 24]);
        MaintenanceItem::create(['maintenance_template_id' => $tmpl_Transit->id, 'task_name' => 'Sichtprüfung Unterboden', 'interval_km' => 20000, 'interval_months' => 12]);
        MaintenanceItem::create(['maintenance_template_id' => $tmpl_Transit->id, 'task_name' => 'Bremsflüssigkeit', 'interval_km' => 60000, 'interval_months' => 24]);

        // Plan B: Opel Zafira (Einfach)
        $tmpl_Zafira = MaintenanceTemplate::create(['manufacturer' => 'Opel', 'model_series' => 'Zafira H1/L2', 'interval_km' => 30000, 'interval_months' => 12, 'warranty_months' => 24, 'is_confirmed' => true]);

        // Plan C: Citroen C1 (Kleinwagen)
        $tmpl_C1 = MaintenanceTemplate::create(['manufacturer' => 'Citroen', 'model_series' => 'C1', 'interval_km' => 15000, 'interval_months' => 12, 'warranty_months' => 24, 'is_confirmed' => true]);
        MaintenanceItem::create(['maintenance_template_id' => $tmpl_C1->id, 'task_name' => 'Kleine Inspektion', 'interval_km' => 15000, 'interval_months' => 12]);


        // ==========================================
        // 4. FAHRZEUGE GENERIEREN
        // ==========================================

        // --- GRUPPE A: Bereich BS (ca. 65 Fahrzeuge) ---
        $bsTargets = [$ccFD, $ccFP];

        for ($i = 1; $i <= 65; $i++) {
            $isTransit = ($i % 2 == 0);
            $targetCC = $bsTargets[rand(0, 1)];

            Vehicle::create([
                'license_plate' => 'BS-' . $targetCC->code . '-' . (100 + $i),
                'vin' => 'VINBS' . rand(10000, 99999),
                'manufacturer' => $isTransit ? 'Ford' : 'Nissan', // Nissan hat keinen Plan zugewiesen -> Testfall
                'model' => $isTransit ? 'Transit H2/L2' : 'Primastar',
                'first_registration_date' => Carbon::now()->subMonths(rand(6, 60)),
                'fuel_type' => 'diesel',
                'cost_center_id' => $targetCC->id,
                // Nur Transit bekommt einen Plan, Nissan bleibt leer (zum Testen der Warnung)
                'maintenance_template_id' => $isTransit ? $tmpl_Transit->id : null,
                'has_lift' => $isTransit,
                'has_smartfloor' => $isTransit,
                'next_hu_date' => Carbon::now()->addMonths(rand(-2, 20)), // Manche überfällig (-2)
                'documentation_percentage' => rand(80, 100),
                'is_fully_documented' => rand(0, 1)
            ]);
        }

        // --- GRUPPE B: Tochter PD (ca. 50 Fahrzeuge - verkürzt für Speed) ---
        $pdTargets = [$ccPDm1, $ccPDm2, $ccPDm3, $ccSWA1, $ccSWA2];

        for ($j = 1; $j <= 50; $j++) {
            $targetCC = $pdTargets[rand(0, 4)];

            if ($j <= 15) {
                $brand = 'Citroen'; $model = 'C1';
                $tmpl = $tmpl_C1; $fuel = 'petrol';
            } else {
                $brand = 'Opel'; $model = 'Zafira H1/L2';
                $tmpl = $tmpl_Zafira; $fuel = 'diesel';
            }

            Vehicle::create([
                'license_plate' => 'PD-' . $targetCC->code . '-' . (500 + $j),
                'vin' => 'VINPD' . rand(10000, 99999),
                'manufacturer' => $brand,
                'model' => $model,
                'first_registration_date' => Carbon::now()->subMonths(rand(12, 48)),
                'fuel_type' => $fuel,
                'cost_center_id' => $targetCC->id,
                'maintenance_template_id' => $tmpl->id,
                'next_hu_date' => Carbon::now()->addMonths(rand(-1, 24)),
                'documentation_percentage' => rand(60, 100),
            ]);
        }
    }*/
}
