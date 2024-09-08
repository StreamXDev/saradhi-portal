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
        Schema::table('member_details', function (Blueprint $table) {
            $table->string('whatsapp_code')->nullable()->after('whatsapp');
            $table->string('emergency_phone_code')->nullable()->after('emergency_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            $table->dropColumn('whatsapp_code');
            $table->dropColumn('emergency_phone_code');
        });
    }
};
