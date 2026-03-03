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
        if (!Schema::hasColumn('plans', 'mp_plan_id')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->string('mp_plan_id')->nullable()->after('is_active');
            });
        }
        
        if (!Schema::hasColumn('subscriptions', 'mp_subscription_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('mp_subscription_id')->nullable()->after('ends_at');
                $table->string('mp_status')->nullable()->after('mp_subscription_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
