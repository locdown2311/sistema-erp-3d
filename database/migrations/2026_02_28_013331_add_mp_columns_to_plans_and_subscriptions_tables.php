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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('mp_plan_id')->nullable()->after('slug')->comment('ID do plano no Mercado Pago');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('mp_subscription_id')->nullable()->after('plan_id')->comment('ID da assinatura (preapproval) no Mercado Pago');
            $table->string('mp_status')->nullable()->after('status')->comment('Status refletido direto do MP');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('mp_plan_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['mp_subscription_id', 'mp_status']);
        });
    }
};
