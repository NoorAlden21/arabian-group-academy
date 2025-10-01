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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_subject_teacher_id')->nullable()->constrained('class_subject_teachers')->onDelete('set null');
            $table->string('day', 10); // بدل enum
            $table->unsignedTinyInteger('period');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE schedules
                ADD CONSTRAINT schedules_day_check
                CHECK (
                    day IN ('saturday','sunday','monday','tuesday','wednesday','thursday','friday')
                )");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
