<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Chat;
use App\Http\Requests\ChatRequest;

class ChatController extends Controller
{
    public function store(ChatRequest $request, Transaction $transaction)
    {
        $chat = $transaction->chats()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('chats','public');
                $chat->images()->create(['img_url'=>$path]);
            }
        }

        return back();
    }

    public function update(ChatRequest $request, Chat $chat)
    {
        $this->authorize('update',$chat);
        $chat->update(['message'=>$request->message]);
        return response()->json([
            'success' => true,
            'message' => $chat->message,
        ]);
    }

    public function destroy(Chat $chat)
    {
        $this->authorize('delete',$chat);
        $chat->delete();
        return response()->json(['success' => true]);
    }
}

