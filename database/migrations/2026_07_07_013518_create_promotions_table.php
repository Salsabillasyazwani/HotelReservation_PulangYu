<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            $table->string('promo_code')->unique();
            $table->string('promo_name');
            $table->text('description')->nullable();

            $table->enum('discount_type', ['Percentage', 'Voucher', 'Fixed Amount']);
            $table->decimal('discount_value', 12, 2);
            $table->decimal('minimum_booking', 12, 2)->default(0);
            $table->decimal('maximum_discount', 12, 2)->nullable();

            $table->string('banner')->nullable();
            $table->json('rooms')->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->unsignedInteger('quota')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
