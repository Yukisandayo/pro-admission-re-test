<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Chat;
use App\Models\Transaction;
use App\Notifications\TransactionCompletedNotification;

class TransactionController extends Controller
{
    public function showChat(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->buyer_id &&     Auth::id() !== $transaction->seller_id) {
            abort(403);
        }

        $transaction->chats()
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $chats = $transaction->chats()->with('user')->get();

        return view('transactions.chat', compact('transaction', 'chats'));
    }

    public function complete(Request $request, $transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);

        if ($transaction->status === 'completed') {
            return redirect()->route('items.list')->with('error', 'この取引は既に完了しています。');
        }

        if (Auth::id() !== $transaction->buyer_id || $transaction->status !== 'ongoing') {
            return back()->with('error', 'この操作は許可されていません。');
        }

        $seller = $transaction->seller;

        if ($seller) {
            $seller->notify(new TransactionCompletedNotification($transaction));
        }

        return back()->with('completed', true)->with('flashSuccess', '取引が完了しました。評価をお願いします。');
    }

}
