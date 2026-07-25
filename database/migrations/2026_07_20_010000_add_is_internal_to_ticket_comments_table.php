<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {
            // Nota interna: visible solo para admin/agente, distinta de la
            // respuesta al solicitante (misma tabla, un flag).
            $table->boolean('is_internal')->default(false)->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }
};
