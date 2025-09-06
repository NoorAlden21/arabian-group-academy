<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();
            $table->string('academic_year', 16);
            $table->decimal('rating', 3, 1);
            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(
                ['student_profile_id', 'teacher_profile_id', 'academic_year'],
                'uq_studentprofile_teacherprofile_year'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_ratings');
    }
};
