<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rebook_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prev_reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('new_reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rebook_reservations');
    }
};
