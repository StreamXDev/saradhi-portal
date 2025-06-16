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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); //done
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('type', array('primary','spouse'))->default('primary')->index(); //done
            $table->string('name')->index(); //done
            $table->enum('gender', array('male','female', 'other'))->nullable(); //done
            $table->string('blood_group')->index()->nullable(); //done
            $table->string('photo')->nullable();
            $table->boolean('active')->index()->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
