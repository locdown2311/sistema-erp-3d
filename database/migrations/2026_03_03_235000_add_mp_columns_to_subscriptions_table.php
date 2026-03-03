<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'mp_payment_id')) {
                $table->string('mp_payment_id')->nullable()->after('ends_at');
            }
            if (!Schema::hasColumn('subscriptions', 'mp_status')) {
                $table->string('mp_status')->nullable()->after('mp_payment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['mp_payment_id', 'mp_status']);
        });
    }
};
