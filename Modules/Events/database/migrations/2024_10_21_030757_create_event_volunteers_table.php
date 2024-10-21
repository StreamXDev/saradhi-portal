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
        Schema::create('event_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('active')->default(1);
            $table->enum('status', array('active','inactive','removed'))->default('active');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->dateTime('added_on')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users'); 
            $table->foreign('added_by')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_volunteers');
    }
};
