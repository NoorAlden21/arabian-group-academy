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
        Schema::create('exam_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year', 9);
            $table->string('term', 10)->default('other');   // بدل enum
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 10)->default('draft'); // بدل enum
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE exam_terms
                ADD CONSTRAINT exam_terms_term_check
                CHECK (term IN ('midterm','final','other'))");

            DB::statement("ALTER TABLE exam_terms
                ADD CONSTRAINT exam_terms_status_check
                CHECK (status IN ('draft','published','archived'))");
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_terms');
    }
};
