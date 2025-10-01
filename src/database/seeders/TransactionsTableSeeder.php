<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\SoldItem;
use App\Models\Item;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $soldItems = SoldItem::all();

        foreach ($soldItems as $soldItem) {
            $item = Item::find($soldItem->item_id);

            if ($item && !Transaction::where('item_id', $soldItem->item_id)->exists()) {
                Transaction::create([
                    'item_id' => $soldItem->item_id,
                    'buyer_id' => $soldItem->user_id,
                    'seller_id' => $item->user_id,
                    'status' => 'ongoing',
                ]);
            }
        }
    }
}
