@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Kelola Produk Saya</h2>
            <p class="text-muted small">Daftar produk yang Anda jual di Lunerburg & Co</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </a>
    </div>

    @if($products->isEmpty())
        <div class="card p-5 text-center shadow-sm border-0">
            <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
            <h5>Belum ada produk yang ditambahkan</h5>
            <p class="text-muted">Mulai jual barang berkualitas Anda sekarang.</p>
            <div class="mt-2">
                <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Produk Sekarang</a>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Gambar</th>
                            <th>Judul Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Gender</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td style="width: 80px;">
                                    <img src="{{ $product->first_image_url }}" alt="{{ $product->title }}"
                                         class="rounded" width="60" height="60" style="object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $product->title }}</div>
                                    <small class="text-muted">Slug: {{ $product->slug }}</small>
                                </td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td class="fw-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $product->stock > 5 ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="text-capitalize">{{ $product->gender }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection