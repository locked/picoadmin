<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_model_id')->constrained()->cascadeOnDelete();
            $table->string('serialnumber');
            $table->string('target_firmware')->nullable();
            $table->string('current_firmware')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('modified')->useCurrent();
            $table->dateTime('created')->useCurrent();
            $table->unique(['device_model_id', 'serialnumber']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
