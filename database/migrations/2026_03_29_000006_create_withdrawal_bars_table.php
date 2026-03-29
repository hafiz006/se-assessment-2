<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_bars', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('withdrawal_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('bar_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_bars');
    }
};
