<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('application_type');
            $table->foreign('application_type')->references('type')->on('applications');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('access_token', 700);
            $table->integer('expires_in')->unsigned()->nullable();
            $table->string('refresh_token', 700)->nullable();
            $table->boolean('authorized')->unsigned()->default(0);
            $table->text('account_data_json');

            $table->index('authorized');

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
        Schema::dropIfExists('application_accounts');
    }
}
