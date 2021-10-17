<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataToContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('maxdebt');
            $table->dropColumn('category');
        });
        Schema::table('contacttypes', function (Blueprint $table) {
            $table->double('max_paydue')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->double('maxdebt');
            $table->string('category');
        });
        Schema::table('contacttypes', function (Blueprint $table) {
            $table->dropColumn('max_paydue');
        });
    }
}
