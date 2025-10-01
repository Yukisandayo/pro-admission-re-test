<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use App\Models\User;
use App\Models\Item;
use App\Models\SoldItem;
use App\Models\Transaction;
use App\Models\Chat;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function profile(){

        $profile = Profile::where('user_id', Auth::id())->first();

        return view('profile',compact('profile'));
    }

    public function updateProfile(ProfileRequest $request){

        $img = $request->file('img_url');
        if (isset($img)){
            $img_url = Storage::disk('local')->put('public/img', $img);
        }else{
            $img_url = '';
        }

        $profile = Profile::where('user_id', Auth::id())->first();
        if ($profile){
            $profile->update([
                'user_id' => Auth::id(),
                'img_url' => $img_url,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building
            ]);
        }else{
            Profile::create([
                'user_id' => Auth::id(),
                'img_url' => $img_url,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building
            ]);
        }

        User::find(Auth::id())->update([
            'name' => $request->name
        ]);

        return redirect('/');
    }

    public function mypage(Request $request){
        $user = User::find(Auth::id());
        $userId = $user->id;
        $items = collect();
        $transactions = collect();

        $allTransactions = Transaction::where(function ($query) use ($userId) {
            $query->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
        })
        ->where('status', 'ongoing')
        ->orderByRaw('
            COALESCE(
                (SELECT created_at FROM chats WHERE transaction_id = transactions.id ORDER BY created_at DESC LIMIT 1),
                transactions.created_at
            ) DESC
        ')
        ->withCount(['chats as unread_count' => function ($q) use ($userId) {$q->where('is_read', false)->where('user_id', '!=', $userId);
            }])
        ->get();

        $totalUnread = $allTransactions->sum('unread_count');

        if ($request->page == 'buy') {
            $items = SoldItem::where('user_id', $userId)->get()->map(function ($sold_item) {
                return $sold_item->item;
            });
            $transactions = collect();
        } elseif ($request->page == 'ongoing') {
            $transactions = $allTransactions;
        } else {
            $items = Item::where('user_id', $userId)->get();
            $transactions = collect();
        }
        return view('mypage', compact('user', 'items', 'transactions', 'totalUnread'));
    }
}
