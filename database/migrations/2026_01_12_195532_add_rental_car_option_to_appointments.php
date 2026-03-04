<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            // Wir prüfen, ob die Spalte 'has_rental_car' noch fehlt
            if (!Schema::hasColumn('workshop_appointments', 'has_rental_car')) {
                // Wir versuchen, sie logisch passend einzufügen
                $after = Schema::hasColumn('workshop_appointments', 'transport_method')
                         ? 'transport_method'
                         : 'is_transport_organized';

                $table->boolean('has_rental_car')->default(false)->after($after);
            }
        });
    }

    public function down()
    {
        Schema::table('workshop_appointments', function (Blueprint $table) {
            if (Schema::hasColumn('workshop_appointments', 'has_rental_car')) {
                $table->dropColumn('has_rental_car');
            }
        });
    }
};
