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
            $table->string('code')->unique()->after('id');
            $table->double('total_billings');
            $table->double('remaining_balance');
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->string('phone');
            $table->string('email');
            $table->foreignId('hostel_office_id')->constrained('offices')->cascadeOnDelete();
            $table->enum('payment_type', ['full_payment', 'pay_later']);
            $table->string('id_type');
            $table->string('employee_id');
            $table->text('purpose_of_stay');
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
