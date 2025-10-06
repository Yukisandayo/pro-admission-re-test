@extends('layouts.default')

@section('title','取引画面')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css')  }}">
<link rel="stylesheet" href="{{ asset('/css/chat.css')  }}">
@endsection

@section('content')
@include('components.header')

@php
    $currentUser = Auth::user();

    $buyerTransactions = $currentUser->transactionsAsBuyer;
    $sellerTransactions = $currentUser->transactionsAsSeller;

    $relatedTransactions = $buyerTransactions->merge($sellerTransactions)->unique('id')->sortByDesc('updated_at');
@endphp
<div class="main-layout-container">
    {{-- サイドバー（取引中の商品一覧） --}}
    <div class="chat-sidebar">
        <h3>その他の取引</h3>
        @foreach($relatedTransactions as $t)
            <a href="{{ route('transactions.chat',$t->id) }}"class="chat-sidebar__item {{ $transaction->id === $t->id ? 'active' : '' }}">
                <p class="chat-sidebar__name">{{ $t->item->name }}</p>
            </a>
        @endforeach
    </div>

    {{-- メインコンテンツエリア --}}
    <div class="main-content">
        @php
            $isReviewedByMe = \App\Models\Review::where('transaction_id', $transaction->id)
                                    ->where('reviewer_id', Auth::id())
                                    ->exists();
            $isBuyerReviewed = \App\Models\Review::where('transaction_id', $transaction->id)
                                    ->where('reviewer_id', $transaction->buyer_id)
                                    ->exists();
            $targetUser = (Auth::id() === $transaction->buyer_id) ? $transaction->seller : $transaction->buyer;
        @endphp
        <div class="chat-header">
            <div class="seller-user-info">
                <div class="seller-user-icon">
                    <img src="{{ $targetUser->profile->img_url ? Storage::url($targetUser->profile->img_url) : asset('img/icon.png') }}" alt="">
                </div>
            </div>
            <h1 class="chat-header__title">「{{ $targetUser->name }}」さんとの取引画面</h1>
            @if($transaction->status === 'ongoing')
                @if(Auth::id() === $transaction->buyer_id && !$isReviewedByMe)
                <form action="{{ route('transaction.complete', $transaction->id) }}" method="POST" class="chat-complete-form" id="completeTransactionForm">
                    @csrf
                    <button type="button" class="btn-complete" id="completeTransactionBtn">取引を完了する</button>
                </form>
                @elseif(Auth::id() === $transaction->seller_id && $isBuyerReviewed && !$isReviewedByMe)
                    <button type="button" class="btn-complete" id="completeTransactionBtn">評価する</button>
                @elseif(Auth::id() === $transaction->buyer_id && $isReviewedByMe)
                    <p></p>
                @elseif(Auth::id() === $transaction->seller_id && !$isBuyerReviewed)
                    <p></p>
                @endif
            @elseif($transaction->status === 'completed')
                <p></p>
            @endif
        </div>

        <div class="chat-container">
            <div class="chat-main">
                <div class="chat-main-header">
                    <div class="chat-main-header__item">
                        <div class="item__img--container">
                            <img src="{{ Storage::url($transaction->item->img_url) }}" class="item__img" alt="商品画像">
                        </div>
                        <div>
                            <p class="item__name">{{ $transaction->item->name }}</p>
                            <p class="item__price">{{ number_format($transaction->item->price) }}円</p>
                        </div>
                    </div>
                </div>

                <div class="chat-messages">
                    @foreach($chats as $chat)
                        <div class="chat-message {{ $chat->user_id == Auth::id() ? 'mine' : 'theirs' }}" data-id="{{ $chat->id }}">
                            <div class="chat-content-container">
                                <div class="chat-user-info">
                                    <div class="chat-user-icon">
                                        <img src="{{ $chat->user->profile->img_url ? Storage::url($chat->user->profile->img_url) : asset('img/icon.png') }}" alt="">
                                    </div>
                                    <span class="chat-username">{{ $chat->user->name }}</span>
                                </div>
                                <div class="chat-bubble-wrapper">
                                    <div class="chat-bubble">
                                        <p class="chat-text">{{ $chat->message }}</p>
                                        @if($chat->images->isNotEmpty())
                                            <div class="chat-images-container">
                                                @foreach($chat->images as $image)
                                                    <img src="{{ Storage::url($image->img_url) }}" alt="添付画像" class="chat-attached-image">
                                                @endforeach
                                            </div>
                                        @endif
                                        <form class="chat-edit-form" style="display:none;">
                                        @csrf
                                        <input type="text" name="message" class="chat-edit-input" value="{{ $chat->message }}">
                                        <button type="submit" class="btn-save">保存</button>
                                        <button type="button" class="btn-cancel">キャンセル</button>
                                        </form>
                                    </div>
                                    @if($chat->user_id == Auth::id())
                                        <div class="chat-actions">
                                            <button type="button" class="chat-edit">編集</button>
                                            <button type="button" class="chat-delete">削除</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- チャットフォーム --}}
                <div class="form__error">
                    @error('message_or_image_required')
                        {{ $message }}
                    @enderror
                </div>
                <div class="form__error">
                    @error('message')
                        {{ $message }}
                    @enderror
                </div>
                <div class="form__error">
                    @error('images')
                        {{ $message }}
                    @enderror
                </div>
                <div class="form__error">
                    @error('images.*')
                        {{ $message }}
                    @enderror
                </div>
                <form action="{{ route('chats.store',$transaction->id) }}" method="post" enctype="multipart/form-data" class="chat-form">
                    @csrf
                    <textarea name="message" id="chatMessageInput" placeholder="取引メッセージを入力してください">{{ old('message') }}</textarea>
                    <label class="btn-image-add">
                        画像を追加
                        <input type="file" name="images[]" multiple>
                    </label>
                    <button type="submit" class="btn-send">送信</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('transactions.review-modal')

<script>
    document.addEventListener('DOMContentLoaded', function(){
        @if(session('completed'))
            const modal = document.getElementById('reviewModal');
            if(modal){
                modal.style.display = 'flex';
            }
        @endif
    });

    // JavaScript for chat edit/delete functions
    document.addEventListener('DOMContentLoaded', function() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Edit and Cancel
        document.querySelectorAll('.chat-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const messageDiv = btn.closest('.chat-message');
                messageDiv.querySelector('.chat-text').style.display = 'none';
                messageDiv.querySelector('.chat-edit-form').style.display = 'flex';
            });
        });

        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = btn.closest('.chat-edit-form');
                const messageDiv = btn.closest('.chat-message');
                form.style.display = 'none';
                messageDiv.querySelector('.chat-text').style.display = 'block';
            });
        });

        // Edit Save (PUT request)
        document.querySelectorAll('.chat-edit-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const messageDiv = form.closest('.chat-message');
                const chatId = messageDiv.dataset.id;
                const newMessage = form.querySelector('.chat-edit-input').value;

                try {
                    const res = await fetch(`/chats/${chatId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message: newMessage })
                    });
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    messageDiv.querySelector('.chat-text').textContent = data.message;
                    form.style.display = 'none';
                    messageDiv.querySelector('.chat-text').style.display = 'block';
                } catch {
                    alert('更新に失敗しました');
                }
            });
        });

        // Delete (DELETE request)
        document.querySelectorAll('.chat-delete').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('削除しますか？')) return;
                const messageDiv = btn.closest('.chat-message');
                const chatId = messageDiv.dataset.id;
                try {
                    const res = await fetch(`/chats/${chatId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token }
                    });
                    if (!res.ok) throw new Error();
                    await res.json();
                    messageDiv.remove();
                } catch {
                    alert('削除に失敗しました');
                }
            });
        });

        // Transaction complete and review modal functionality
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('ratingInput');
        const submitBtn = document.getElementById('submitBtn');
        const closeModal = document.getElementById('closeReviewModal');
        const modal = document.getElementById('reviewModal');
        const completeBtn = document.getElementById('completeTransactionBtn');

        let selectedRating = 0;

        const transactionId = "{{ $transaction->id }}";
        const inputElement = document.getElementById('chatMessageInput');
        const storageKey = 'chatMessage_' + transactionId;

        if (inputElement) {
            const savedMessage = localStorage.getItem(storageKey);
            if (savedMessage) {
                inputElement.value = savedMessage;
            }

            inputElement.addEventListener('input', function () {
                localStorage.setItem(storageKey, inputElement.value);
            });

            const chatForm = document.querySelector('.chat-form');
            if (chatForm) {
                chatForm.addEventListener('submit', function() {
                    localStorage.removeItem(storageKey);
                });
            }
        }

        if (completeBtn) {
            // 取引完了ボタンクリック時にモーダルを表示
            completeBtn.addEventListener('click', function() {
                if ("{{ Auth::id() }}" === "{{ $transaction->buyer_id }}" && "{{ !$isReviewedByMe }}" === "1"){
                    document.getElementById('completeTransactionForm').submit();
                }
                else if ("{{ Auth::id() }}" === "{{ $transaction->seller_id }}" && "{{ $isBuyerReviewed }}" === "1" && "{{ !$isReviewedByMe }}" === "1") {
                    modal.style.display = 'flex';
                }
            });
        }

        if (stars.length > 0) {
            // Star click handling
            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.dataset.rating);
                    ratingInput.value = selectedRating;
                    updateStars(selectedRating);
                    submitBtn.disabled = false;
                });

                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const hoverRating = parseInt(this.dataset.rating);
                    updateStars(hoverRating);
                });
            });

            // Reset stars on mouse leave
            document.getElementById('starRating').addEventListener('mouseleave', function() {
                updateStars(selectedRating);
            });

            // Update star display
            function updateStars(rating) {
                stars.forEach((star, index) => {
                    const starRating = parseInt(star.dataset.rating);
                    if (starRating <= rating) {
                        star.classList.remove('empty');
                        star.classList.add('filled');
                    } else {
                        star.classList.remove('filled');
                        star.classList.add('empty');
                    }
                });
            }

            // Close modal
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    modal.style.display = 'none';
                    resetForm();
                });
            }

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    resetForm();
                }
            });

            // Reset form
            function resetForm() {
                selectedRating = 0;
                ratingInput.value = '';
                updateStars(0);
                submitBtn.disabled = true;
            }

// Form submission (評価送信後に取引完了処理を実行)
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (selectedRating > 0) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const formData = new FormData();
                formData.append('rating', selectedRating);

                fetch('{{ route("reviews.store", $transaction->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server responded with a non-200 status.');
                    }
                    return response.json();
                })
                .then(data => {
                    modal.style.display = 'none';
                    window.location.href = '{{ route("items.list") }}';
                })
                .catch(error => {
                    alert('評価の送信に失敗しました。');
                    console.error('Error:', error);
                });
            }
        });
        }
    });
</script>

@endsection