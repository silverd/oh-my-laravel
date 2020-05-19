<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersStatsDaily extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_stats_daily', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid');
            $table->date('today');
            $table->timestamps();
            $table->index(['uid', 'today']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_stats_daily');
    }
}
