<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Transaction;
use App\Http\Requests\ReviewRequest;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Transaction $transaction)
{
    // --- 処理全体を try-catch で囲む ---
    try {
        // 二重評価チェック
        if (Review::where('transaction_id', $transaction->id)->where('reviewer_id', Auth::id())->exists()) {
            return response()->json(['error' => '既に評価済みです。'], 409);
        }

        $reviewedId = Auth::id() === $transaction->buyer_id
            ? $transaction->seller_id
            : $transaction->buyer_id;

        // レビュー作成
        Review::create([
            'transaction_id'=>$transaction->id,
            'reviewer_id'=>Auth::id(),
            'reviewed_id'=>$reviewedId,
            'rating'=>$request->rating,
        ]);

        $reviewCount = Review::where('transaction_id', $transaction->id)->count();

        if ($reviewCount === 2) {
            $transaction->update(['status'=>'completed']);
        }

        return response()->json(['success' => true, 'message' => '評価を送信しました']);

    } catch (\Exception $e) {
        // 🚨 ログにエラーを出力する
        \Log::error("レビュー作成中の致命的なエラー: " . $e->getMessage());
        \Log::error("トレース: " . $e->getTraceAsString());
        
        // クライアントには 500 エラーを返す
        return response()->json(['error' => 'サーバーで予期せぬエラーが発生しました。'], 500); 
    }
}
}