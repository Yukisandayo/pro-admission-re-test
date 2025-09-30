<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Chat;
use App\Models\ChatImage;
use App\Http\Requests\ChatRequest;

class ChatController extends Controller
{
    public function store(ChatRequest $request, Transaction $transaction)
    {
        $uploadedPaths = [];

        try {
            DB::transaction(function () use ($request, $transaction, &$uploadedPaths) {
                $chat = $transaction->chats()->create([
                    'user_id' => Auth::id(),
                    'message' => $request->message,
                    'is_read' => false,
                ]);

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $path = $file->store('chats', 'public');
                        $uploadedPaths[] = $path;

                        $chat->images()->create([
                            'img_url' => $path
                        ]);
                    }
                }
            });

            return back()->with('flashSuccess', 'メッセージを送信しました。');

        } catch (\Exception $e) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            \Log::error("チャット/画像保存エラー: " . $e->getMessage());

            return back()->with('error', 'メッセージの送信に失敗しました。時間をおいて再度お試しください。');
        }
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