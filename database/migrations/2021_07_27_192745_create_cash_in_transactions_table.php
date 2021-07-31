<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashInTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Cashintrans', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('cash_id');
            $table->biginteger('total');
            $table->timestamps();

            $table->foreign('cash_id')->references('id')->on('cashes')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_in_transactions');

    }
}
