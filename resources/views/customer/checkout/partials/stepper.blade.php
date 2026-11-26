<!-- Modern 3-Step Guided Progress Bar (1. Delivery -> 2. Offers & Savings -> 3. Payment & Place Order) -->
<div class="row justify-content-center mb-4">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="d-flex align-items-center justify-content-between position-relative px-3 px-sm-5">
            <!-- Progress Track strictly constrained between Step 1 and Step 3 centers -->
            <div class="position-absolute" style="top: 19px; left: 16%; right: 16%; height: 2px; background: #e2e8f0; z-index: 0; overflow: hidden;">
                <!-- Active Animated Progress Line inside track -->
                <div id="stepper-progress-line" style="width: 0%; height: 100%; background: #4f46e5; transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);"></div>
            </div>

            <!-- Step 1: Delivery -->
            <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-1" onclick="goToStep(1)" style="z-index: 2;">
                <div class="checkout-step-badge text-white shadow-xs" id="stepper-badge-1" style="background: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2) !important;">
                    1
                </div>
                <span class="fw-bold text-primary mt-1.5 text-center" id="stepper-text-1" style="font-size: 0.8rem; line-height: 1.2;">1. Delivery Info</span>
            </div>

            <!-- Step 2: Offers & Savings -->
            <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-2" onclick="goToStep(2)" style="z-index: 2;">
                <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-2">
                    2
                </div>
                <span class="fw-semibold text-muted mt-1.5 text-center" id="stepper-text-2" style="font-size: 0.8rem; line-height: 1.2;">2. Offers &amp; Savings</span>
            </div>

            <!-- Step 3: Payment & Place Order -->
            <div class="d-flex flex-column align-items-center position-relative cursor-pointer stepper-tab-item" id="stepper-tab-3" onclick="goToStep(3)" style="z-index: 2;">
                <div class="checkout-step-badge bg-light text-muted border" id="stepper-badge-3">
                    3
                </div>
                <span class="fw-semibold text-muted mt-1.5 text-center" id="stepper-text-3" style="font-size: 0.8rem; line-height: 1.2;">3. Payment &amp; Place</span>
            </div>
        </div>
    </div>
</div>
