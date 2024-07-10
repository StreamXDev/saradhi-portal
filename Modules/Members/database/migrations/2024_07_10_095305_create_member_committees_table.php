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
        Schema::create('member_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_type_id')->constrained('member_enums');
            $table->foreignId('member_unit_id')->nullable()->constrained();
            $table->foreignId('designation_id')->constrained('member_enums');
            $table->foreignId('member_id')->constrained();
            $table->string('year');
            $table->boolean('active')->default(1)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_committees');
    }
};
