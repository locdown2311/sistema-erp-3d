<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->decimal('filament_weight_g', 8, 2)->default(0);
            $table->decimal('filament_price_kg', 10, 2)->default(0);
            $table->decimal('filament_cost', 10, 2)->default(0);
            $table->decimal('print_time_hours', 8, 2)->default(0);
            $table->decimal('printer_wattage', 8, 2)->default(0);
            $table->decimal('kwh_rate', 10, 4)->default(0);
            $table->decimal('energy_cost', 10, 2)->default(0);
            $table->decimal('printer_price', 10, 2)->default(0);
            $table->decimal('printer_lifespan_hours', 10, 2)->default(0);
            $table->decimal('depreciation_cost', 10, 2)->default(0);
            $table->decimal('post_processing_hours', 8, 2)->default(0);
            $table->decimal('labor_rate', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('margin_percent', 5, 2)->default(0);
            $table->decimal('suggested_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_costs');
    }
};
