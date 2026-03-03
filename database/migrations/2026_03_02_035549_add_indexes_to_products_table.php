<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('category');
            $table->index(['user_id', 'category']);
            $table->index('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['category']);
            $table->dropIndex(['user_id', 'category']);
            $table->dropIndex(['name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
    }
};
