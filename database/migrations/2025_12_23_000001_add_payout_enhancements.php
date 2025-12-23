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
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_gateways', 'payouts_enabled')) {
                $table->boolean('payouts_enabled')->default(false)->after('is_active');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'payout_hold_enabled')) {
                $table->boolean('payout_hold_enabled')->default(true)->after('payouts_enabled');
            }
            if (! Schema::hasColumn('users', 'payout_hold_minutes')) {
                $table->unsignedInteger('payout_hold_minutes')->default(60)->after('payout_hold_enabled');
            }
            if (! Schema::hasColumn('users', 'payout_max_active_payouts')) {
                $table->unsignedInteger('payout_max_active_payouts')->default(1)->after('payout_hold_minutes');
            }
        });

        Schema::table('payouts', function (Blueprint $table) {
            if (! Schema::hasColumn('payouts', 'requisite_type')) {
                $table->string('requisite_type')->nullable()->after('detail_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (Schema::hasColumn('payment_gateways', 'payouts_enabled')) {
                $table->dropColumn('payouts_enabled');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'payout_hold_enabled')) {
                $table->dropColumn('payout_hold_enabled');
            }
            if (Schema::hasColumn('users', 'payout_hold_minutes')) {
                $table->dropColumn('payout_hold_minutes');
            }
            if (Schema::hasColumn('users', 'payout_max_active_payouts')) {
                $table->dropColumn('payout_max_active_payouts');
            }
        });

        Schema::table('payouts', function (Blueprint $table) {
            if (Schema::hasColumn('payouts', 'requisite_type')) {
                $table->dropColumn('requisite_type');
            }
        });
    }
};

