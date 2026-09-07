<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('terms_version', 30)->nullable()->after('payment_method');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_version');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['terms_version', 'terms_accepted_at']);
        });
    }
};
