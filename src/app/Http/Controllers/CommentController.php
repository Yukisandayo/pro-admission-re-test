<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function create($item_id, CommentRequest $request)
    {
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->item_id = $item_id;
        $comment->comment = $request->comment;
        $comment->save();

        $item = Item::findOrFail($item_id);

        $existingTransaction = Transaction::where('item_id', $item_id)
            ->where('buyer_id', Auth::id())
            ->where('seller_id', $item->user_id)
            ->first();

        if (!$existingTransaction) {
            $transaction = new Transaction();
            $transaction->item_id = $item_id;
            $transaction->buyer_id = Auth::id();
            $transaction->seller_id = $item->user_id;
            $transaction->status = 'ongoing';
            $transaction->save();
        }

        return back()->with('flashSuccess', 'コメントを送信しました！');
    }
}
