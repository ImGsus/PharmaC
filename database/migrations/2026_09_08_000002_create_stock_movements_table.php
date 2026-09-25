<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type');
            $table->integer('quantity');
            $table->integer('quantity_after')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('batch_number')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['movement_type', 'created_at']);
            $table->index(['batch_number', 'purchase_id']);
        });

        $purchases = DB::table('purchases')->select('id', 'quantity', 'cost_price', 'batch_number', 'created_at')->get();
        foreach ($purchases as $purchase) {
            DB::table('stock_movements')->insert([
                'purchase_id' => $purchase->id,
                'movement_type' => 'opening',
                'quantity' => (int) $purchase->quantity,
                'quantity_after' => (int) $purchase->quantity,
                'unit_cost' => $purchase->cost_price,
                'batch_number' => $purchase->batch_number,
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'notes' => 'Opening balance imported from existing purchase stock.',
                'created_at' => $purchase->created_at ?: now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
