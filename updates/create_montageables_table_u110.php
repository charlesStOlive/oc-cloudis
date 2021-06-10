<?php namespace Waka\Utils\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateDocumentsTableU110 extends Migration
{
    public function up()
    {
        Schema::table('waka_cloudis_montageables', function (Blueprint $table) {
            $table->boolean('errors')->default(false);
        });
    }

    public function down()
    {
        Schema::table('waka_cloudis_montageables', function (Blueprint $table) {
            $table->dropColumn('errors');
        });
    }
}
