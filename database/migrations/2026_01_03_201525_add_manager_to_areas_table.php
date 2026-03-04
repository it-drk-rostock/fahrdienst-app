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
           // Wir fügen die neuen Spalten hinzu (nullable, falls kein Manager da ist)
           $table->string('manager_name')->nullable()->after('name');
           $table->string('manager_email')->nullable()->after('manager_name');
       });
   }

   public function down()
   {
       Schema::table('areas', function (Blueprint $table) {
           // Falls wir zurückrollen (rollback), löschen wir sie wieder
           $table->dropColumn(['manager_name', 'manager_email']);
       });
   }
};
