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
        Schema::create('pn_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title'); 
            $table->string('description')->nullable(); 
            $table->string('image')->nullable(); 
            $table->string('link')->nullable();
            $table->integer('total_users')->default(0);
            $table->integer('total_sent')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pn_messages');
    }
};
