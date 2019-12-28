<?php namespace Waka\Cloudis\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMontagesTable extends Migration
{
    public function up()
    {
        Schema::create('waka_cloudis_montages', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('slug');

            $table->boolean('active')->default(true);
            $table->boolean('auto_create')->default(true);
            $table->boolean('ready')->default(false);

            $table->boolean('data_source_id');

            $table->text('options')->nullable();
            
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('nest_left')->unsigned()->nullable();
            $table->integer('nest_right')->unsigned()->nullable();
            $table->integer('nest_depth')->unsigned()->nullable();
                                                
            $table->softDeletes();
                        
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_cloudis_montages');
    }
}
