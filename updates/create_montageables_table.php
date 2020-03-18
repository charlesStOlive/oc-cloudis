<?php namespace Waka\Cloudis\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateMontageablesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_cloudis_montageables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('montage_id');
            $table->integer('montageable_id');
            $table->string('montageable_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_cloudis_montageables');
    }
}
