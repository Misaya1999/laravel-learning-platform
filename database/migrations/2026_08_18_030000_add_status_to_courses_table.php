<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->index()->after('access_duration_unit');
        });

        DB::table('courses')->update(['status' => 'published']);
    }

    public function down(): void
    {
        Schema::table('courses', fn (Blueprint $table) => $table->dropColumn('status'));
    }
};
