<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->after('product');
            $table->date('manufacture_date')->nullable()->after('batch_number');
            $table->unsignedInteger('reorder_level')->default(10)->after('quantity');
            $table->string('order_number')->nullable()->after('reorder_level');
            $table->date('expected_delivery_date')->nullable()->after('order_number');
            $table->date('received_date')->nullable()->after('expected_delivery_date');
            $table->string('status')->default('received')->after('received_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'batch_number',
                'manufacture_date',
                'reorder_level',
                'order_number',
                'expected_delivery_date',
                'received_date',
                'status',
            ]);
        });
    }
};
