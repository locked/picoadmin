<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmware', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_model_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->binary('data');
            $table->dateTime('modified')->useCurrent();
            $table->dateTime('created')->useCurrent();
            $table->unique(['device_model_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firmware');
    }
};
