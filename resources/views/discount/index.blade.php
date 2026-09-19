@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Daftar Diskon</h2>
            <p class="text-muted small">Kelola kupon dan potongan harga toko Anda</p>
        </div>
        <a href="{{ route('discounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Diskon
        </a>
    </div>

    @if($discounts->isEmpty())
        <div class="card p-5 text-center shadow-sm border-0">
            <i class="bi bi-tags fs-1 text-muted mb-3"></i>
            <h5>Belum ada diskon yang dibuat</h5>
            <p class="text-muted">Buat diskon menarik untuk meningkatkan penjualan Anda.</p>
            <div class="mt-2">
                <a href="{{ route('discounts.create') }}" class="btn btn-primary">Buat Diskon Baru</a>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach ($discounts as $discount)
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0">{{ $discount->name }}</h5>
                            <span class="badge bg-danger fs-6">{{ $discount->percentage }}%</span>
                        </div>
                        <div class="small text-muted mb-3">
                            <div><i class="bi bi-calendar-check me-1"></i>Mulai: {{ $discount->start_date->format('d/m/Y') }}</div>
                            <div><i class="bi bi-calendar-x me-1"></i>Berakhir: {{ $discount->end_date->format('d/m/Y') }}</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-auto">
                            <a href="{{ route('discounts.edit', $discount->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                            <form action="{{ route('discounts.destroy', $discount->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus diskon ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection