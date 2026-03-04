<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Dienstleister Tabelle (Werkstätten, TÜV, etc.)
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('workshop'); // 'workshop', 'inspection', 'other'
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 2. Fahrzeuge bekommen eine Farbe
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('calendar_color')->default('#3B82F6')->after('model'); // Standard Blau
        });

        // 3. Werkstatt-Termine massiv erweitern
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // Alte Datumsspalten ändern zu DateTime (Wir müssen sie droppen und neu machen oder ändern)
            // Um Fehler zu vermeiden, ändern wir sie hier direkt:
            $table->dateTime('start_time')->nullable()->after('start_date');
            $table->dateTime('planned_end_time')->nullable()->after('planned_end_date');
            $table->dateTime('actual_end_time')->nullable()->after('actual_end_date');

            // Verknüpfung zum Dienstleister statt nur Name
            $table->foreignId('service_provider_id')->nullable()->after('vehicle_id')->constrained('service_providers')->nullOnDelete();

            // Logistik / Verbringung
            $table->boolean('is_transport_organized')->default(false)->after('status');
            $table->string('transport_method')->nullable()->after('is_transport_organized'); // 'replacement', 'department', 'driver_service'

            // Details für Fahrdienst (Vorbereitung für spätere Abrechnung)
            $table->string('transport_driver_name')->nullable();
            $table->dateTime('transport_start_time')->nullable();
            $table->dateTime('transport_end_time')->nullable();
            $table->string('transport_location_to')->nullable();
            $table->string('transport_return_method')->nullable(); // Wie kommt Fahrer zurück?
            $table->string('transport_billing_department')->nullable(); // Wer zahlt?
        });

        // Bestehende Daten migrieren (Alte Date -> Neue DateTime)
        DB::statement("UPDATE workshop_appointments SET start_time = CONCAT(start_date, ' 08:00:00') WHERE start_date IS NOT NULL");
        DB::statement("UPDATE workshop_appointments SET planned_end_time = CONCAT(planned_end_date, ' 16:00:00') WHERE planned_end_date IS NOT NULL");
    }

    public function down()
    {
        // Rollback Logik (vereinfacht)
        Schema::dropIfExists('service_providers');
        Schema::table('vehicles', function (Blueprint $table) { $table->dropColumn('calendar_color'); });
        Schema::table('workshop_appointments', function (Blueprint $table) {
            $table->dropColumn([
                'start_time', 'planned_end_time', 'actual_end_time',
                'service_provider_id', 'is_transport_organized', 'transport_method',
                'transport_driver_name', 'transport_start_time', 'transport_end_time',
                'transport_location_to', 'transport_return_method', 'transport_billing_department'
            ]);
        });
    }
};
