@extends('layouts.default')

<!-- タイトル -->
@section('title','マイページ')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css')  }}" >
<link rel="stylesheet" href="{{ asset('/css/mypage.css')  }}" >
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="container">
    <div class="user">
        <div class="user__info">
            <div class="user__img">
                @if (isset($user->profile->img_url))
                    <img class="user__icon" src="{{ \Storage::url($user->profile->img_url) }}" alt="">
                @else
                    <img id="myImage" class="user__icon" src="{{ asset('img/icon.png') }}" alt="">
                @endif
            </div>
            <div class="user__text">
                <p class="user__name">{{$user->name}}</p>
                @php
                    $avg = round($user->averageRating() ?? 0);
                @endphp
                <div class="user__rating">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $avg)
                            <span class="star filled">★</span>
                        @else
                            <span class="star">☆</span>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
        <div class="mypage__user--btn">
        <a class="btn2" href="/mypage/profile">プロフィールを編集</a>
        </div>
    </div>
    <div class="border">
        <ul class="border__list">
            <li><a href="/mypage?page=sell" class="{{ (request('page') === 'sell' || !request('page')) ? 'active' : '' }}">出品した商品</a></li>
            <li><a href="/mypage?page=buy" class="{{ request('page') === 'buy' ? 'active' : '' }}">購入した商品</a></li>
            <li><a href="/mypage?page=ongoing" class="{{ request('page') === 'ongoing' ? 'active' : '' }}">取引中の商品</a>
            @if($totalUnread > 0)
                <span class="badge">{{$totalUnread}}</span>
            @endif
            </li>
        </ul>
    </div>
    <div class="items">
    @if(request()->page === 'ongoing')
        @foreach($transactions as $transaction)
            <div class="item">
                <a href="{{ route('transactions.chat', $transaction->id) }}">
                    <div class="item__img--container">
                        <img src="{{ \Storage::url($transaction->item->img_url) }}" class="item__img" alt="商品画像">
                        @if($transaction->unread_count > 0)
                            <span class="badge badge--img">{{ $transaction->unread_count }}</span>
                        @endif
                    </div>
                    <p class="item__name">{{ $transaction->item->name }}</p>
                </a>
            </div>
        @endforeach
    @else
        @foreach ($items as $item)
            <div class="item">
                <a href="/item/{{$item->id}}">
                    <div class="item__img--container {{ $item->sold() ? 'sold' : '' }}">
                        <img src="{{ \Storage::url($item->img_url) }}" class="item__img" alt="商品画像">
                        @if(request()->page === 'ongoing' && isset($item->unread_count) && $item->unread_count > 0)
                            <span class="badge badge--img">{{$item->unread_count}}</span>
                        @endif
                    </div>
                    <p class="item__name">{{$item->name}}</p>
                </a>
            </div>
        @endforeach
    @endif
</div>
</div>
@endsection
