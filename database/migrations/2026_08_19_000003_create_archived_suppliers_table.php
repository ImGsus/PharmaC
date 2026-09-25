<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('archived_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->timestamp('archived_at');
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archived_suppliers');
    }
}
