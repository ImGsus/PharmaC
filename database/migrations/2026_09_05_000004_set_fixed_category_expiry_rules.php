<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')
            ->where('fixed_key', 'medical-devices')
            ->update(['no_expiry' => true]);
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('fixed_key', 'medical-devices')
            ->update(['no_expiry' => false]);
    }
};
