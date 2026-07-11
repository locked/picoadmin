<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_set')->default(false);
            $table->integer('weekdays');
            $table->integer('hour');
            $table->integer('minute');
            $table->string('week')->default('all');
            $table->string('chime')->nullable();
            $table->dateTime('modified')->useCurrent();
            $table->dateTime('created')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alarms');
    }
};
