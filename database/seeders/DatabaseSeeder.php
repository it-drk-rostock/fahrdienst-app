<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Wir rufen NUR unseren aktuellen Haupt-Seeder auf.
        // Falls hier noch andere Zeilen stehen (z.B. OrganizationSeeder), LÖSCHE SIE!
        $this->call([
            FleetProductionSeeder::class,
        ]);
    }
}
