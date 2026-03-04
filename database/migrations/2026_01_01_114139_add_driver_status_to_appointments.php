<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // 'search_needed' (Fahrer suchen), 'informed' (Fahrer informiert/gefunden)
            $table->string('transport_driver_status')->nullable()->after('transport_driver_name');
        });
    }

    public function down()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            $table->dropColumn('transport_driver_status');
        });
    }
};
