@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 760px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('products.seller') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Edit Produk: {{ $product->title }}</h2>
    </div>

    <div class="card shadow-sm border-0 p-4 mb-4">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Judul Produk <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $product->title) }}" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="all" {{ old('gender', $product->gender) == 'all' ? 'selected' : '' }}>Semua (Unisex)</option>
                        <option value="pria" {{ old('gender', $product->gender) == 'pria' ? 'selected' : '' }}>Pria</option>
                        <option value="wanita" {{ old('gender', $product->gender) == 'wanita' ? 'selected' : '' }}>Wanita</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', (int)$product->price) }}" required min="0" step="1000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jumlah Stok <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required min="0">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Tautkan Diskon Produk</label>
                <select name="discount_id" class="form-select">
                    <option value="">-- Tanpa Diskon Tambahan --</option>
                    @foreach($discounts as $disc)
                        <option value="{{ $disc->id }}" {{ $product->discounts->contains($disc->id) ? 'selected' : '' }}>
                            {{ $disc->name }} ({{ $disc->percentage }}%)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi Produk <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="4" required>{{ old('description', $product->description) }}</textarea>
            </div>

            <!-- Existing Images -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Foto Saat Ini</label>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($product_images as $img)
                        <div class="position-relative border rounded p-1">
                            <img src="{{ $img->image_url }}" alt="Img" width="90" height="90" style="object-fit: cover;" class="rounded">
                        </div>
                    @empty
                        <span class="text-muted small">Belum ada foto yang diunggah.</span>
                    @endforelse
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Tambah Foto Baru</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                <small class="text-muted">Pilih foto jika ingin menambahkan foto baru.</small>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('products.seller') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Perbarui Produk</button>
            </div>
        </form>
    </div>
</div>
@endsection