<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('access_duration')->default(12)->after('price');
            $table->string('access_duration_unit', 10)->default('months')->after('access_duration');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('enrolled_at')->index();
        });

        DB::table('enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->select(['enrollments.id', 'enrollments.enrolled_at', 'courses.access_duration', 'courses.access_duration_unit'])
            ->orderBy('enrollments.id')
            ->each(function ($enrollment) {
                $expiresAt = Carbon::parse($enrollment->enrolled_at);
                $expiresAt = $enrollment->access_duration_unit === 'days'
                    ? $expiresAt->addDays($enrollment->access_duration)
                    : $expiresAt->addMonthsNoOverflow($enrollment->access_duration);

                DB::table('enrollments')->where('id', $enrollment->id)->update(['expires_at' => $expiresAt]);
            });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['access_duration', 'access_duration_unit']);
        });
    }
};
