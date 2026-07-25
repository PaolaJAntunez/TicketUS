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
        Schema::table('approval_levels', function (Blueprint $table) {
            $table->foreignId('approver_id')->nullable()->after('order')->constrained('users')->nullOnDelete();
        });

        // "role" queda en desuso a favor de approver_id, pero no se borra
        // (compatibilidad con el nivel existente que aún lo usa).
        Schema::table('approval_levels', function (Blueprint $table) {
            $table->string('role')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_levels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approver_id');
        });

        Schema::table('approval_levels', function (Blueprint $table) {
            $table->string('role')->nullable(false)->change();
        });
    }
};
