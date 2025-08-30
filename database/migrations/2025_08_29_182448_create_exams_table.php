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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_type_id')->constrained('class_types')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->enum('status', ['draft','published','done','cancelled'])->default('draft');
            $table->text('notes')->nullable();

            $table->dateTime('published_at')->nullable();
            $table->dateTime('results_published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exam_term_id','class_type_id','subject_id']);
            $table->index(['class_type_id','scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
