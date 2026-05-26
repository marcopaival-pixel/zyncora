<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'expiry_warning_sent_at')) {
                $table->timestamp('expiry_warning_sent_at')->nullable()->after('mercadopago_preapproval_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('expiry_warning_sent_at');
        });
    }
};
