<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Neue Tabelle für Werkstatt-Termine
        Schema::create('workshop_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('workshop_name');
            $table->date('start_date');       // Verbringung
            $table->date('planned_end_date'); // Geplantes Ende
            $table->date('actual_end_date')->nullable();
            $table->string('status')->default('planned'); // planned, active, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Mängel-Tabelle erweitern
        Schema::table('damages', function (Blueprint $table) {
            $table->foreignId('workshop_appointment_id')
                  ->nullable()
                  ->constrained('workshop_appointments')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('damages', function (Blueprint $table) {
            $table->dropForeign(['workshop_appointment_id']);
            $table->dropColumn('workshop_appointment_id');
        });
        Schema::dropIfExists('workshop_appointments');
    }
};
