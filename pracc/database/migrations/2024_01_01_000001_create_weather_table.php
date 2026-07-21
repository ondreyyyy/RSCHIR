<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather', function (Blueprint $table) {
            $table->id();
            $table->string('city', 100);
            $table->float('temperature');
            $table->string('description', 255);
            $table->integer('humidity');
            $table->integer('pressure');
            $table->date('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather');
    }
};
