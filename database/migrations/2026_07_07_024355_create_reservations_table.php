<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {

            $table->id();

            $table->string('reservation_code')->unique();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnUpdate();

            $table->foreignId('promotion_id')
                ->nullable()
                ->constrained('promotions')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('guest_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('identity_number');
            $table->string('nationality')->default('Indonesia');
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('nights')->default(1);
            $table->unsignedInteger('guests')->default(1);

            $table->enum('reservation_status', [
                'Pending',
                'Confirmed',
                'Checked In',
                'Checked Out',
                'Cancelled'
            ])->default('Pending');

            $table->string('payment_method')->nullable();

            $table->enum('payment_status', [
                'Paid',
                'Unpaid',
                'Partial',
                'Refunded'
            ])->default('Unpaid');

            $table->decimal('price_per_night', 12, 2)->default(0);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('additional_charges', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('special_request')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('actual_check_in')->nullable();
            $table->dateTime('actual_check_out')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
