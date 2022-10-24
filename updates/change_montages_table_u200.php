<?php namespace Waka\Worder\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;
use Waka\Cloudis\Models\Montage;
use Waka\Session\Models\WakaSession;

class ChangeMontagesTableU200 extends Migration
{
    public function up()
    {
        $montages = Montage::get();
        foreach($montages as $montage) {
            $ds = $montage->data_source;
            $testId = $montage->test_id;
            if($ds) {
                $wakaSession = new WakaSession();
                $wakaSession->data_source = $ds;
                $wakaSession->ds_id_test = $testId;
                $wakaSession->name = 'montage_'.$montage->slug;
                $wakaSession->has_ds = true;
                $wakaSession->embed_all_ds = true;
                $wakaSession->key_duration = '1y';
                $wakaSession->save();
                $montage->waka_session()->add($wakaSession);
            }
        }
    }

    public function down()
    {

    }
}