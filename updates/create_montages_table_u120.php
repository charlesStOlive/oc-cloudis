<?php namespace Waka\Cloudis\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateMontagesTableU120 extends Migration
{
    public function up()
    {
        Schema::table('waka_cloudis_montages', function (Blueprint $table) {
            $table->text('memo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_cloudis_montages', function (Blueprint $table) {
            $table->dropColumn('memo');
        });
    }
}