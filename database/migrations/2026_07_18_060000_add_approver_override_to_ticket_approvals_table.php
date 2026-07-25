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
        Schema::table('ticket_approvals', function (Blueprint $table) {
            // Aprobador ajustado solo para este ticket, sin tocar el flujo general.
            // Si es null, se usa approval_levels.approver_id (el aprobador por defecto del nivel).
            $table->foreignId('approver_id')->nullable()->after('approval_level_id')->constrained('users')->nullOnDelete();
        });

        // Nullable para soportar aprobaciones ad-hoc (categoría sin flujo configurado),
        // que no pertenecen a ningún nivel de ningún flujo.
        Schema::table('ticket_approvals', function (Blueprint $table) {
            $table->foreignId('approval_level_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_approvals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approver_id');
        });

        Schema::table('ticket_approvals', function (Blueprint $table) {
            $table->foreignId('approval_level_id')->nullable(false)->change();
        });
    }
};
