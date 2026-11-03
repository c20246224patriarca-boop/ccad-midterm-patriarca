<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bus_company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('bus_plate')->unique();
            $table->unsignedInteger('total_seats');
            $table->boolean('is_inspected')->default(false);

            $table->timestamps();

            $table->index('bus_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};