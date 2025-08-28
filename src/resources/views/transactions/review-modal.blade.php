<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span id="closeReviewModal" class="modal-close">&times;</span>
        <h2 class="modal-title">取引が完了しました。</h2>
        <p class="modal-subtitle">今回の取引相手はどうでしたか？</p>

        <form action="{{ route('reviews.store', $transaction->id) }}" method="post" id="reviewForm">
            @csrf
            <div class="stars-container">
                <div class="stars" id="starRating">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="star empty" data-rating="{{ $i }}">★</span>
                    @endfor
                </div>
                <input type="hidden" id="ratingInput" name="rating" value="">
            </div>
            <button type="submit" class="btn-submit" id="submitBtn" disabled>送信する</button>
        </form>
    </div>
</div>