<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashtransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashtransactions', function (Blueprint $table) {
            $table->id();
            $table->integer('from')->nullable();
            $table->integer('to')->nullable();
            $table->biginteger('cashin')->nullable();
            $table->biginteger('cashout')->nullable();
            $table->biginteger('transfer')->nullable();
            $table->string('staff')->nullable();
            $table->string('desc')->nullable();
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
        Schema::dropIfExists('cashtransactions');
    }
}
