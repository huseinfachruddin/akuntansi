<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToContacttypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacttypes', function (Blueprint $table) {
            $table->string('category')->nullable();
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('category')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacttypes', function (Blueprint $table) {
            $table->dropColumn('category');
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
