<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Nullable: las categorías existentes (Hardware, Software, Red, Accesos,
            // Compras) no tienen subcategorías, y un ticket sigue siendo válido sin una.
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
        });
    }
};
