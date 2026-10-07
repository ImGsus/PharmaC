<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoxExpiriesToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (! Schema::hasColumn('purchases', 'box_expiries')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->text('box_expiries')->nullable()->after('expiry_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('purchases', 'box_expiries')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('box_expiries');
            });
        }
    }
}

