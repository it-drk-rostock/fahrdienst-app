<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Wir ändern die Spalte 'status' zu einem normalen String (VARCHAR 50)
        // Damit sind Werte wie 'commissioned', 'in_repair' etc. erlaubt.
        DB::statement("ALTER TABLE damages MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'open'");
    }

    public function down()
    {
        // Optional: Zurück zum alten Zustand (falls nötig, hier musst du wissen welche Werte vorher erlaubt waren)
        // DB::statement("ALTER TABLE damages MODIFY COLUMN status ENUM('open', 'resolved', 'deferred') NOT NULL DEFAULT 'open'");
    }
};
