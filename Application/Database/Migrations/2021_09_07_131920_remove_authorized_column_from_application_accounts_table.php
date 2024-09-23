<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAuthorizedColumnFromApplicationAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_accounts', function (Blueprint $table) {
            $table->dropColumn('authorized');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_accounts', function (Blueprint $table) {
            $table->boolean('authorized')->unsigned()->default(0);
        });
    }
}
