<?php

use App\AppConfig;
use Illuminate\Database\Seeder;

class AppConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AppConfig::truncate();

        AppConfig::firstOrCreate(
            [
                'group' => 'huawei',
                'name'  => 'ad',
                'state' => 0,
            ]
        );
        AppConfig::firstOrCreate(
            [
                'group' => 'huawei',
                'name'  => 'wallet',
                'state' => 0,
            ]
        );
        AppConfig::firstOrCreate(
            [
                'group' => 'android',
                'name'  => 'ad',
                'state' => 0,
            ]
        );
        AppConfig::firstOrCreate(
            [
                'group' => 'android',
                'name'  => 'wallet',
                'state' => 0,
            ]
        );
        AppConfig::firstOrCreate(
            [
                'group' => 'ios',
                'name'  => 'ad',
                'state' => 0,
            ]
        );
        AppConfig::firstOrCreate(
            [
                'group' => 'ios',
                'name'  => 'wallet',
                'state' => 0,
            ]
        );
        AppConfig::updateOrCreate(
            [
                'group' => 'record',
                'name'  => 'web',
            ], [
                'state' => AppConfig::STATUS_ON,
            ]
        );
    }
}
