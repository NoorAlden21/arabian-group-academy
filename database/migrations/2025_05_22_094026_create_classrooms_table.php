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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_type_id')->nullable()->constrained('class_types')->onDelete('set null');
            $table->string('level')->nullable();
            $table->string('name');     // e.g. "9A"
            $table->string('year', 4);     // e.g. "2024"
            $table->smallInteger('students_count')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['class_type_id']);
            $table->unique(['name', 'year', 'class_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
