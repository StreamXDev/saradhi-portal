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
        Schema::create('member_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('member_unit_id')->constrained();
            $table->string('civil_id')->nullable(); //done
            $table->date('dob'); //done
            $table->string('whatsapp'); //done
            $table->string('emergency_phone')->nullable(); //done
            $table->string('company')->nullable();
            $table->string('profession')->nullable();
            $table->string('company_address')->nullable();
            $table->string('passport_no')->nullable(); //done
            $table->date('passport_expiry')->nullable(); //done
            $table->string('photo_civil_id_front')->nullable();
            $table->string('photo_civil_id_back')->nullable();
            $table->string('photo_passport_front')->nullable();
            $table->string('photo_passport_back')->nullable();
            $table->string('paci')->nullable();
            $table->string('sndp_branch')->nullable();
            $table->string('sndp_branch_number')->nullable();
            $table->string('sndp_union')->nullable();
            $table->boolean('completed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_details');
    }
};
