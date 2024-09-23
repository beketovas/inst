<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('node_id')->unsigned();
            $table->foreign('node_id')->references('id')->on('instagram_nodes')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('field_id')->unsigned();
            $table->foreign('field_id')->references('id')->on('instagram_fields')->onDelete('cascade')->onUpdate('cascade');
            $table->string('value', 500)->nullable();
            $table->json('value_json')->nullable();
            $table->json('additional_data')->nullable();
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
        Schema::dropIfExists('instagram_field_values');
    }
}
