<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleOrderProductsPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_order_products_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_order_id');
            $table->foreignId('product_id');
            $table->double('price_unit');
            $table->integer('quantity');
            $table->string('currency');
            $table->double('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_order_products_pivot');
    }
}
