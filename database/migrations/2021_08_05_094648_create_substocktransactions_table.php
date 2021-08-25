<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubstocktransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('substocktransactions', function (Blueprint $table) {
            $table->id();
            $table->biginteger('stocktransaction_id')->nullable();
            $table->biginteger('product_id')->nullable();
            $table->double('qty')->nullable();
            $table->double('left')->nullable();
            $table->double('purchase_price')->nullable();
            $table->double('hpp')->nullable();
            $table->double('total')->nullable();
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
        Schema::dropIfExists('substocktransactions');
    }
}
