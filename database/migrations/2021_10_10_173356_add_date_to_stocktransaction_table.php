<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateToStocktransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stocktransactions', function (Blueprint $table) {
            $table->dropColumn('payment_due');
        });
        Schema::table('stocktransactions', function (Blueprint $table) {
            $table->date('payment_due')->nullable()->before('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stocktransactions', function (Blueprint $table) {
        });
    }
}
