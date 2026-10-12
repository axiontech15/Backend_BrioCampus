<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::create('palmares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('university_id')
                ->constrained('universities')
                ->cascadeOnDelete();

            $table->string('file');

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('palmares');
    }
};