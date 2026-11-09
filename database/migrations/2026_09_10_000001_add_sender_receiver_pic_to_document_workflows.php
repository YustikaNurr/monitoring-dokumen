<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_workflows', function (Blueprint $table) {
            $table->string('sender_pic')->nullable()->after('pic');
            $table->string('receiver_pic')->nullable()->after('sender_pic');
        });
    }

    public function down(): void
    {
        Schema::table('document_workflows', function (Blueprint $table) {
            $table->dropColumn(['sender_pic', 'receiver_pic']);
        });
    }
};
