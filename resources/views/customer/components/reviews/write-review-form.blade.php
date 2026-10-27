<div id="write-review-container">
    @auth
        @php
            $userReview = $product->reviews()->where('user_id', Auth::id())->first();
        @endphp

        {{-- Wrapper if user already has a pending or approved review --}}
        <div id="user-review-status-wrapper" @if(!$userReview) style="display: none;" @endif>
            @if($userReview)
                @if($userReview->status === 'Pending')
                    <div class="text-center py-4" id="user-pending-review-box">
                        <div class="mx-auto mb-3" style="width: 56px; height: 56px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-clock-history fs-3" style="color: #d97706;"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1 fs-5">Thank You for Your Feedback!</h6>
                        <p class="text-muted small mb-3" style="max-width: 460px; margin: 0 auto;">
                            Your review for <strong>{{ $product->name }}</strong> has been submitted successfully and is pending approval.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                onclick="confirmDeleteUserReview({{ $userReview->id }}, '{{ route('reviews.destroy', $userReview) }}', this)">
                            <i class="bi bi-trash3 me-1"></i> Delete Review
                        </button>
                    </div>
                @elseif($userReview->status === 'Approved')
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-star-fill text-warning me-2"></i>Your Published Review</h6>
                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;">Active</span>
                    </div>
                    @include('customer.components.reviews.review-card', ['review' => $userReview])
                @endif
            @endif
        </div>

        {{-- Review Form: shown when no review exists or after deleting existing review --}}
        <div id="write-review-form-wrapper" @if($userReview) style="display: none;" @endif>
            <div class="border-bottom pb-2.5 mb-3">
                <h6 class="fw-bold text-dark mb-1 fs-6"><i class="bi bi-pencil-square text-primary me-2"></i>How would you rate this product?</h6>
                <p class="text-muted small mb-0">Share your genuine experience with other shoppers.</p>
            </div>

            <form id="write-review-form" action="{{ route('reviews.store', $product) }}" method="POST" onsubmit="handleReviewSubmit(event, this)" novalidate>
                @csrf
                
                {{-- Interactive Star Selector --}}
                <div class="mb-3 p-3 rounded-3 bg-light border text-center">
                    <label class="form-label fw-bold text-dark small mb-1 d-block">Overall Rating <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                        <div class="star-rating-picker d-inline-flex gap-1 p-1" id="star-picker" style="transition: border 0.2s ease;">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" id="star-{{ $i }}" name="rating" value="{{ $i }}" class="d-none">
                                <label for="star-{{ $i }}" class="star-item cursor-pointer user-select-none" data-value="{{ $i }}" style="font-size: clamp(2rem, 5vw, 2.5rem); color: #cbd5e1; transition: color 0.15s ease, transform 0.15s ease; padding: 0 4px; display: inline-block;">
                                    ★
                                </label>
                            @endfor
                        </div>
                    </div>
                    <div id="star-label-text" class="fw-bold small text-muted mt-1" style="min-height: 20px;">Select Rating</div>
                    <div id="rating-validation-feedback" class="text-danger small mt-1.5 fw-semibold d-none">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> Please tap a star to give a rating (1 to 5 stars).
                    </div>
                </div>

                {{-- Review Title --}}
                <div class="mb-3">
                    <label for="review_title" class="form-label fw-semibold text-dark small mb-1">Review Headline</label>
                    <input type="text" name="title" id="review_title" class="form-control rounded-3" placeholder="e.g., Excellent quality & fast shipping!" style="border-color: #cbd5e1; font-size:0.875rem;">
                </div>

                {{-- Review Body --}}
                <div class="mb-3">
                    <label for="review_body" class="form-label fw-semibold text-dark small mb-1">Detailed Feedback</label>
                    <textarea name="review" id="review_body" rows="3" class="form-control rounded-3" placeholder="What did you like or dislike about this product? How was the build quality and performance?" style="border-color: #cbd5e1; font-size:0.875rem;"></textarea>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                    <button type="button" class="btn btn-light rounded-pill px-3.5 fw-medium btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" id="btn-submit-review" class="btn btn-primary rounded-pill px-4 fw-bold btn-sm shadow-xs">
                        <i class="bi bi-send-fill me-1"></i> Submit Review
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="text-center py-4 bg-light rounded-4 p-3">
            <div class="mx-auto mb-3 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                <i class="bi bi-lock-fill fs-3"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">Sign in to rate this product</h6>
            <p class="text-muted small mb-3">Please log in to your account to share your verified review and feedback.</p>
            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-xs">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Review
            </a>
        </div>
    @endauth
</div>

<style>
.star-item:hover,
.star-item.active {
    color: #f59e0b !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starLabels = document.querySelectorAll('.star-item');
    const starLabelText = document.getElementById('star-label-text');
    const ratingFeedback = document.getElementById('rating-validation-feedback');
    const starPicker = document.getElementById('star-picker');
    const ratingTexts = {1: '1 Star - Poor', 2: '2 Stars - Fair', 3: '3 Stars - Good', 4: '4 Stars - Very Good', 5: '5 Stars - Excellent!'};

    starLabels.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const val = parseInt(this.getAttribute('data-value'));
            highlightStars(val);
            if (starLabelText) {
                starLabelText.textContent = ratingTexts[val] || '';
                starLabelText.classList.remove('text-muted');
                starLabelText.classList.add('text-primary');
            }
        });

        star.addEventListener('mouseleave', function() {
            const selected = document.querySelector('input[name="rating"]:checked');
            if (selected) {
                const val = parseInt(selected.value);
                highlightStars(val);
                if (starLabelText) {
                    starLabelText.textContent = ratingTexts[val] || '';
                    starLabelText.classList.remove('text-muted');
                    starLabelText.classList.add('text-primary');
                }
            } else {
                highlightStars(0);
                if (starLabelText) {
                    starLabelText.textContent = 'Select Rating';
                    starLabelText.classList.remove('text-primary');
                    starLabelText.classList.add('text-muted');
                }
            }
        });

        star.addEventListener('click', function() {
            const val = parseInt(this.getAttribute('data-value'));
            const radio = document.getElementById('star-' + val);
            if (radio) radio.checked = true;
            highlightStars(val);
            if (starLabelText) {
                starLabelText.textContent = ratingTexts[val] || '';
                starLabelText.classList.remove('text-muted');
                starLabelText.classList.add('text-primary');
            }
            if (ratingFeedback) ratingFeedback.classList.add('d-none');
            if (starPicker) starPicker.style.border = 'none';
        });
    });

    function highlightStars(val) {
        starLabels.forEach(star => {
            const sVal = parseInt(star.getAttribute('data-value'));
            if (sVal <= val) {
                star.style.color = '#f59e0b';
            } else {
                star.style.color = '#cbd5e1';
            }
        });
    }
});

function handleReviewSubmit(e, form) {
    e.preventDefault();

    const ratingChecked = form.querySelector('input[name="rating"]:checked');
    const ratingFeedback = document.getElementById('rating-validation-feedback');
    const starPicker = document.getElementById('star-picker');

    if (!ratingChecked || !ratingChecked.value) {
        if (ratingFeedback) {
            ratingFeedback.classList.remove('d-none');
        }
        if (starPicker) {
            starPicker.style.border = '1.5px solid #ef4444';
            starPicker.style.borderRadius = '8px';
            starPicker.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Rating Required',
                text: 'Please select a star rating between 1 and 5 stars.',
                confirmButtonColor: '#6366f1'
            });
        }
        return false;
    }

    const btn = document.getElementById('btn-submit-review');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) {
            let errorMsg = data.message || 'Failed to submit review.';
            if (data.errors && data.errors.rating) {
                errorMsg = data.errors.rating[0];
            }
            throw new Error(errorMsg);
        }
        return data;
    })
    .then(data => {
        const modalEl = document.getElementById('writeReviewModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Thank You!',
                text: data.message || 'Your review has been submitted successfully.',
                confirmButtonColor: '#6366f1',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            alert(data.message || 'Your review has been submitted successfully.');
            location.reload();
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Submit Review';
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Failed to submit review.'
            });
        } else {
            alert(err.message || 'Failed to submit review.');
        }
    });
}
</script>
