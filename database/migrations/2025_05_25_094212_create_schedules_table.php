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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_subject_teacher_id')->nullable()->constrained('class_subject_teachers')->onDelete('set null');
            $table->enum('day',['saturday','sunday','monday','tuesday','wednesday','thursday','friday']);
            $table->unsignedTinyInteger('period');
            $table->time('start_time');
            $table->time('end_time');
            //$table->string('room'); (optional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
