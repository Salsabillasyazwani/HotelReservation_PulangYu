<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {

            $table->id();

            $table->foreignId('room_type_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->string('room_number')->unique();
            $table->string('room_name');

            $table->integer('floor');

            $table->decimal('price',10,2);

            $table->integer('capacity');

            $table->enum('status',[
                'Available',
                'Occupied',
                'Booked',
                'Maintenance'
            ])->default('Available');

            $table->string('image')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
