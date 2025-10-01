<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

            $table->unsignedTinyInteger('period'); // بدل enum أرقام
            $table->dateTime('absent_at');
            $table->date('absent_date')->storedAs('DATE(absent_at)');

            $table->string('status', 10)->default('absent'); // بدل enum
            $table->timestamps();

            $table->unique(['student_profile_id', 'absent_date', 'period'], 'uniq_profile_date_period');

            $table->index(['student_profile_id', 'absent_date', 'period']);
            $table->index('status');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE student_absences
                ADD CONSTRAINT student_absences_period_check
                CHECK (period BETWEEN 1 AND 7)");

            DB::statement("ALTER TABLE student_absences
                ADD CONSTRAINT student_absences_status_check
                CHECK (status IN ('absent','late'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_absences');
    }
};
