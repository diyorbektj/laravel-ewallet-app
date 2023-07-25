<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_registers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('sms_code');
            $table->boolean('step_1')->default(1);
            $table->boolean('step_2')->default(0);
            $table->boolean('step_3')->default(0);
            $table->integer('count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_registers');
    }
};
