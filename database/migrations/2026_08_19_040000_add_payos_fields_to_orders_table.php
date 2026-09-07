<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payos_order_code')->nullable()->unique()->after('payment_method');
            $table->string('payment_link_id')->nullable()->after('payos_order_code');
            $table->text('checkout_url')->nullable()->after('payment_link_id');
            $table->text('qr_code')->nullable()->after('checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payos_order_code', 'payment_link_id', 'checkout_url', 'qr_code']);
        });
    }
};
