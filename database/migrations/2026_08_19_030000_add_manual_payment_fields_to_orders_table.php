<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('transaction_id');
            $table->timestamp('receipt_submitted_at')->nullable()->after('receipt_path')->index();
            $table->foreignId('confirmed_by')->nullable()->after('receipt_submitted_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['receipt_path', 'receipt_submitted_at', 'confirmed_by']);
        });
    }
};
