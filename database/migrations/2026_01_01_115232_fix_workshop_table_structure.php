<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {

            // 1. Alte Spalten löschen (falls sie noch da sind und Fehler verursachen)
            if (Schema::hasColumn('workshop_appointments', 'start_date')) {
                $table->dropColumn(['start_date', 'planned_end_date', 'actual_end_date']);
            }

            // 2. Neue Spalten hinzufügen (falls sie noch fehlen)
            if (!Schema::hasColumn('workshop_appointments', 'transport_driver_status')) {
                $table->string('transport_driver_status')->nullable()->after('transport_driver_name');
            }

            if (!Schema::hasColumn('workshop_appointments', 'services')) {
                $table->json('services')->nullable()->after('workshop_name');
            }

            // Sicherstellen, dass Status ein String ist (kein ENUM, das macht oft Probleme)
            // Hinweis: change() braucht das Paket doctrine/dbal. Falls nicht installiert, ignorieren wir das hier.
            // Stattdessen nutzen wir raw SQL für maximale Kompatibilität:
        });

        // Status Spalte auf VARCHAR ändern (falls es noch ENUM ist)
        DB::statement("ALTER TABLE workshop_appointments MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'planned'");
    }

    public function down()
    {
        // Keine Umkehrung nötig, das ist ein Fix.
    }
};
