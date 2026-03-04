<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('workshop_appointments', function (Blueprint $table) {
        // Prüfen, ob 'transport_method' fehlt, bevor wir sie anlegen
        if (!Schema::hasColumn('workshop_appointments', 'transport_method')) {
            $table->string('transport_method')->nullable()->after('is_transport_organized');
        }

        // Prüfen, ob 'transport_billing_department' fehlt
        if (!Schema::hasColumn('workshop_appointments', 'transport_billing_department')) {
            $table->string('transport_billing_department')->nullable()->after('transport_driver_status');
        }
    });
}

    public function down()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            $table->dropColumn(['transport_method', 'transport_billing_department']);
        });
    }
};
