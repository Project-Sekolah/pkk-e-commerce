@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header & Search -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <h2 class="fw-bold mb-0">Koleksi Produk</h2>
            <p class="text-muted small">Temukan pakaian dan perlengkapan gaya hidup terbaik</p>
        </div>
        <div class="col-md-6">
            <form action="{{ route('products.index') }}" method="GET" class="d-flex">
                @foreach($selectedCategories as $cat)
                    <input type="hidden" name="categories[]" value="{{ $cat }}">
                @endforeach
                @foreach($selectedGenders as $gen)
                    <input type="hidden" name="gender[]" value="{{ $gen }}">
                @endforeach
                <input type="text" name="search" class="form-control me-2" placeholder="Cari produk..." value="{{ $search }}">
                <button type="submit" class="btn btn-dark"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="card product-filters p-3 mb-4 border-0 shadow-sm bg-light">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="filter-heading">
                        <span class="form-label small fw-bold mb-0">Kategori</span>
                        <span class="filter-hint">Pilih satu atau lebih</span>
                    </div>
                    <div class="filter-chip-group" role="group" aria-label="Filter kategori">
                        @foreach($categories as $category)
                            <label class="filter-chip">
                                <input class="filter-chip-input" type="checkbox" name="categories[]" value="{{ $category->slug }}"
                                       id="cat_{{ $category->id }}" {{ in_array($category->slug, $selectedCategories) ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="filter-heading">
                        <span class="form-label small fw-bold mb-0">Gender</span>
                        <span class="filter-hint">Pilih gaya</span>
                    </div>
                    <div class="filter-chip-group gender-chips" role="group" aria-label="Filter gender">
                        @foreach(['all' => 'Semua', 'pria' => 'Pria', 'wanita' => 'Wanita'] as $val => $label)
                            <label class="filter-chip">
                                <input class="filter-chip-input" type="checkbox" name="gender[]" value="{{ $val }}"
                                       id="gen_{{ $val }}" {{ in_array($val, $selectedGenders) ? 'checked' : '' }}
                                       onchange="this.form.submit()">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="filter-summary">
                        <span class="small text-muted"><i class="bi bi-sliders2 me-1"></i>Filter aktif</span>
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Product Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @forelse($products as $product)
            @php
                $imgUrl = $product->first_image_url;
                $avgRating = round((float) $product->ratings_avg_rating, 1);
            @endphp
            <div class="col product-item">
                <div class="card border h-100 card-3d interactive shadow-sm">
                    <div class="position-relative overflow-hidden" style="height: 240px;">
                        <img src="{{ $imgUrl }}"
                             class="card-img-top w-100 h-100 product-img"
                             alt="{{ $product->title }}"
                             style="object-fit: cover; cursor: pointer;"
                             data-bs-toggle="modal"
                             data-bs-target="#productModal"
                             data-id="{{ $product->id }}"
                             data-title="{{ $product->title }}"
                             data-price="{{ number_format($product->price, 0, ',', '.') }}"
                             data-category="{{ $product->category?->name ?? 'Umum' }}"
                             data-description="{{ $product->description }}"
                             data-gender="{{ ucfirst($product->gender) }}"
                             data-stock="{{ $product->stock }}"
                             data-image="{{ $imgUrl }}"
                             data-images='@json($product->images->pluck("image_url")->values())'
                             data-rating-count="{{ $product->ratings_count }}"
                             data-comment-count="{{ $product->comments_count }}"
                             data-owner="{{ $product->user?->full_name ?? $product->user?->username }}">
                        
                        <span class="position-absolute top-0 start-0 m-2 badge bg-dark text-white text-uppercase small">
                            {{ $product->gender }}
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column justify-content-between p-3">
                        <div>
                            <small class="text-muted text-uppercase d-block mb-1">{{ $product->category?->name }}</small>
                            <h6 class="card-title fw-bold mb-1 text-truncate" title="{{ $product->title }}">
                                {{ $product->title }}
                            </h6>
                            <div class="text-warning small mb-2">
                                <i class="bi bi-star-fill"></i>
                                <span class="fw-semibold text-dark">{{ $avgRating > 0 ? $avgRating : 'Baru' }}</span>
                                <span class="text-muted">({{ $product->ratings_count }})</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold text-primary fs-6">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <button class="btn btn-sm btn-outline-primary add-to-cart" data-id="{{ $product->id }}">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                <h5 class="text-muted">Tidak ada produk yang cocok dengan pencarian Anda.</h5>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-dark mt-2">Lihat Semua Produk</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-5">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection