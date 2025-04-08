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
        Schema::create('pn_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('message_id');
            $table->boolean('sent')->default(1);
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('pn_devices');
            $table->foreign('message_id')->references('id')->on('pn_messages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pn_deliveries');
    }
};
