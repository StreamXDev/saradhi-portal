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
        Schema::table('event_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_user_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('dependent_id')->nullable()->after('user_id');
            $table->string('relation')->nullable()->after('user_id');

            $table->foreign('parent_user_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            
        });
    }
};
