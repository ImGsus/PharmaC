<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerInfoToSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('total_price');
            $table->text('notes')->nullable()->after('customer_name');
            $table->string('payment_method')->nullable()->after('notes');
            $table->decimal('payment_amount', 12, 2)->nullable()->after('payment_method');
            $table->decimal('change_amount', 12, 2)->nullable()->after('payment_amount');
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
            $table->dropColumn(['customer_name', 'notes', 'payment_method', 'payment_amount', 'change_amount']);
        });
    }
}
