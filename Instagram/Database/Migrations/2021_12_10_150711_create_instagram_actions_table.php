<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('name_ru')->default('');
            $table->text('description')->nullable();
            $table->string('task');
            $table->string('type');
            $table->boolean('for_trigger')->default(0);
            $table->boolean('for_action')->default(0);
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instagram_actions');
    }
}
