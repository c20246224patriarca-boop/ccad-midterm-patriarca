<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('passenger_tickets'));
        $indexNames = $indexes->pluck('name')->all();
        $hasCompositeIndex = $indexes->contains(
            fn (array $index): bool => $index['columns'] === ['terminal_trip_id', 'status', 'seat_number']
        );

        if (in_array('passenger_tickets_terminal_trip_id_seat_number_unique', $indexNames, true)) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->dropUnique('passenger_tickets_terminal_trip_id_seat_number_unique');
            });
        }

        if (in_array('passenger_tickets_status_index', $indexNames, true)) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->dropIndex('passenger_tickets_status_index');
            });
        }

        if (Schema::hasColumn('passenger_tickets', 'ticket_price')) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->dropColumn('ticket_price');
            });
        }

        if (! $hasCompositeIndex) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->index(
                    ['terminal_trip_id', 'status', 'seat_number'],
                    'passenger_tickets_trip_status_seat_index'
                );
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('passenger_tickets'));
        $compositeIndexes = $indexes->filter(
            fn (array $index): bool => $index['columns'] === ['terminal_trip_id', 'status', 'seat_number']
        );

        foreach ($compositeIndexes as $index) {
            Schema::table('passenger_tickets', function (Blueprint $table) use ($index): void {
                $table->dropIndex($index['name']);
            });
        }

        if (! Schema::hasColumn('passenger_tickets', 'ticket_price')) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->decimal('ticket_price', 10, 2)->after('seat_number');
            });
        }

        $indexes = collect(Schema::getIndexes('passenger_tickets'));
        $hasSeatUnique = $indexes->contains(
            fn (array $index): bool => $index['unique'] && $index['columns'] === ['terminal_trip_id', 'seat_number']
        );

        if (! $hasSeatUnique) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->unique(['terminal_trip_id', 'seat_number']);
            });
        }

        $indexes = collect(Schema::getIndexes('passenger_tickets'));
        $hasStatusIndex = $indexes->contains(
            fn (array $index): bool => ! $index['unique'] && $index['columns'] === ['status']
        );

        if (! $hasStatusIndex) {
            Schema::table('passenger_tickets', function (Blueprint $table): void {
                $table->index('status');
            });
        }
    }
};
