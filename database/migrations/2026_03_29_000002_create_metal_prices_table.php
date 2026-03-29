<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('metal_type', ['gold', 'silver', 'platinum']);
            $table->decimal('price_per_kg', 18, 4);
            $table->date('effective_date');
            $table->timestamps();

            $table->unique(['metal_type', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_prices');
    }
};
