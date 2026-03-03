<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('max_sales_per_month')->nullable()->after('max_products'); // null = ilimitado
            $table->integer('max_wishlists')->nullable()->after('max_sales_per_month'); // null = ilimitado
            $table->boolean('can_export_reports')->default(false)->after('features');
            $table->boolean('can_use_nfe')->default(false)->after('can_export_reports');
            $table->boolean('priority_store')->default(false)->after('can_use_nfe');
            $table->boolean('can_customize_store')->default(false)->after('priority_store');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_sales_per_month',
                'max_wishlists',
                'can_export_reports',
                'can_use_nfe',
                'priority_store',
                'can_customize_store',
            ]);
        });
    }
};
