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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            // المُشتكي: طالب/معلّم عبر بروفايل
            $table->nullableMorphs('complainantable'); // complainantable_type, complainantable_id

            // المُشتكى عليه: طالب/معلّم عبر بروفايل
            $table->nullableMorphs('targetable');      // targetable_type, targetable_id
            $table->string('topic', 100)->index();
            $table->text('description');
            $table->enum('status', ['pending', 'in_review', 'resolved', 'rejected'])
                ->default('pending')
                ->index();

            // the admin who handled it
            $table->foreignId('handled_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
