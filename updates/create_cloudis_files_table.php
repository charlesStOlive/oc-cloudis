<?php namespace Waka\Cloudis\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class CreateCloudisFilesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_cloudis_cloudis_files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('code');
            $table->string('version');
            $table->timestamp('last_update_at');
            $table->morphs('cloudeable');
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_cloudis_cloudis_files');
    }
}
