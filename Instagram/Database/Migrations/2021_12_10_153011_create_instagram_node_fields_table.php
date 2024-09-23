<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramNodeFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_node_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('node_id')->unsigned();
            $table->foreign('node_id')->references('id')->on('instagram_nodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('identifier', 500);
            $table->string('title');
            $table->string('type');
            $table->boolean('required')->default(0);
            $table->string('example_value', 512)->nullable();
            $table->boolean('dynamic')->default(0);
            $table->json('uses_fields')->nullable();
            $table->json('dropdown_source')->nullable();
            $table->string('description', 500)->default('');
            $table->bigInteger('position')->unsigned()->nullable();
            $table->boolean('custom_field')->default(false);
            $table->string('loader', 255)->nullable();
            $table->integer('ordering')->default(1);
            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('instagram_node_fields')->onDelete('cascade');
            $table->timestamps();

            $table->index('identifier');
            $table->index('ordering');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instagram_node_fields');
    }
}
