<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('integration_id')->unsigned();
            $table->string('app_type_trigger')->nullable();
            $table->string('app_type_action')->nullable();
            $table->enum('action', ['activated', 'deactivated', 'deleted']);
            $table->string('performed_by');
            $table->string('reason')->nullable();
            $table->string('command')->nullable();

            $table->timestamp('created_at');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_logs');
    }
};
