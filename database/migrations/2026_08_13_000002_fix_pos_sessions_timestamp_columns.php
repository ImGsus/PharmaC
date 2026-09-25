<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixPosSessionsTimestampColumns extends Migration
{
    public function up()
    {
        // Use DATETIME so MySQL does not implicitly add ON UPDATE CURRENT_TIMESTAMP
        // behavior to the first TIMESTAMP column. This prevents started_at from
        // being overwritten when the row is updated on session end.
        DB::statement('ALTER TABLE `pos_sessions` MODIFY `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE `pos_sessions` MODIFY `ended_at` DATETIME NULL DEFAULT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `pos_sessions` MODIFY `started_at` TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE `pos_sessions` MODIFY `ended_at` TIMESTAMP NULL DEFAULT NULL');
    }
}
