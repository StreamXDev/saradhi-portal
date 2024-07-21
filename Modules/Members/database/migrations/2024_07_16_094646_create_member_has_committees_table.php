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
        Schema::create('member_has_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_committee_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('designation_id')->constrained('member_enums');
            $table->boolean('active')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_has_committees');
    }
};
