<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rolle des Benutzers (Admin, Fuhrparkleiter, Fahrer)
            $table->string('role')->default('user');

            // KORREKTUR: Verknüpfung zur Area (Tochtergesellschaft) statt Company
            // Falls der User nur für einen bestimmten Bereich zuständig ist
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn(['role', 'area_id']);
        });
    }
};
