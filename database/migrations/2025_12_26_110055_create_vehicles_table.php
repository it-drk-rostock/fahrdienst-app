<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // 1. Basisdaten
            $table->string('license_plate')->unique();
            $table->string('vin')->nullable();
            $table->string('manufacturer');
            $table->string('model');
            $table->string('fuel_type')->nullable();
            
            // 2. Relationen
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('maintenance_template_id')->nullable()->constrained()->nullOnDelete();

            // 3. Termine
            $table->date('first_registration_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->date('battery_warranty_expiry_date')->nullable(); // Für E-Autos

            $table->date('next_hu_date')->nullable();
            $table->date('next_uvv_date')->nullable();
            $table->date('next_bokraft_date')->nullable();

            $table->date('next_lift_uvv_date')->nullable();
            $table->date('next_chair_uvv_date')->nullable();
            $table->date('next_cable_check_date')->nullable();      // Für E-Autos
            $table->date('next_home_cable_check_date')->nullable(); // Für E-Autos (Schuko)

            // 4. Ausstattung & Status (Booleans)
            $table->boolean('has_lift')->default(false);
            $table->boolean('has_chair')->default(false);
            $table->boolean('has_smartfloor')->default(false);
            $table->boolean('is_electric')->default(false);
            $table->boolean('has_home_cable')->default(false);

            // 5. BOKraft & Nutzung
            $table->boolean('is_bokraft')->default(false);
            $table->string('concession_number')->nullable();
            $table->string('private_use_scope')->nullable(); // <--- DAS HAT GEFEHLT

            // 6. System
            $table->boolean('is_fully_documented')->default(false);
            $table->integer('documentation_percentage')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
