<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('action_id')->unsigned();
            $table->foreign('action_id')->references('id')->on('instagram_actions')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title');
            $table->string('identifier', 500);
            $table->string('type');
            $table->boolean('required')->default(0);
            $table->boolean('dynamic')->default(0);
            $table->json('uses_fields')->nullable();
            $table->json('dropdown_source')->nullable();
            $table->string('description', 500)->default('');
            $table->string('loader', 255)->nullable();
            $table->integer('ordering')->default(1);
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
        Schema::dropIfExists('instagram_action_fields');
    }
}
