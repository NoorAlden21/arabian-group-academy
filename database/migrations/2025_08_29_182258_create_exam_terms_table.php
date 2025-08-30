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
        Schema::create('exam_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // مثال: "Midterm 2025/2026"
            $table->string('academic_year', 9);              // "2025/2026"
            $table->enum('term', ['midterm','final','other'])->default('other');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft','published','archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_terms');
    }
};
