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
        Schema::create('student_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->enum('period',[1,2,3,4,5,6,7]);
            $table->dateTime('absent_at');
            $table->date('absent_date')->storedAs('DATE(absent_at)');
            $table->enum('status', ['absent', 'late'])->default('absent');
            $table->timestamps();
            $table->unique(['student_profile_id', 'absent_date', 'period'], 'uniq_profile_date_period');

            $table->index(['student_profile_id','absent_date', 'period']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_absences');
    }
};
