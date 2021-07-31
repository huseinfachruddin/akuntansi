<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashinakunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashinakuns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashin_id');
            $table->foreignId('akun_id');
            $table->string('desc');
            $table->biginteger('total');
            $table->timestamps();

            $table->foreign('cashin_id')->references('id')->on('cashintrans')->onDelete('cascade');
            $table->foreign('akun_id')->references('id')->on('akuns')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cashinakuns');
    }
}
