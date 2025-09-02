<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SoldItem;

class SoldItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'item_id' => '1',
            'user_id' => '2',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '2',
            'user_id' => '2',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '3',
            'user_id' => '2',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '4',
            'user_id' => '2',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '5',
            'user_id' => '2',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '6',
            'user_id' => '1',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '7',
            'user_id' => '1',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '8',
            'user_id' => '1',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '9',
            'user_id' => '1',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);

        $param = [
            'item_id' => '10',
            'user_id' => '1',
            'sending_postcode' => '1080014',
            'sending_address' => '東京都港区芝5丁目29-20610',
            'sending_building' => 'クロスオフィス三田',
        ];
        SoldItem::create($param);
    }
}
