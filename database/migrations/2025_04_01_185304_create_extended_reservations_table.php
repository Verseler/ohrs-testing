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
        Schema::create('extended_reservations', function (Blueprint $table) {
            $table->id();
            $table->date('check_in_date');
            $table->date('old_check_out_date');
            $table->date('new_check_out_date');
            $table->integer('days_extended');
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extended_reservations');
    }
};
