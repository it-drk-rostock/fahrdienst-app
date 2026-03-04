<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::table('damages', function (Blueprint $table) {
        // Art des Schadens: 'found' (Vorfinde), 'accident_own' (Vollkasko/Eigen), 'accident_other' (Fremd), 'wear' (Verschleiß/Sonstiges)
        $table->string('damage_type')->default('wear')->after('status');

        // Abrechnung: true = Versicherung zahlt, false = Selbstzahler/Unter SB
        $table->boolean('insurance_cover')->default(false)->after('damage_type');
    });
}

public function down()
{
    Schema::table('damages', function (Blueprint $table) {
        $table->dropColumn(['damage_type', 'insurance_cover']);
    });
}
};
