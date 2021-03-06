<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserResidenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_residence', function (Blueprint $table) {
            $table->increments('id');

            $table->string('user_id', 6);
            $table->integer('residence_id')->unsigned();
            $table->string('room', 10);
            $table->date('from');
            $table->date('to');

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
        Schema::drop('user_residence');
    }
}
