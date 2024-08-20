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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mid')->unique()->nullable();
            $table->foreignId('user_id')->constrained();
            $table->date('start_date')->nullable();
            $table->date('updated_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('type', array('single', 'family'))->index();
            $table->enum('family_in', array('india', 'kuwait'))->nullable();
            $table->enum('status', array('active','inactive', 'rejected', 'dormant'))->default('inactive')->index();
            $table->enum('joined_as', array('old', 'new'))->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
