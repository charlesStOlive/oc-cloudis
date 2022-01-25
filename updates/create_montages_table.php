<?php namespace Waka\Cloudis\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateMontagesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_cloudis_montages', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('state')->default('Actif');
            $table->string('name');
            $table->string('slug');
            $table->string('data_source');
            $table->string('test_id')->nullable();
            $table->text('options')->nullable();
            $table->boolean('use_files')->nullable()->default(false);
            //reorder
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_cloudis_montages');
    }
}