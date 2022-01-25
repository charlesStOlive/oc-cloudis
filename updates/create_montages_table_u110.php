<?php namespace Waka\Cloudis\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class CreateMontagesTableU110 extends Migration
{
    public function up()
    {
        Schema::table('waka_cloudis_montages', function (Blueprint $table) {
        });
    }

    public function down()
    {
        Schema::table('waka_cloudis_montages', function (Blueprint $table) {
        });
    }
}