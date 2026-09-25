<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temperature_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location');
            $table->decimal('temperature', 5, 2);
            $table->decimal('minimum_temperature', 5, 2)->nullable();
            $table->decimal('maximum_temperature', 5, 2)->nullable();
            $table->string('source')->default('manual');
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['location', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temperature_readings');
    }
};