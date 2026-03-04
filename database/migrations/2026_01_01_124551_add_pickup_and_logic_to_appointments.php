<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // Abholung organisieren
            $table->boolean('is_pickup_needed')->default(false)->after('transport_driver_status');
            $table->string('pickup_driver_name')->nullable()->after('is_pickup_needed');
            $table->string('pickup_driver_status')->nullable(); // 'search_needed', 'informed'
        });
    }

    public function down()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            $table->dropColumn(['is_pickup_needed', 'pickup_driver_name', 'pickup_driver_status']);
        });
    }
};
