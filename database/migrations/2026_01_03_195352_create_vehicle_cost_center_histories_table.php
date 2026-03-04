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
     Schema::create('vehicle_cost_center_histories', function (Blueprint $table) {
         $table->id();
         // Verknüpfung zum Fahrzeug
         $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');

         // Verknüpfung zur Kostenstelle (nullable, falls mal keine zugewiesen ist)
         $table->foreignId('cost_center_id')->nullable()->constrained()->onDelete('set null');

         // Zeitraum
         $table->date('assigned_from'); // Ab wann
         $table->date('assigned_until')->nullable(); // Bis wann (null = aktuell aktiv)

         $table->timestamps();
     });
 }
};
