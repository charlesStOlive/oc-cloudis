<?php namespace Waka\Cloudis\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateBibliosTable extends Migration
{
    public function up()
    {
        Schema::create('waka_cloudis_biblios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('type')->nullable();
            $table->text('options')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_cloudis_biblios');
    }
}