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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();

            $table->morphs('gradable');

            $table->decimal('score', 6, 2)->nullable();
            $table->decimal('max_score', 6, 2)->default(100);

            $table->string('status', 12)->default('present'); // بدل enum
            $table->string('remark')->nullable();

            $table->timestamps();

            $table->unique(['student_profile_id', 'gradable_type', 'gradable_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE grades
                ADD CONSTRAINT grades_status_check
                CHECK (status IN ('present','absent','excused','cheated','incomplete'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
