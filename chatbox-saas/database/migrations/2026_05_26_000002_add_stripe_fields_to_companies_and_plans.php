<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('expires_at');
            }
            if (! Schema::hasColumn('companies', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            }
            if (! Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status', 32)->nullable()->after('stripe_subscription_id');
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'stripe_price_id')) {
                $table->string('stripe_price_id')->nullable()->after('interval');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id', 'stripe_subscription_id', 'subscription_status']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('stripe_price_id');
        });
    }
};
