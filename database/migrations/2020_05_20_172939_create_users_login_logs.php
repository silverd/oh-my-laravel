<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersLoginLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid');
            $table->string('api_token', 50);
            $table->string('ip', 15);
            $table->timestamps();
            $table->unique(['uid', 'api_token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_login_logs');
    }
}
