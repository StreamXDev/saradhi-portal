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
        Schema::table('member_dependents', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_user_id')->nullable();
            $table->bigInteger('parent_mid')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->index();
            $table->string('email')->nullable();
            $table->string('calling_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('gender');
            $table->string('blood_group');
            $table->string('civil_id')->nullable();
            $table->date('dob');
            $table->string('passport_no')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('photo_civil_id_front')->nullable();
            $table->string('photo_civil_id_back')->nullable();
            $table->string('photo_passport_front')->nullable();
            $table->string('photo_passport_back')->nullable();

            $table->foreign('parent_user_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_dependents', function (Blueprint $table) {
            $table->dropColumn('parent_user_id')->nullable();
            $table->dropColumn('type');
            $table->dropColumn('name')->index();
            $table->dropColumn('email')->nullable();
            $table->dropColumn('calling_code')->nullable();
            $table->dropColumn('phone')->nullable();
            $table->dropColumn('avatar')->nullable();
            $table->dropColumn('gender');
            $table->dropColumn('blood_group');
            $table->dropColumn('civil_id')->nullable();
            $table->dropColumn('dob');
            $table->dropColumn('passport_no')->nullable();
            $table->dropColumn('passport_expiry')->nullable();
            $table->dropColumn('photo_civil_id_front')->nullable();
            $table->dropColumn('photo_civil_id_back')->nullable();
            $table->dropColumn('photo_passport_front')->nullable();
            $table->dropColumn('photo_passport_back')->nullable();
        });
    }
};
