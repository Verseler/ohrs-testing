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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->double('current_balance');
            $table->double('total_amount');
            $table->enum('status', allowed: ['pending', 'canceled', 'checked_in', 'checked_out'])->default('pending');
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('guest_office_id')->constrained('offices')->cascadeOnDelete();#
            //Employee ID image
            $table->string('employee_identification');
            $table->text('purpose_of_stay')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
