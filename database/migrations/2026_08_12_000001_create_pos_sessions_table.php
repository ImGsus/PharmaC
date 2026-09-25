<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per cashier shift on the POS page.
 *  - started_at: set when the cashier clicks "Start Session"
 *  - ended_at:   set when they click "End Session" (null while still open)
 * A user can have only ONE open session at a time; the Start button finds it
 * (or creates a new one) on click. Total earnings is computed live from the
 * sales table for the window [started_at, ended_at] — no totals are stored
 * here so the source of truth stays in sales.
 */
class CreatePosSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            // Speeds up the two queries we run: "find the current open session
            // for this user" and "list recent sessions for this user".
            $table->index(['user_id', 'ended_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_sessions');
    }
}