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
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('member_type', array('primary','spouse', 'child'))->default('primary')->index();
            $table->string('civil_id');
            $table->string('name');
            $table->enum('gender', array('male','female', 'other'));
            $table->date('dob');
            $table->string('company')->nullable();
            $table->string('profession')->nullable();
            $table->string('passport_no')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('blood_group')->index();
            $table->string('photo')->nullable();
            $table->boolean('active')->index()->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('members'); 
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
