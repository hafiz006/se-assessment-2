<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('deposit_number')->unique();
            $table->foreignUlid('account_id')->constrained()->cascadeOnDelete();
            $table->enum('metal_type', ['gold', 'silver', 'platinum']);
            $table->enum('storage_type', ['allocated', 'unallocated']);
            $table->decimal('quantity_kg', 12, 6);
            $table->enum('status', ['pending', 'confirmed', 'withdrawn'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
