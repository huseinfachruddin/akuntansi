<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocktransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('stocktransactions', function (Blueprint $table) {
            $table->id();
            $table->biginteger('contact_id')->nullable()->nullable();
            $table->biginteger('cashin_id')->nullable();
            $table->biginteger('cashout_id')->nullable();
            $table->string('staff')->nullable();
            $table->double('total')->nullable();
            $table->double('paid')->nullable();
            $table->string('payment_due')->nullable();
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
        Schema::dropIfExists('stocktransactions');
    }
}
