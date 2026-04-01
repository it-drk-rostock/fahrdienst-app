
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
        Schema::table('areas', function (Blueprint $table) {
            // Fügt die parent_id Spalte direkt hinter der ID ein
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');

            // Setzt den Foreign Key auf die eigene Tabelle (Self-Referencing)
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('areas')
                  ->onDelete('set null');
                  // set null bedeutet: Löschst du den Hauptbereich,
                  // werden die Unterbereiche nicht gelöscht, sondern rutschen einfach eine Ebene hoch.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            // Wichtig: Erst den Foreign Key droppen, dann die Spalte
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
