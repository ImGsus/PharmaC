<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityBreakdownToPurchasesTable extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->integer('item_quantity')->default(0)->after('cost_price');
            $table->integer('packaging_box')->default(0)->after('item_quantity');
            $table->integer('quantity_per_box')->default(0)->after('packaging_box');
            $table->integer('total_quantity')->default(0)->after('quantity_per_box');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['item_quantity', 'packaging_box', 'quantity_per_box']);
        });
    }
}
