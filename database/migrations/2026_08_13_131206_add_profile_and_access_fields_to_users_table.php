<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)
                ->nullable()
                ->unique()
                ->after('password');

            $table->string('avatar')
                ->nullable()
                ->after('phone');

            $table->enum('role', ['admin', 'user'])
                ->default('user')
                ->index()
                ->after('avatar');

            $table->enum('status', ['active', 'inactive', 'blocked'])
                ->default('active')
                ->index()
                ->after('role');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'role',
                'status',
                'last_login_at',
            ]);
        });
    }
};