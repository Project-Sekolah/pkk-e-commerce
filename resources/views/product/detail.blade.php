@extends('layouts.app')

@section('content')
<div class="container py-5">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('products.index') }}" class="btn btn-sm btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>

    <div class="row g-5">
        <div class="col-lg-6">
            <div id="productDetailGallery" class="carousel slide" data-bs-ride="false">
                <div class="carousel-inner rounded-4 overflow-hidden bg-light">
                    @forelse($product->images as $index => $image)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $image->image_url }}" class="d-block w-100" style="height: 500px; object-fit: cover;" alt="{{ $product->title }} {{ $index + 1 }}">
                        </div>
                    @empty
                        <div class="carousel-item active">
                            <img src="{{ $product->first_image_url }}" class="d-block w-100" style="height: 500px; object-fit: cover;" alt="{{ $product->title }}">
                        </div>
                    @endforelse
                </div>
                @if($product->images->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#productDetailGallery" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productDetailGallery" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                    <div class="d-flex gap-2 overflow-auto mt-3">
                        @foreach($product->images as $index => $image)
                            <button type="button" class="border-0 bg-transparent p-0" data-bs-target="#productDetailGallery" data-bs-slide-to="{{ $index }}">
                                <img src="{{ $image->image_url }}" width="72" height="72" class="rounded object-fit-cover" alt="Thumbnail {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <span class="badge bg-light text-dark text-uppercase mb-2">{{ $product->category?->name ?? 'Produk' }}</span>
            <h1 class="fw-bold mb-3">{{ $product->title }}</h1>
            <div class="text-warning mb-3"><i class="bi bi-star-fill"></i> {{ number_format((float) $product->ratings_avg_rating, 1) }} <span class="text-muted">({{ $product->comments_count }} komentar)</span></div>
            <div class="fs-3 fw-bold text-primary mb-4">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
            <p class="text-muted mb-4">{{ $product->description }}</p>
            <div class="row g-3 small mb-4">
                <div class="col-6"><strong>Stok</strong><br>{{ $product->stock }}</div>
                <div class="col-6"><strong>Penjual</strong><br>{{ $product->user?->full_name ?? $product->user?->username }}</div>
            </div>
            <button class="btn btn-primary add-to-cart" data-id="{{ $product->id }}"><i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang</button>
        </div>
    </div>

    <section class="mt-5 pt-4 border-top" id="reviews">
        <h3 class="fw-bold mb-4">Komentar Pelanggan</h3>
        @forelse($product->ratings->whereNotNull('review_text')->where('review_text', '<>', '') as $review)
            <article class="border-bottom py-3">
                <div class="d-flex justify-content-between"><strong>{{ $review->user?->full_name ?? $review->user?->username ?? 'Anonymous' }}</strong><span class="text-warning">{{ str_repeat('★', $review->rating) }}</span></div>
                <p class="mb-1 mt-2">{{ $review->review_text }}</p>
                <small class="text-muted">{{ $review->created_at->format('d M Y') }}</small>
            </article>
        @empty
            <p class="text-muted">Belum ada komentar.</p>
        @endforelse
    </section>
</div>
@endsection
