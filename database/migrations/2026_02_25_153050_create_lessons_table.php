<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url');
            $table->boolean('is_preview')->default(false)->index();
            $table->boolean('is_required')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_id', 'position']);
            $table->index(['course_id', 'is_required']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
