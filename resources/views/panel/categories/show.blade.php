@extends('layout.backend')
@push('title', 'Category')
@section('page-width', 'ff-page-narrow')

@section('content')
    @php $tags = is_array($category->categories) ? $category->categories : []; @endphp

    <div class="ff-breadcrumb">
        <a href="{{ route('panel.categories.index') }}">Categories</a>
        <span>/</span>
        <span class="is-current">{{ $category->title }}</span>
    </div>
    <h1 class="ff-h1 ff-h1-sm">{{ $category->title }}</h1>
    <p class="ff-sub">{{ $category->description ?: 'No description for this category.' }}</p>

    <div class="ff-split">
        <div class="ff-form-card">
            <div class="ff-section-head">
                <span class="ff-section-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                        <line x1="7" y1="7" x2="7.01" y2="7" />
                    </svg>
                </span>
                <div>
                    <div class="ff-section-title">Details</div>
                    <div class="ff-section-sub">How this collection is configured</div>
                </div>
            </div>

            <div class="ff-list">
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Type</span>
                    <span class="ff-badge-type">{{ ucfirst($category->type) }}</span>
                </div>
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Visibility</span>
                    <span class="ff-list-title">{{ $category->is_hidden ? 'Hidden' : 'Visible' }}</span>
                </div>
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Marked as new</span>
                    <span class="ff-list-title">{{ $category->is_new ? 'Yes' : 'No' }}</span>
                </div>
                <div class="ff-list-row">
                    <span class="ff-grow ff-soft" style="font-size:13.5px;">Created</span>
                    <span class="ff-list-title">{{ $category->created_at->format('M j, Y') }}</span>
                </div>
            </div>

            <div class="ff-field">
                <label class="ff-label">Tags</label>
                <div class="ff-row" style="gap:8px; flex-wrap:wrap;">
                    @forelse ($tags as $tag)
                        <span class="ff-badge-hidden">{{ $tag }}</span>
                    @empty
                        <span class="ff-hint">No tags on this category.</span>
                    @endforelse
                </div>
            </div>

            <div class="ff-divider"></div>
            <div class="ff-form-actions">
                <a href="{{ route('panel.categories.index') }}" class="ff-btn">Back</a>
                <a href="{{ route('panel.categories.edit', $category) }}" class="ff-btn ff-btn-primary">Edit Category</a>
            </div>
        </div>

        <div class="ff-preview-card ff-hide-mobile">
            <div class="ff-preview-label">Thumbnail</div>
            <div class="ff-preview-thumb">
                @php
                    $thumbSrc = null;
                    if ($category->thumbnail) {
                        $thumbSrc = \Illuminate\Support\Str::startsWith($category->thumbnail, ['http://', 'https://', '//', 'data:'])
                            ? $category->thumbnail
                            : asset($category->thumbnail);
                    }
                @endphp
                @if ($thumbSrc)
                    <img src="{{ $thumbSrc }}" alt="{{ $category->title }}" loading="lazy">
                @else
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <path d="M21 15l-5-5L5 21" />
                    </svg>
                @endif
            </div>
            <div class="ff-row" style="gap:8px; margin-top:14px;">
                <div style="font-size:15.5px; font-weight:700; color:var(--ff-text);">{{ $category->title }}</div>
                @if ($category->is_new)
                    <span class="ff-badge-new">New</span>
                @endif
            </div>
            <div style="font-size:13px; color:var(--ff-text-2); margin-top:6px; line-height:1.5;">
                {{ $category->description ?: 'No description.' }}
            </div>
        </div>
    </div>
@endsection

@section('push-script')
    <script>
        window.ff.icons();
    </script>
@endsection
