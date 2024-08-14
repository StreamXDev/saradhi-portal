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
        Schema::create('member_introduces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('introducer_id')->nullable();
            $table->string('introducer_name');
            $table->string('introducer_phone');
            $table->string('introducer_mid')->index();
            $table->string('introducer_unit');
            $table->timestamps();

            $table->foreign('introducer_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_introduces');
    }
};
