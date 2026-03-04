<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
 {
     Schema::table('workshop_appointments', function (Blueprint $table) {
         $table->string('transport_method')->nullable()->after('is_transport_organized');
         $table->string('transport_billing_department')->nullable()->after('transport_driver_status');
     });
 }

 public function down()
 {
     Schema::table('workshop_appointments', function (Blueprint $table) {
         $table->dropColumn(['transport_method', 'transport_billing_department']);
     });
 }
};
