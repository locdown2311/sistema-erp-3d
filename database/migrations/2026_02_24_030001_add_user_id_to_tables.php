<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['products', 'filaments', 'sales', 'stock_movements', 'tasks', 'print_costs', 'settings'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['products', 'filaments', 'sales', 'stock_movements', 'tasks', 'print_costs', 'settings'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName . '_user_id_foreign']);
                $table->dropColumn('user_id');
            });
        }
    }
};
