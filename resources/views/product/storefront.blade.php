@extends('layouts.app')

@section('content')
<div class="container py-4">
    <section class="rounded-3 bg-dark text-white p-4 p-md-5 mb-4">
        <span class="text-white-50 small text-uppercase">Storefront seller</span>
        <h1 class="fw-bold mb-2">{{ $seller->shop_name ?? $seller->full_name }}</h1>
        <p class="mb-0 text-white-50"><i class="bi bi-geo-alt me-1"></i>{{ $seller->shop_address ?? 'Alamat toko belum diisi' }}</p>
    </section>

    @if($discounts->isNotEmpty())
        <section class="mb-4">
            <h3 class="fw-bold mb-3">Promo dari toko ini</h3>
            <div class="row g-3">
                @foreach($discounts as $discount)
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <span class="badge bg-success mb-2">Hemat {{ number_format($discount->percentage, 0) }}%</span>
                            <h5 class="fw-bold mb-1">{{ $discount->name }}</h5>
                            <small class="text-muted">Berlaku untuk produk promo toko ini.</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div><h2 class="fw-bold mb-1">Produk {{ $seller->shop_name ?? $seller->full_name }}</h2><p class="text-muted mb-0">{{ $products->total() }} produk aktif</p></div>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
        @forelse($products as $product)
            <div class="col">
                <article class="card h-100 shadow-sm border-0">
                    <img src="{{ $product->first_image_url }}" class="card-img-top" style="height:220px;object-fit:cover" alt="{{ $product->title }}">
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted">{{ $product->category?->name }}</small>
                        <h5 class="fw-bold mt-1">{{ $product->title }}</h5>
                        <strong class="text-primary mt-auto">Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye me-1"></i>Detail</a>
                            <button class="btn btn-sm btn-primary add-to-cart" data-id="{{ $product->id }}"><i class="bi bi-cart-plus"></i></button>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><p class="text-muted">Belum ada produk aktif di toko ini.</p></div>
        @endforelse
    </div>
    <div class="d-flex justify-content-center mt-4">{{ $products->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
