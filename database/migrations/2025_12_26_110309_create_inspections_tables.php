<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Manager Audit (Fahrzeugschau, Reifen & KM)
        Schema::create('manager_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');

            // Wann und KM
            $table->date('checked_at');
            $table->integer('mileage');

            // Reifendruck (Bar) - passend zum neuen Controller
            $table->float('tire_pressure_front_left')->nullable();
            $table->float('tire_pressure_front_right')->nullable();
            $table->float('tire_pressure_rear_left')->nullable();
            $table->float('tire_pressure_rear_right')->nullable();

            // Profiltiefe (mm) - passend zum neuen Controller
            $table->float('tire_tread_front_left')->nullable();
            $table->float('tire_tread_front_right')->nullable();
            $table->float('tire_tread_rear_left')->nullable();
            $table->float('tire_tread_rear_right')->nullable();

            // Bemerkungen
            $table->text('notes')->nullable();

            // Optional: Wenn du Benutzer tracken willst
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->timestamps();
        });

        // 2. HU Berichte (Hauptuntersuchung)
        Schema::create('hu_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->date('inspection_date');

            // Ergebnis-Flag
            $table->enum('result', ['pass', 'note', 'minor', 'major', 'unsafe']); // 'unsafe' hinzugefügt für Controller

            $table->string('report_number')->nullable();
            $table->string('organization')->nullable();

            $table->decimal('fees', 8, 2)->nullable(); // Gebührenfeld fehlte vorher
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hu_reports');
        Schema::dropIfExists('manager_audits');
    }
};
