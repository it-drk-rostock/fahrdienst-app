<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Zuerst die Obergesellschaften (Areas) anlegen
        // Damit die Kostenstellen darauf verweisen können.
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // z.B. "Hauptgesellschaft", "Tochter PD"
            $table->timestamps();
        });

        // 2. Dann die Kostenstellen anlegen
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            // Verknüpfung zur Area
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        // Löschen in umgekehrter Reihenfolge (wegen Fremdschlüsseln)
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('areas');
    }
};
