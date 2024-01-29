<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('external_id');
            $table->integer('amount');
            $table->string('description');
            $table->string('payment_source');
            $table->date('payment_date');
            $table->string('integration_payment_id')->nullable();
            $table->string('integration_type')->nullable();
            // $table->integer('invoice_id')->unsigned();
            // $table->foreign('invoice_id')->references('id')->on('invoices');

            $table->morphs('invoiceable');

            $table->softDeletes();
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
        Schema::dropIfExists('payments');
    }
}
