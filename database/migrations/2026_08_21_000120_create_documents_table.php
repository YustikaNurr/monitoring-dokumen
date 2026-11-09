<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->date('received_at');
            $table->string('subject');
            $table->foreignId('document_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('sender_unit_id')->constrained('units')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('initial_pic');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
