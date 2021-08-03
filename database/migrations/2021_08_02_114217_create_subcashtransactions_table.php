<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubcashtransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subcashtransactions', function (Blueprint $table) {
            $table->id();
            $table->integer('cashtransaction_id')->nullable();
            $table->integer('akun_id')->nullable();
            $table->string('desc')->nullable();
            $table->biginteger('total')->nullable();
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
        Schema::dropIfExists('subcashtransactions');
    }
}
