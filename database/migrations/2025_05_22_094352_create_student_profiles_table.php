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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('parent_profiles')->onDelete('set null');
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->string('level');
            $table->string('previous_status', 30)->nullable(); // بدل enum
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('enrollment_year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE student_profiles
                ADD CONSTRAINT student_profiles_previous_status_check
                CHECK (
                    previous_status IS NULL OR
                    previous_status IN ('11th','qualifying','bacaloria_repeater')
                )");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
