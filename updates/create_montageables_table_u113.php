<?php namespace Waka\Utils\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateDocumentsTableU113 extends Migration
{
    public function up()
    {
        Schema::table('waka_cloudis_montageables', function (Blueprint $table) {
            $table->index(['montageable_id', 'montageable_type'], 'montageable');
        });
    }

    public function down()
    {
        Schema::table('waka_cloudis_montageables', function (Blueprint $table) {
            $table->dropIndex('montageable');
        });
    }
}
