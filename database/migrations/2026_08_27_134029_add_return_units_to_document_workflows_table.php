<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_workflows', function (Blueprint $table) {
            $table->foreignId('return_by_unit_id')->nullable()->constrained('units')->nullOnDelete()->after('to_unit_id');
            $table->foreignId('return_to_unit_id')->nullable()->constrained('units')->nullOnDelete()->after('return_by_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('document_workflows', function (Blueprint $table) {
            $table->dropForeign(['return_by_unit_id']);
            $table->dropForeign(['return_to_unit_id']);
            $table->dropColumn(['return_by_unit_id', 'return_to_unit_id']);
        });
    }
};