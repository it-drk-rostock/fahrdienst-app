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
         Schema::table('areas', function (Blueprint $table) {
             $table->string('telephone')->nullable()->after('manager_email');
         });

         Schema::table('cost_centers', function (Blueprint $table) {
             $table->string('telephone')->nullable()->after('contact_email');
         });
     }

     public function down()
     {
         Schema::table('areas', function (Blueprint $table) {
             $table->dropColumn('telephone');
         });
         Schema::table('cost_centers', function (Blueprint $table) {
             $table->dropColumn('telephone');
         });
     }
};
