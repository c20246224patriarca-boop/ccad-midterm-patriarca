<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminal_trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bus_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('driver_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('route_origin');
            $table->string('route_destination');

            $table->dateTime('departure_time');

            $table->decimal('ticket_price', 10, 2);

            $table->enum('trip_status', [
                'scheduled',
                'departed',
                'completed',
                'cancelled'
            ])->default('scheduled');

            $table->timestamps();

            $table->index('departure_time');
            $table->index('trip_status');
            $table->index(['bus_id', 'departure_time']);
            $table->index(['driver_id', 'departure_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_trips');
    }
};