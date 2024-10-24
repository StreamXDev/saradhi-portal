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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->string('type')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('designation')->nullable();
            $table->string('unit')->nullable()->index();
            $table->integer('pack_count')->default(1);
            $table->integer('admit_count')->default(0);
            $table->boolean('admitted')->default(0);
            $table->dateTime('admitted_on')->nullable();
            $table->unsignedBigInteger('admitted_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users'); 
            $table->foreign('admitted_by')->references('id')->on('users'); 
            $table->foreign('created_by')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
