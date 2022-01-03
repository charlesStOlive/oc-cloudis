<?php namespace Wcli\Crm\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;

class UpdateCloudisSystemFiles extends Migration
{
    public function up()
    {
        Schema::table('waka_cloudis_system_files', function (Blueprint $table) {
            $table->text('options')->nullable();
        });
    }

    public function down()
    {
        Schema::table('waka_cloudis_system_files', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
}