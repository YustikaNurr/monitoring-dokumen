<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // DOCUMENT_DISPOSITION, DOCUMENT_RETURNED, etc.
            $table->morphs('notifiable'); // user_id, unit_id (polymorphic)
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_workflow_id')->nullable()->constrained()->nullOnDelete();
            $table->json('data')->nullable(); // additional data: from_unit, to_unit, message, etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};