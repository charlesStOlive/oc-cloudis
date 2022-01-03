<?php namespace Wcli\Crm\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class UpdateBiblioSystemFiles extends Migration
{
    public function up()
    {
        Schema::table('waka_cloudis_biblios', function (Blueprint $table) {
            $table->text('load_options')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_cloudis_biblios', function (Blueprint $table) {
            $table->dropColumn('load_options');
        });
    }
}