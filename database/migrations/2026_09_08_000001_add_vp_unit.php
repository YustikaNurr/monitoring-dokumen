<?php

use App\Models\Unit;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Unit::query()->firstOrCreate(['name' => 'VP']);
    }

    public function down(): void
    {
        Unit::query()->where('name', 'VP')->delete();
    }
};
