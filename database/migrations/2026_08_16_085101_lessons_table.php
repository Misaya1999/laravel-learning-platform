<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_section_id')
                ->constrained('course_sections')
                ->cascadeOnDelete();

            $table->string('title');

            $table->enum('type', [
                'video',
                'article',
                'assignment',
            ])->default('video');

            $table->string('video_url')->nullable();
            $table->longText('content')->nullable();
            $table->string('attachment')->nullable();

            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_preview')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};