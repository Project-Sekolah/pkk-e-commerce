@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="hero d-flex align-items-center text-white position-relative" style="background-color: #2b2b2b; padding: 100px 0 140px 0;">
    <div class="container text-start position-relative" style="z-index: 2;">
        <h1 class="display-4 fw-bold">Effortless Style</h1>
        <h1 class="display-4 fw-bold">Endless Comfort</h1>
        <p class="lead">Dibuat dengan cinta, warna alami, dan sentuhan kehangatan abadi.</p>
        <a href="#produk" class="btn px-4 py-2 mt-3 btn-discover btn-light fw-semibold">Jelajahi Sekarang</a>
    </div>

    <!-- Wave Decoration -->
    <svg viewBox="0 0 1440 320" class="position-absolute" style="bottom: -1px; left: 0; width: 100%; z-index: 1;">
        <path fill="#f8f9fa" d="M0,64L40,90.7C80,117,160,171,240,186.7C320,203,400,181,480,176C560,171,640,181,720,192C800,203,880,213,960,197.3C1040,181,1120,139,1200,117.3C1280,96,1360,96,1400,96L1440,96L1440,320L0,320Z"/>
    </svg>
</section>

<!-- Hotlist Comfort: sourced from active products and completed sales -->
<div class="container py-4" id="produk">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Hotlist Comfort</h3>
            <p class="text-muted small">Produk nyata yang paling banyak dipilih pelanggan kami.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm">Lihat Semua Produk &rarr;</a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="productGrid">
        @forelse($products as $product)
            @php
                $imgUrl = $product->first_image_url;
                $avgRating = round((float) $product->ratings_avg_rating, 1);
            @endphp
            <div class="col product-item" data-category="{{ strtolower($product->category?->slug ?? '') }}" data-gender="{{ strtolower($product->gender) }}">
                <div class="card border h-100 card-3d interactive shadow-sm">
                    <div class="position-relative overflow-hidden" style="height: 240px;">
                        <img src="{{ $imgUrl }}"
                             class="card-img-top w-100 h-100 product-img cursor-pointer"
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
                <p class="text-muted">Belum ada produk yang tersedia saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection