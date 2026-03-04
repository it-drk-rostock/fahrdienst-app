<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ZUERST DIE TEMPLATES (ELTERN)
        Schema::create('maintenance_templates', function (Blueprint $table) {
    $table->id();
    // Diese Felder erwartet dein Seeder:
    $table->string('manufacturer')->nullable();
    $table->string('model_series')->nullable();
    $table->integer('interval_km')->nullable();
    $table->integer('interval_months')->nullable();
    $table->integer('warranty_months')->nullable();
    $table->boolean('is_confirmed')->default(false);

    // Name als Fallback (falls du ihn brauchst)
    $table->string('name')->nullable();
    $table->text('description')->nullable();
    $table->timestamps();
});

        // 2. DANACH DIE ITEMS (KINDER)
        Schema::create('maintenance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_template_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->integer('interval_km')->nullable();
            $table->integer('interval_months')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_items');
        Schema::dropIfExists('maintenance_templates');
    }
};
