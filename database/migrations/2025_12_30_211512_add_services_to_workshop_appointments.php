<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // Speichert Zusatzleistungen als JSON, z.B. ["HU", "UVV", "Inspektion"]
            $table->json('services')->nullable()->after('workshop_name');
        });
    }

    public function down()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            $table->dropColumn('services');
        });
    }
};
