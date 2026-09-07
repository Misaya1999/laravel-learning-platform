<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentors', function (Blueprint $table) {
            $table->string('specialty')->nullable()->after('email');
            $table->text('philosophy')->nullable()->after('bio');
            $table->text('credentials')->nullable()->after('philosophy');
            $table->boolean('is_main')->default(false)->after('credentials')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mentors', function (Blueprint $table) {
            $table->dropIndex(['is_main']);
            $table->dropColumn(['specialty', 'philosophy', 'credentials', 'is_main']);
        });
    }
};
