<?php namespace Waka\Cloudis\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CleanUnused_112 extends Migration
{
    public function up()
    {
        Schema::dropIfExists('waka_cloudis_cloudis_files');
    }

    public function down()
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
}
