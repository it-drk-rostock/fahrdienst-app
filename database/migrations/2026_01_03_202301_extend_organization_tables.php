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
     // 1. Bereiche (Areas): Buchungskreis hinzufügen
     Schema::table('areas', function (Blueprint $table) {
         $table->string('company_code')->nullable()->after('name');
     });

     // 2. Kostenstellen: Ansprechpartner hinzufügen
     Schema::table('cost_centers', function (Blueprint $table) {
         $table->string('contact_name')->nullable()->after('code');
         $table->string('contact_email')->nullable()->after('contact_name');
     });
 }

 public function down()
 {
     Schema::table('areas', function (Blueprint $table) {
         $table->dropColumn('company_code');
     });
     Schema::table('cost_centers', function (Blueprint $table) {
         $table->dropColumn(['contact_name', 'contact_email']);
     });
 }
};
