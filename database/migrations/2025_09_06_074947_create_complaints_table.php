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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('complainantable');
            $table->nullableMorphs('targetable');

            $table->string('topic', 100)->index();
            $table->text('description');

            $table->string('status', 12)->default('pending')->index(); // بدل enum

            $table->foreignId('handled_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE complaints
                ADD CONSTRAINT complaints_status_check
                CHECK (status IN ('pending','in_review','resolved','rejected'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
