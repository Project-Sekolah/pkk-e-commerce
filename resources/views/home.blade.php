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

<!-- Kolase Promo -->
<div class="container py-5">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="collection-box bg-dark position-relative rounded overflow-hidden shadow-sm" style="height: 100%; min-height: 400px;">
                <img src="{{ asset('assets/img/model1.jpg') }}" alt="Women" class="w-100 h-100" style="object-fit: cover;">
                <div class="collection-content position-absolute bottom-0 start-0 p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); width: 100%;">
                    <h6 class="text-uppercase tracking-wider">HOT LIST</h6>
                    <h4 class="fw-bold">WOMEN COLLECTION</h4>
                    <a href="{{ route('products.index', ['gender' => 'wanita']) }}" class="text-white fw-bold text-decoration-underline">SHOP NOW</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-3">
                <div class="col-12">
                    <div class="collection-box bg-light position-relative rounded overflow-hidden shadow-sm" style="height: 220px;">
                        <img src="{{ asset('assets/img/model4.png') }}" alt="Men" class="w-100 h-100" style="object-fit: cover;">
                        <div class="collection-content position-absolute bottom-0 start-0 p-3 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); width: 100%;">
                            <h6 class="text-uppercase">HOT LIST</h6>
                            <h5 class="fw-bold">MEN COLLECTION</h5>
                            <a href="{{ route('products.index', ['gender' => 'pria']) }}" class="text-white fw-bold text-decoration-underline">SHOP NOW</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="collection-box position-relative rounded overflow-hidden shadow-sm" style="height: 220px;">
                        <img src="{{ asset('assets/img/model5.jpg') }}" alt="Comfort" class="w-100 h-100" style="object-fit: cover;">
                        <div class="collection-content position-absolute bottom-0 start-0 p-3 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); width: 100%;">
                            <h6 class="text-uppercase">COMFORT</h6>
                            <h6 class="fw-bold">COLLECTIONS</h6>
                            <a href="{{ route('products.index') }}" class="text-white fw-bold text-decoration-underline">SHOP NOW</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="collection-box position-relative rounded overflow-hidden shadow-sm" style="height: 220px;">
                        <img src="{{ asset('assets/img/model6.jpg') }}" alt="Gifts" class="w-100 h-100" style="object-fit: cover;">
                        <div class="collection-content position-absolute bottom-0 start-0 p-3 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); width: 100%;">
                            <h6 class="text-uppercase">E-GIFT CARDS</h6>
                            <small class="d-block mb-1">Berikan hadiah terbaik.</small>
                            <a href="{{ route('products.index') }}" class="text-white fw-bold text-decoration-underline">DISCOVER MORE</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popular Products -->
<div class="container py-4" id="produk">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Produk Terpopuler</h3>
            <p class="text-muted small">Pilihan favorit para pelanggan Lunerburg & Co</p>
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