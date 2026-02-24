<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('store_color_primary', 7)->default('#6366f1')->after('store_description');
            $table->string('store_color_accent', 7)->default('#06b6d4')->after('store_color_primary');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['store_color_primary', 'store_color_accent']);
        });
    }
};
