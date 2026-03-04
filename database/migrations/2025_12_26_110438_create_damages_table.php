<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damages', function (Blueprint $table) {
            $table->id();

            // 1. Zuordnung
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('hu_report_id')->nullable()->constrained('hu_reports')->onDelete('set null');

            // 2. Deine Felder (Original)
            $table->string('reporter_name')->nullable();
            $table->string('source')->default('Fahrer');
            $table->text('description');
            $table->json('images')->nullable();

            // 3. Ergänzungen für das Dashboard (Wichtig für die Optik!)
            $table->string('title'); // Kurze Überschrift
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low'); // Ampel-Farbe

            // 4. Status & Kosten (Deine Felder)
            $table->enum('status', ['open', 'deferred', 'in_repair', 'resolved'])->default('open');
            $table->decimal('repair_cost', 10, 2)->nullable();
            $table->date('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
