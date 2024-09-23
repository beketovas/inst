<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNodePollingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_pollings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('application_type');
            $table->foreign('application_type')->references('type')->on('applications')->onUpdate('cascade');
            $table->integer('node_id')->unsigned();
            $table->foreign('node_id')->references('id')->on('integration_nodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('trigger_type');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->index('trigger_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_pollings');
    }
}
