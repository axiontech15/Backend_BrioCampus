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
        Schema::create('payments', function (Blueprint $table) {
           $table->id();

           $table->foreignId('user_id')
               ->constrained()
               ->cascadeOnDelete();

           $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->decimal('amount', 10, 2);

            $table->string('reference')->unique();

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'cancelled'
            ])->default('pending');

            $table->timestamp('payment_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};