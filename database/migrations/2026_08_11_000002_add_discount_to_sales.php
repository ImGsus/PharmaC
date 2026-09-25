<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountToSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Cashier-facing discount applied to the whole sale.
            // Written to every Sale row of a single Save click so the receipt
            // (which groups by payment_method+payment_amount+minute) can read
            // it back without a parent Order record.
            $table->decimal('discount', 12, 2)->nullable()->after('change_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
}