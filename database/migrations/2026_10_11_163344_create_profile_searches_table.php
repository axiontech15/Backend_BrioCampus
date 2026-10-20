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
        Schema::create('profile_searches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rh_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('graduate_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('university_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('ticket_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_searches');
    }
};