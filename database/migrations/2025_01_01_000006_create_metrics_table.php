<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->integer('metric_type');
            $table->decimal('metric_value', 8, 2);
            $table->dateTime('metric_date');
            $table->dateTime('created')->useCurrent();
            $table->unique(['device_id', 'metric_type', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
