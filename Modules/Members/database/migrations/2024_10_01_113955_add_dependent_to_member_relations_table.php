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
        Schema::table('member_relations', function (Blueprint $table) {
            $table->foreignId('dependent_id')->nullable()->references('id')->on('member_dependents')->after('related_member_id');
            $table->foreignId('related_dependent_id')->nullable()->references('id')->on('member_dependents')->after('related_member_id');
            $table->dropForeign('member_relations_member_id_foreign');
            $table->bigInteger('member_id')->nullable()->unsigned()->change();
            $table->foreign('member_id')->references('id')->on('members');
            $table->dropForeign('member_relations_related_member_id_foreign');
            $table->bigInteger('related_member_id')->nullable()->unsigned()->change();
            $table->foreign('related_member_id')->references('id')->on('members');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_relations', function (Blueprint $table) {
            $table->dropColumn('dependent_id');
            $table->dropColumn('related_dependent_id');
            $table->dropForeign('member_relations_member_id_foreign');
            $table->bigInteger('member_id')->nullable(false)->unsigned()->change();
            $table->foreign('member_id')->references('id')->on('members');
            $table->dropForeign('member_relations_related_member_id_foreign');
            $table->bigInteger('related_member_id')->nullable(false)->unsigned()->change();
            $table->foreign('related_member_id')->references('id')->on('members');
        });
    }
};
