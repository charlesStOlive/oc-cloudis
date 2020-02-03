<?php namespace Waka\Cloudis\Updates;

use DB;
use Excel;
use Seeder;

class SeedExemples extends Seeder
{
    public function run()
    {
        Db::table('waka_cloudis_montages')->truncate();
        $sql = plugins_path('waka/cloudis/updates/sql/waka_cloudis_montages.sql');
        DB::unprepared(file_get_contents($sql));
    }

}
