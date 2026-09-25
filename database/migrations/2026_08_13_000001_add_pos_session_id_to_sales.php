<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPosSessionIdToSales extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('pos_session_id')->nullable()->after('cashier_id');
            $table->foreign('pos_session_id')->references('id')->on('pos_sessions')->nullOnDelete();
            $table->index('pos_session_id');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['pos_session_id']);
            $table->dropIndex(['pos_session_id']);
            $table->dropColumn('pos_session_id');
        });
    }
}
