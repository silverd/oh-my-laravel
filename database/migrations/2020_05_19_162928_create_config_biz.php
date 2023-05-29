<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigBiz extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_biz', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50);
            $table->text('value')->nullable();
            $table->tinyInteger('value_type')->unsigned()->default(1);
            $table->string('remark', 100)->nullable();
            $table->json('scopes')->nullable();
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
        Schema::dropIfExists('config_biz');
    }
}
