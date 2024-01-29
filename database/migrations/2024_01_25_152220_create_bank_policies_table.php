<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_bank_accounts_id')->references('id')->on('agency_bank_accounts');
            $table->foreignId('currency_id')->references('id')->on('currency');
            $table->text('description');
            $table->dateTime('date_apply')->nullable();
            $table->string('policy_type')->nullable();
            $table->string('payment_source')->nullable();
            $table->string('reference')->nullable();
            $table->double('exchange_rate','12,5')->default(1);
            $table->double('amount','12,5')->default(0);
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
        Schema::dropIfExists('bank_policies');
    }
}
