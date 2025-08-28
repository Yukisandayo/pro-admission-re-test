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
        $reviewedId = Auth::id() === $transaction->buyer_id
            ? $transaction->seller_id
            : $transaction->buyer_id;

        Review::create([
            'transaction_id'=>$transaction->id,
            'reviewer_id'=>Auth::id(),
            'reviewed_id'=>$reviewedId,
            'rating'=>$request->rating,
        ]);

        if ($transaction->reviews()->count() >= 2) {
            $transaction->update(['status'=>'completed']);
        }

        return response()->json(['success' => true, 'message' => '評価を送信しました']);
    }
}

