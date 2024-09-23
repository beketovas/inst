<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('integration_id')->unsigned();
            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');
            $table->string('application_id')->nullable();
            $table->string('application_type')->nullable();
            $table->integer('account_id')->nullable();
            $table->integer('ordering');
            $table->timestamps();

            $table->index('application_id');
            $table->index('application_type');
            $table->index('account_id');
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
        Schema::dropIfExists('integration_nodes');
    }
}
