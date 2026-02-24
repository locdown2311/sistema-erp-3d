<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('store_name')->after('slug');
            $table->string('store_logo')->nullable()->after('store_name');
            $table->string('whatsapp', 20)->nullable()->after('store_logo');
            $table->text('store_description')->nullable()->after('whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['slug', 'store_name', 'store_logo', 'whatsapp', 'store_description']);
        });
    }
};
