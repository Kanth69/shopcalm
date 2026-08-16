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
                            <i class="bi bi-info-circle me-2"></i> Structured Corporate Page: Update hero messaging, mission statement, and strategic focus cards below.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Hero Title <span class="text-danger">*</span></label>
                            <input type="text" id="about_hero_title" class="form-control" value="{{ $aboutData['hero_title'] ?? 'Welcome to ShopCalm' }}" placeholder="e.g. Elevating Quality Commerce">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Corporate Tagline</label>
                            <input type="text" id="about_tagline" class="form-control" value="{{ $aboutData['tagline'] ?? '' }}" placeholder="e.g. Curated with Purpose & Precision">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Supporting Overview Text</label>
                            <textarea id="about_supporting_text" class="form-control" rows="3" placeholder="Overview paragraph shown below the hero...">{{ strip_tags(str_replace('</p>', "\n", $aboutData['supporting_text'] ?? '')) }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Our Mission & Core Philosophy</label>
                            <textarea id="about_mission" class="form-control" rows="3" placeholder="Mission statement...">{{ strip_tags(str_replace('</p>', "\n", $aboutData['mission'] ?? '')) }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold text-dark mb-3">Focus Areas & Pillars</h6>
                        <div id="about_focus_container">
                            @foreach($aboutData['focus_areas'] ?? [] as $index => $focus)
                                <div class="card bg-light border rounded-3 mb-3 focus-item p-3">
                                    <div class="mb-2">
                                        <label class="form-label small text-muted fw-bold">Pillar Title #{{ $index + 1 }}</label>
                                        <input type="text" class="form-control fw-bold focus-title" value="{{ $focus['title'] ?? '' }}" placeholder="Pillar Title">
                                    </div>
                                    <div>
                                        <label class="form-label small text-muted fw-bold">Pillar Description</label>
                                        <input type="text" class="form-control focus-desc" value="{{ $focus['desc'] ?? '' }}" placeholder="Description of this pillar...">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <textarea id="page-content-json" name="content" class="d-none">{{ old('content', $page->content) }}</textarea>

                    @elseif($page->slug === 'contact-us')
                        @php $contactData = json_decode(old('content', $page->content), true) ?: []; @endphp
                        
                        <div class="alert alert-primary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-info-circle me-2"></i> Contact Page Template: Customize hero headers and information panel prompts.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Hero Title</label>
                            <input type="text" id="contact_hero_title" class="form-control" value="{{ $contactData['hero_title'] ?? 'Get in Touch' }}">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Hero Subtitle</label>
                            <textarea id="contact_hero_subtitle" class="form-control" rows="2">{{ $contactData['hero_subtitle'] ?? 'Have a question or need assistance? We\'re here to help.' }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Left Panel Title</label>
                                <input type="text" id="contact_info_title" class="form-control" value="{{ $contactData['info_title'] ?? 'Contact Information' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Form Header Title</label>
                                <input type="text" id="contact_form_title" class="form-control" value="{{ $contactData['form_title'] ?? 'Send us a message' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small">Left Panel Description</label>
                                <textarea id="contact_info_subtitle" class="form-control" rows="2">{{ $contactData['info_subtitle'] ?? 'Fill up the form and our team will get back to you within 24 hours.' }}</textarea>
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
                                <input type="text" id="faq_hero_subtitle" class="form-control" value="{{ $faqData['hero_subtitle'] ?? 'Have questions? We\'re here to help you get the answers you need.' }}">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold text-dark mb-3">FAQ Questions & Answers</h6>
                        <div id="faq-blocks-container">
                            @for($i = 1; $i <= 30; $i++)
                                @php 
                                    $q = $faqData['faqs'][$i-1]['question'] ?? '';
                                    $a = $faqData['faqs'][$i-1]['answer'] ?? '';
                                    $isVisible = ($i <= 5 || $q !== '' || $a !== '');
                                @endphp
                                <div class="faq-section-block mb-3 p-3 border rounded-3 bg-light {{ $isVisible ? '' : 'd-none' }}" id="faq-block-{{ $i }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-white text-dark border font-monospace small">Q&A Item #{{ $i }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small text-muted fw-bold">Question</label>
                                        <input type="text" id="faq_{{ $i }}_question" class="form-control fw-bold" placeholder="e.g., Do you offer free shipping across India?" value="{{ $q }}">
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
                        @php $legalData = json_decode(old('content', $page->content), true) ?: []; @endphp
                        
                        <div class="alert alert-primary border-0 rounded-3 mb-4 small">
                            <i class="bi bi-shield-check me-2"></i> Structured Legal Sections: Enter your section headings and policy clauses below.
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">Preamble / Introductory Clause</label>
                            <textarea id="legal_intro" class="form-control" rows="3" placeholder="Opening clause or general policy overview...">{{ $legalData['intro'] ?? '' }}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold text-dark mb-3">Numbered Policy Clauses</h6>
                        @for($i = 1; $i <= 10; $i++)
                            <div class="legal-section-block mb-3 p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-white text-dark border font-monospace small">Clause Section #{{ $i }}</span>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small text-muted fw-bold">Section Heading</label>
                                    <input type="text" id="legal_section_{{ $i }}_title" class="form-control fw-bold" value="{{ $legalData['sections'][$i-1]['title'] ?? '' }}" placeholder="e.g. {{ $i }}. Eligibility & Scope">
                                </div>
                                <div>
                                    <label class="form-label small text-muted fw-bold">Clause Body</label>
                                    <textarea id="legal_section_{{ $i }}_content" class="form-control" rows="3" placeholder="Full terms for this clause...">{{ $legalData['sections'][$i-1]['content'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor
                        
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

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pageEditForm');
    const slug = '{{ $page->slug }}';

    form.addEventListener('submit', function(e) {
        const jsonTarget = document.getElementById('page-content-json');
        if (!jsonTarget) return;

        if (slug === 'about-us') {
            const focusAreas = [];
            document.querySelectorAll('.focus-item').forEach(item => {
                const title = item.querySelector('.focus-title')?.value.trim();
                const desc = item.querySelector('.focus-desc')?.value.trim();
                if (title || desc) {
                    focusAreas.push({ title: title || '', desc: desc || '' });
                }
            });

            const data = {
                hero_title: document.getElementById('about_hero_title')?.value.trim() || '',
                tagline: document.getElementById('about_tagline')?.value.trim() || '',
                supporting_text: document.getElementById('about_supporting_text')?.value.trim() || '',
                mission: document.getElementById('about_mission')?.value.trim() || '',
                focus_areas: focusAreas
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
            for (let i = 1; i <= 30; i++) {
                const q = document.getElementById(`faq_${i}_question`)?.value.trim();
                const a = document.getElementById(`faq_${i}_answer`)?.value.trim();
                if (q && a) {
                    faqs.push({ question: q, answer: a });
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
            for (let i = 1; i <= 10; i++) {
                const title = document.getElementById(`legal_section_${i}_title`)?.value.trim();
                const content = document.getElementById(`legal_section_${i}_content`)?.value.trim();
                if (title && content) {
                    sections.push({ title: title, content: content });
                }
            }
            const data = {
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
