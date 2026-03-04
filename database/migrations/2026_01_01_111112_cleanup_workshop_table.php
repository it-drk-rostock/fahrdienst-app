<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // Wir entfernen die alten "Nur-Datum"-Spalten, da wir jetzt DateTime haben
            $table->dropColumn(['start_date', 'planned_end_date', 'actual_end_date']);
        });
    }

    public function down()
    {
        // Zurück-Logik (falls nötig)
        Schema::table('workshop_appointments', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
        });
    }
};
