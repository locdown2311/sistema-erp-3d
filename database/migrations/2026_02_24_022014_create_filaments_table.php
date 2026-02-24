<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Ex: PLA Branco, ABS Preto
            $table->string('type');              // PLA, ABS, PETG, TPU, Resina
            $table->string('color')->nullable(); // Cor do filamento
            $table->string('brand')->nullable(); // Marca
            $table->decimal('price_per_kg', 10, 2)->default(0);
            $table->decimal('weight_grams', 10, 2)->default(1000); // Peso total do rolo
            $table->decimal('remaining_grams', 10, 2)->default(1000); // Quanto resta
            $table->decimal('diameter_mm', 5, 2)->default(1.75); // 1.75 ou 2.85
            $table->integer('print_temp_min')->nullable(); // Temp mín impressão
            $table->integer('print_temp_max')->nullable(); // Temp máx impressão
            $table->integer('bed_temp_min')->nullable();
            $table->integer('bed_temp_max')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filaments');
    }
};
