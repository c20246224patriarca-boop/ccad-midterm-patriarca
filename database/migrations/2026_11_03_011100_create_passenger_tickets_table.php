<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passenger_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('terminal_trip_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('passenger_name');
            $table->string('passenger_email')->nullable();

            $table->unsignedInteger('seat_number');

            $table->decimal('ticket_price', 10, 2);

            $table->enum('status', [
                'booked',
                'cancelled'
            ])->default('booked');

            $table->timestamps();

            $table->unique([
                'terminal_trip_id',
                'seat_number'
            ]);

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passenger_tickets');
    }
};