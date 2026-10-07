@extends('admin.layouts.app')

@section('header', 'Edit Content Page: ' . $page->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $page->title }}</li>
@endsection

@section('actions')
    <div class="btn-group gap-2">
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Pages
        </a>
        <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Live
        </a>
    </div>
@endsection

@section('content')
<form action="{{ route('admin.pages.update', $page) }}" method="POST" id="pageEditForm">
    @csrf
    @method('PUT')
    
    <div class="row g-4">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Page Content Editor
                    </h6>
                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace small">
                        /{{ $page->slug }}
                    </span>
                </div>
                <div class="card-body p-4">
                    @if($page->slug === 'about-us')
                        @php $aboutData = json_decode(old('content', $page->content), true) ?: []; @endphp
                        
                        <div class="alert alert-primary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-info-circle me-2"></i> Structured Corporate Page: Update hero messaging, stats counters, company story, mission, customer commitments, and service focus pillars below.
                        </div>

                        <!-- 1. Hero & Tagline -->
                        <div class="card border rounded-3 p-3 mb-4 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-megaphone text-primary me-2"></i>Hero & Introduction</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">Hero Title <span class="text-danger">*</span></label>
                                <input type="text" id="about_hero_title" class="form-control" value="{{ $aboutData['hero_title'] ?? 'About ShopCalm' }}" placeholder="e.g. About ShopCalm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">Corporate Tagline</label>
                                <input type="text" id="about_tagline" class="form-control" value="{{ $aboutData['tagline'] ?? 'Simple. Transparent. Trustworthy.' }}" placeholder="e.g. Simple. Transparent. Trustworthy.">
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold text-dark small">Supporting Overview Text</label>
                                <textarea id="about_supporting_text" class="form-control" rows="3" placeholder="Overview paragraph shown below the hero...">{{ $aboutData['supporting_text'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <!-- 2. Core Stats Counters -->
                        <div class="card border rounded-3 p-3 mb-4 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-fill text-success me-2"></i>Key Performance Metrics & Stats (4 Counters)</h6>
                            <div class="row g-3">
                                @php $stats = $aboutData['stats'] ?? []; @endphp
                                @for($s = 0; $s < 4; $s++)
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-2 bg-light">
                                            <div class="fw-bold small text-muted mb-2">Counter #{{ $s + 1 }}</div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted">Value (e.g. 50K+)</label>
                                                    <input type="text" id="about_stat_{{ $s }}_val" class="form-control form-control-sm fw-bold" value="{{ $stats[$s]['value'] ?? '' }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted">Label (e.g. Happy Customers)</label>
                                                    <input type="text" id="about_stat_{{ $s }}_lbl" class="form-control form-control-sm" value="{{ $stats[$s]['label'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- 3. Our Story & Journey -->
                        <div class="card border rounded-3 p-3 mb-4 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-compass-fill text-primary me-2"></i>Our Story & Journey</h6>
                            @php $story = $aboutData['our_story'] ?? []; @endphp
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark">Section Badge</label>
                                    <input type="text" id="about_story_badge" class="form-control form-control-sm" value="{{ $story['badge'] ?? 'Our Journey' }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-dark">Heading Title</label>
                                    <input type="text" id="about_story_title" class="form-control form-control-sm" value="{{ $story['title'] ?? 'How ShopCalm Came to Life' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark">Subtitle Tagline</label>
                                    <input type="text" id="about_story_subtitle" class="form-control form-control-sm" value="{{ $story['subtitle'] ?? 'Born out of the desire to eliminate shopping anxiety and clutter.' }}">
                                </div>
                            </div>

                            <label class="form-label small fw-bold text-dark mb-1">Story Paragraphs (3 Paragraphs)</label>
                            @php $storyParas = $story['paragraphs'] ?? []; @endphp
                            <div class="mb-3">
                                <textarea id="about_story_p1" class="form-control form-control-sm mb-2" rows="2" placeholder="Paragraph 1...">{{ $storyParas[0] ?? '' }}</textarea>
                                <textarea id="about_story_p2" class="form-control form-control-sm mb-2" rows="2" placeholder="Paragraph 2...">{{ $storyParas[1] ?? '' }}</textarea>
                                <textarea id="about_story_p3" class="form-control form-control-sm" rows="2" placeholder="Paragraph 3...">{{ $storyParas[2] ?? '' }}</textarea>
                            </div>

                            <div class="p-3 border rounded-2 bg-light">
                                <div class="fw-bold small text-dark mb-2">Right Callout Box</div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Box Title</label>
                                        <input type="text" id="about_story_quote_title" class="form-control form-control-sm" value="{{ $story['quote_title'] ?? 'Built for Peace of Mind' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Guarantee Badge</label>
                                        <input type="text" id="about_story_guarantee_badge" class="form-control form-control-sm" value="{{ $story['guarantee_badge'] ?? 'Customer-First Guarantee' }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-muted">Guiding Question / Quote</label>
                                        <textarea id="about_story_quote_text" class="form-control form-control-sm" rows="2">{{ $story['quote_text'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Mission Statement -->
                        <div class="card border rounded-3 p-3 mb-4 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bullseye text-danger me-2"></i>Our Mission & Core Philosophy</h6>
                            <textarea id="about_mission" class="form-control" rows="3" placeholder="Mission statement HTML or text...">{{ $aboutData['mission'] ?? '' }}</textarea>
                        </div>

                        <!-- 5. 4 Core Customer Commitments -->
                        <div class="card border rounded-3 p-3 mb-4 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock-fill text-success me-2"></i>Our 4 Core Customer Commitments</h6>
                            @php $commitments = $aboutData['trust_commitments'] ?? []; @endphp
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Section Title</label>
                                    <input type="text" id="about_commitments_title" class="form-control form-control-sm" value="{{ $commitments['title'] ?? 'Our 4 Core Customer Commitments' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Section Subtitle</label>
                                    <input type="text" id="about_commitments_subtitle" class="form-control form-control-sm" value="{{ $commitments['subtitle'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row g-3">
                                @php $cItems = $commitments['items'] ?? []; @endphp
                                @for($c = 0; $c < 4; $c++)
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-2 bg-light">
                                            <div class="fw-bold small text-muted mb-2">Commitment #{{ $c + 1 }}</div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted">Title</label>
                                                <input type="text" id="about_commit_{{ $c }}_title" class="form-control form-control-sm fw-bold" value="{{ $cItems[$c]['title'] ?? '' }}">
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted">Description</label>
                                                <input type="text" id="about_commit_{{ $c }}_desc" class="form-control form-control-sm" value="{{ $cItems[$c]['desc'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- 6. Focus Areas & Service Standards -->
                        <div class="card border rounded-3 p-3 mb-4 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-award-fill text-primary me-2"></i>Focus Areas / Service Pillars (6 Items)</h6>
                            <div class="row g-3" id="about_focus_container">
                                @php $focusList = $aboutData['focus_areas'] ?? []; @endphp
                                @for($f = 0; $f < 6; $f++)
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-2 bg-light focus-item h-100">
                                            <div class="fw-bold small text-muted mb-2">Pillar #{{ $f + 1 }}</div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted">Title</label>
                                                <input type="text" class="form-control form-control-sm fw-bold focus-title" value="{{ $focusList[$f]['title'] ?? '' }}" placeholder="e.g. Curated Authenticity">
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted">Description</label>
                                                <textarea class="form-control form-control-sm focus-desc" rows="2" placeholder="Description of this pillar...">{{ $focusList[$f]['desc'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- 7. Call To Action -->
                        <div class="card border rounded-3 p-3 mb-3 bg-white shadow-2xs">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Bottom Call to Action Banner</h6>
                            @php $cta = $aboutData['cta'] ?? []; @endphp
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">CTA Title</label>
                                    <input type="text" id="about_cta_title" class="form-control form-control-sm" value="{{ $cta['title'] ?? 'Ready to Experience Great Shopping?' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">CTA Subtitle</label>
                                    <input type="text" id="about_cta_subtitle" class="form-control form-control-sm" value="{{ $cta['subtitle'] ?? 'Discover thousands of verified products with fast delivery and guaranteed satisfaction.' }}">
                                </div>
                            </div>
                        </div>

                        <textarea id="page-content-json" name="content" class="d-none">{{ old('content', $page->content) }}</textarea>

                    @elseif($page->slug === 'contact-us')
                        @php $contactData = json_decode(old('content', $page->content), true) ?: []; @endphp
                        
                        <div class="alert alert-primary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-info-circle me-2"></i> Contact Page Template: Customize hero headers, support prompts, and contact form titles.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Hero Title</label>
                            <input type="text" id="contact_hero_title" class="form-control" value="{{ $contactData['hero_title'] ?? 'Get in Touch with ShopCalm' }}" placeholder="e.g. Get in Touch with ShopCalm">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Hero Subtitle</label>
                            <textarea id="contact_hero_subtitle" class="form-control" rows="2">{{ $contactData['hero_subtitle'] ?? 'Have questions about your order, tracking, or products? Our dedicated support team is here to assist you 24/7.' }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Left Panel Title</label>
                                <input type="text" id="contact_info_title" class="form-control" value="{{ $contactData['info_title'] ?? 'Customer Care & Support' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Form Header Title</label>
                                <input type="text" id="contact_form_title" class="form-control" value="{{ $contactData['form_title'] ?? 'Send us a message' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Left Panel Description</label>
                                <textarea id="contact_info_subtitle" class="form-control" rows="2">{{ $contactData['info_subtitle'] ?? 'Reach out through any channel below. Our customer support agents respond promptly.' }}</textarea>
                            </div>
                        </div>
                        
                        <textarea id="page-content-json" name="content" class="d-none">{{ old('content', $page->content) }}</textarea>

                    @elseif($page->slug === 'faq')
                        @php $faqData = json_decode(old('content', $page->content), true) ?: []; @endphp
                        
                        <div class="alert alert-primary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-patch-question me-2"></i> FAQ Accordion Builder: Fill in questions & answers. Blank questions will be automatically hidden.
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Hero Title</label>
                                <input type="text" id="faq_hero_title" class="form-control" value="{{ $faqData['hero_title'] ?? 'Frequently Asked Questions' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Hero Subtitle</label>
                                <input type="text" id="faq_hero_subtitle" class="form-control" value="{{ $faqData['hero_subtitle'] ?? 'Find quick answers regarding shipping timelines, order tracking, payment methods, and authenticity guarantee.' }}">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold text-dark mb-3">FAQ Questions & Answers</h6>
                        <div id="faq-blocks-container">
                            @for($i = 1; $i <= 30; $i++)
                                @php 
                                    $q = $faqData['faqs'][$i-1]['question'] ?? '';
                                    $a = $faqData['faqs'][$i-1]['answer'] ?? '';
                                    $cat = $faqData['faqs'][$i-1]['category'] ?? 'General';
                                    $isVisible = ($i <= 8 || $q !== '' || $a !== '');
                                @endphp
                                <div class="faq-section-block mb-3 p-3 border rounded-3 bg-light {{ $isVisible ? '' : 'd-none' }}" id="faq-block-{{ $i }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-white text-dark border font-monospace small">Q&A Item #{{ $i }}</span>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-8">
                                            <label class="form-label small text-muted fw-bold">Question</label>
                                            <input type="text" id="faq_{{ $i }}_question" class="form-control fw-bold" placeholder="e.g., How long does shipping usually take?" value="{{ $q }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted fw-bold">Topic Category</label>
                                            <select id="faq_{{ $i }}_category" class="form-select">
                                                <option value="Shipping & Delivery" {{ $cat === 'Shipping & Delivery' ? 'selected' : '' }}>🚚 Shipping & Delivery</option>
                                                <option value="Returns & Quality" {{ $cat === 'Returns & Quality' ? 'selected' : '' }}>🛡️ Returns & Quality</option>
                                                <option value="Orders & Tracking" {{ $cat === 'Orders & Tracking' ? 'selected' : '' }}>📦 Orders & Tracking</option>
                                                <option value="Payments & COD" {{ $cat === 'Payments & COD' ? 'selected' : '' }}>💳 Payments & COD</option>
                                                <option value="General" {{ ($cat === 'General' || empty($cat)) ? 'selected' : '' }}>❓ General Inquiry</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small text-muted fw-bold">Answer</label>
                                        <textarea id="faq_{{ $i }}_answer" class="form-control" rows="3" placeholder="Detailed answer...">{{ $a }}</textarea>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="text-center my-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill btn-sm px-3" onclick="showNextFaqBlock()">
                                <i class="bi bi-plus-circle me-1"></i> Add Another FAQ Item
                            </button>
                        </div>
                        
                        <textarea id="page-content-json" name="content" class="d-none">{{ old('content', $page->content) }}</textarea>

                    @elseif(in_array($page->slug, ['terms-and-conditions', 'privacy-policy', 'shipping-policy', 'return-refund-policy', 'cancellation-policy']))
                        @php 
                            $legalData = json_decode(old('content', $page->content), true) ?: []; 
                            $sections = $legalData['sections'] ?? [];
                        @endphp
                        
                        <div class="alert alert-primary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-shield-check me-2"></i> Structured Legal Sections: Enter your section headings and policy clauses below. Unused clause blocks will be omitted automatically.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Policy Hero Subtitle / Summary Tagline</label>
                            <input type="text" id="legal_desc" class="form-control" value="{{ $legalData['desc'] ?? '' }}" placeholder="e.g. Rules, policies, and guidelines for using our store services.">
                            <div class="form-text small text-muted">Displayed under the main policy title on the storefront.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Preamble / Introductory Clause (Overview & Applicability)</label>
                            <textarea id="legal_intro" class="form-control" rows="4" placeholder="Opening clause or general policy overview...">{{ $legalData['intro'] ?? '' }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold text-dark mb-3">Numbered Policy Clauses</h6>
                        <div id="legal-clauses-container">
                            @for($i = 1; $i <= 20; $i++)
                                @php 
                                    $cTitle = $sections[$i-1]['title'] ?? '';
                                    $cContent = $sections[$i-1]['content'] ?? '';
                                    $isClauseVisible = ($i <= max(3, count($sections)) || !empty($cTitle) || !empty($cContent));
                                @endphp
                                <div class="legal-section-block mb-3 p-3 border rounded-3 bg-light {{ $isClauseVisible ? '' : 'd-none' }}" id="legal-block-{{ $i }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-white text-dark border font-monospace small">Clause Section #{{ $i }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small text-muted fw-bold">Section Heading</label>
                                        <input type="text" id="legal_section_{{ $i }}_title" class="form-control fw-bold" value="{{ $cTitle }}" placeholder="e.g. {{ $i }}. Eligibility & Scope">
                                    </div>
                                    <div>
                                        <label class="form-label small text-muted fw-bold">Clause Body</label>
                                        <textarea id="legal_section_{{ $i }}_content" class="form-control" rows="4" placeholder="Full terms for this clause...">{{ $cContent }}</textarea>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="text-center my-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill btn-sm px-3" onclick="showNextLegalBlock()">
                                <i class="bi bi-plus-circle me-1"></i> Add Another Policy Clause
                            </button>
                        </div>
                        
                        <textarea id="page-content-json" name="content" class="d-none">{{ old('content', $page->content) }}</textarea>

                    @else
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">HTML / Markdown Content</label>
                            <textarea name="content" class="form-control" rows="15" required>{{ old('content', $page->content) }}</textarea>
                        </div>
                    @endif

                    @error('content') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
            
            <!-- SEO Settings Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-search text-primary me-2"></i>SEO & Meta Tag Configuration</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $page->meta_title) }}" placeholder="e.g. Terms of Service | ShopCalm">
                        @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Meta Description</label>
                        <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3" placeholder="Short description for search engine listings...">{{ old('meta_description', $page->meta_description) }}</textarea>
                        @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders text-primary me-2"></i>Publishing Settings</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Live Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark small" for="isActive">Published & Active</label>
                        </div>
                    </div>
                    
                    <hr class="my-3">

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Permanent URL Path</label>
                        <input type="text" class="form-control bg-light text-muted font-monospace small" value="/{{ $page->slug }}" readonly disabled>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Core system route cannot be renamed.</div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-light rounded-pill border">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function showNextFaqBlock() {
    const hiddenBlocks = document.querySelectorAll('.faq-section-block.d-none');
    if (hiddenBlocks.length > 0) {
        hiddenBlocks[0].classList.remove('d-none');
        hiddenBlocks[0].querySelector('input')?.focus();
    } else {
        alert('All 30 FAQ slots are active.');
    }
}

function showNextLegalBlock() {
    const hiddenBlocks = document.querySelectorAll('.legal-section-block.d-none');
    if (hiddenBlocks.length > 0) {
        hiddenBlocks[0].classList.remove('d-none');
        hiddenBlocks[0].querySelector('input')?.focus();
    } else {
        alert('All 20 clause slots are active.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pageEditForm');
    const slug = '{{ $page->slug }}';

    form.addEventListener('submit', function(e) {
        const jsonTarget = document.getElementById('page-content-json');
        if (!jsonTarget) return;

        if (slug === 'about-us') {
            // Stats
            const stats = [];
            for (let s = 0; s < 4; s++) {
                const val = document.getElementById(`about_stat_${s}_val`)?.value.trim();
                const lbl = document.getElementById(`about_stat_${s}_lbl`)?.value.trim();
                const colors = ['#6366f1', '#10b981', '#0284c7', '#f59e0b'];
                if (val || lbl) {
                    stats.push({ value: val || '', label: lbl || '', color: colors[s] || '#6366f1' });
                }
            }

            // Story Paragraphs
            const p1 = document.getElementById('about_story_p1')?.value.trim();
            const p2 = document.getElementById('about_story_p2')?.value.trim();
            const p3 = document.getElementById('about_story_p3')?.value.trim();
            const paragraphs = [p1, p2, p3].filter(p => !!p);

            // Our Story Object
            const ourStory = {
                badge: document.getElementById('about_story_badge')?.value.trim() || 'Our Journey',
                title: document.getElementById('about_story_title')?.value.trim() || '',
                subtitle: document.getElementById('about_story_subtitle')?.value.trim() || '',
                paragraphs: paragraphs,
                quote_title: document.getElementById('about_story_quote_title')?.value.trim() || '',
                quote_text: document.getElementById('about_story_quote_text')?.value.trim() || '',
                guarantee_badge: document.getElementById('about_story_guarantee_badge')?.value.trim() || ''
            };

            // Customer Commitments Items
            const commitItems = [];
            const commitIcons = ['bi-patch-check-fill', 'bi-tag-fill', 'bi-shield-fill-check', 'bi-arrow-clockwise'];
            const commitColors = ['#10b981', '#6366f1', '#06b6d4', '#8b5cf6'];
            for (let c = 0; c < 4; c++) {
                const cTitle = document.getElementById(`about_commit_${c}_title`)?.value.trim();
                const cDesc = document.getElementById(`about_commit_${c}_desc`)?.value.trim();
                if (cTitle || cDesc) {
                    commitItems.push({
                        title: cTitle || '',
                        desc: cDesc || '',
                        icon: commitIcons[c] || 'bi-patch-check-fill',
                        color: commitColors[c] || '#6366f1'
                    });
                }
            }

            const trustCommitments = {
                badge: 'Customer Guarantee',
                title: document.getElementById('about_commitments_title')?.value.trim() || 'Our 4 Core Customer Commitments',
                subtitle: document.getElementById('about_commitments_subtitle')?.value.trim() || '',
                items: commitItems
            };

            // Focus Areas
            const focusAreas = [];
            document.querySelectorAll('#about_focus_container .focus-item').forEach(item => {
                const title = item.querySelector('.focus-title')?.value.trim();
                const desc = item.querySelector('.focus-desc')?.value.trim();
                if (title || desc) {
                    focusAreas.push({ title: title || '', desc: desc || '' });
                }
            });

            // CTA
            const cta = {
                title: document.getElementById('about_cta_title')?.value.trim() || 'Ready to Experience Great Shopping?',
                subtitle: document.getElementById('about_cta_subtitle')?.value.trim() || 'Discover thousands of verified products with fast delivery and guaranteed satisfaction.'
            };

            const data = {
                hero_title: document.getElementById('about_hero_title')?.value.trim() || '',
                tagline: document.getElementById('about_tagline')?.value.trim() || '',
                supporting_text: document.getElementById('about_supporting_text')?.value.trim() || '',
                stats: stats,
                our_story: ourStory,
                mission: document.getElementById('about_mission')?.value.trim() || '',
                trust_commitments: trustCommitments,
                focus_areas: focusAreas,
                cta: cta
            };
            jsonTarget.value = JSON.stringify(data);

        } else if (slug === 'contact-us') {
            const data = {
                hero_title: document.getElementById('contact_hero_title')?.value.trim() || '',
                hero_subtitle: document.getElementById('contact_hero_subtitle')?.value.trim() || '',
                info_title: document.getElementById('contact_info_title')?.value.trim() || '',
                info_subtitle: document.getElementById('contact_info_subtitle')?.value.trim() || '',
                form_title: document.getElementById('contact_form_title')?.value.trim() || ''
            };
            jsonTarget.value = JSON.stringify(data);

        } else if (slug === 'faq') {
            const faqs = [];
            const categoryIconMap = {
                'Shipping & Delivery': 'bi-truck',
                'Returns & Quality': 'bi-patch-check',
                'Orders & Tracking': 'bi-box-seam',
                'Payments & COD': 'bi-credit-card-2-front',
                'General': 'bi-question-circle'
            };
            for (let i = 1; i <= 30; i++) {
                const q = document.getElementById(`faq_${i}_question`)?.value.trim();
                const a = document.getElementById(`faq_${i}_answer`)?.value.trim();
                const cat = document.getElementById(`faq_${i}_category`)?.value.trim() || 'General';
                if (q && a) {
                    faqs.push({
                        category: cat,
                        icon: categoryIconMap[cat] || 'bi-question-circle',
                        question: q,
                        answer: a
                    });
                }
            }
            const data = {
                hero_title: document.getElementById('faq_hero_title')?.value.trim() || 'Frequently Asked Questions',
                hero_subtitle: document.getElementById('faq_hero_subtitle')?.value.trim() || '',
                faqs: faqs
            };
            jsonTarget.value = JSON.stringify(data);

        } else if (['terms-and-conditions', 'privacy-policy', 'shipping-policy', 'return-refund-policy', 'cancellation-policy'].includes(slug)) {
            const sections = [];
            for (let i = 1; i <= 20; i++) {
                const title = document.getElementById(`legal_section_${i}_title`)?.value.trim();
                const content = document.getElementById(`legal_section_${i}_content`)?.value.trim();
                if (title && content) {
                    sections.push({ title: title, content: content });
                }
            }
            const data = {
                desc: document.getElementById('legal_desc')?.value.trim() || '',
                intro: document.getElementById('legal_intro')?.value.trim() || '',
                sections: sections
            };
            jsonTarget.value = JSON.stringify(data);
        }
    });
});
</script>
@endpush
@endsection
