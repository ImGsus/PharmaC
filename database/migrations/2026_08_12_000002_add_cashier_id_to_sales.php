<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an optional cashier_id (FK to users.id) to the sales table.
 *
 * Required for the new "POS sessions" feature so the running earnings total
 * for a session can scope by which cashier recorded each sale. Nullable so
 * the column is safe to backfill — pre-existing sales rows are simply not
 * attributed to any user.
 *
 * Named `cashier_id` (not `user_id`) to keep the sales schema readable and
 * to avoid colliding with any future generic user_id column.
 */
class AddCashierIdToSales extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('cashier_id')->nullable()->after('notes');
            $table->foreign('cashier_id')->references('id')->on('users')->nullOnDelete();
            $table->index('cashier_id');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropIndex(['cashier_id']);
            $table->dropColumn('cashier_id');
        });
    }
}