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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable()->after('assigned_to');
            $table->decimal('amount', 12, 2)->nullable()->after('resolved_at');
            $table->text('reopened_reason')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['resolved_at', 'amount', 'reopened_reason']);
        });
    }
};
