<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAkunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('akuns', function (Blueprint $table) {
            $table->id();
            $table->integer('perent_id')->nullable();
            $table->string('name')->nullable();
            $table->biginteger('total')->nullable();
            $table->boolean('iscash')->nullable();
            $table->boolean('isheader')->nullable();
            $table->boolean('iscashout')->nullable();
            $table->boolean('iscashin')->nullable();

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
        Schema::dropIfExists('akuns');
    }
}
