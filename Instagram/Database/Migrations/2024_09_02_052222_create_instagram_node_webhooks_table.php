<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramNodeWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_node_webhooks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('integration_id')->unsigned();
            $table->foreign('integration_id')->references('id')->on('integrations')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('node_id')->unsigned();
            $table->foreign('node_id')->references('id')->on('instagram_nodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('code')->unique();
            $table->boolean('opened_for_sample')->default(false);
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
        Schema::dropIfExists('instagram_node_webhooks');
    }
}
