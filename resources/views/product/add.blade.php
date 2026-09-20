@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 760px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('products.seller') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Tambah Produk Baru</h2>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Judul Produk <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Jaket Korduroi Hangat">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="all" {{ old('gender') == 'all' ? 'selected' : '' }}>Semua (Unisex)</option>
                        <option value="pria" {{ old('gender') == 'pria' ? 'selected' : '' }}>Pria</option>
                        <option value="wanita" {{ old('gender') == 'wanita' ? 'selected' : '' }}>Wanita</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" value="{{ old('price') }}" required min="0" step="1000" placeholder="150000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jumlah Stok <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', 10) }}" required min="0" placeholder="10">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi Produk <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="4" required placeholder="Jelaskan detail bahan, ukuran, dan keunikan produk...">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Unggah Foto Produk</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                <small class="text-muted">Bisa memilih lebih dari satu foto sekaligus. Format: JPG, PNG, WEBP (Maks: 10MB per foto).</small>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('products.seller') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>
@endsection