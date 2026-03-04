<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// WICHTIG: Das Wort 'return' muss hier stehen!
return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // Prüfung, ob Spalte schon da ist
            if (!Schema::hasColumn('workshop_appointments', 'pickup_method')) {
                // Einfügen nach 'is_pickup_needed'
                $table->string('pickup_method')->nullable()->after('is_pickup_needed');
            }
        });
    }

    public function down()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            if (Schema::hasColumn('workshop_appointments', 'pickup_method')) {
                $table->dropColumn('pickup_method');
            }
        });
    }
};
