@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="fw-bold mb-1">Laporan Penjualan</h2><p class="text-muted mb-0">Ringkasan produk Anda yang sudah dikirim atau selesai.</p></div>
        <a href="{{ route('products.seller') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Produk Saya</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="border rounded-3 p-3 bg-light"><small class="text-muted">Total Order</small><h3 class="fw-bold mb-0">{{ $totalOrders }}</h3></div></div>
        <div class="col-md-4"><div class="border rounded-3 p-3 bg-light"><small class="text-muted">Item Terjual</small><h3 class="fw-bold mb-0">{{ $totalItems }}</h3></div></div>
        <div class="col-md-4"><div class="border rounded-3 p-3 bg-success-subtle"><small class="text-muted">Pendapatan</small><h3 class="fw-bold mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3></div></div>
    </div>
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Tanggal</th><th>Produk</th><th>Pembeli</th><th>Status</th><th>Qty</th><th class="text-end">Pendapatan</th></tr></thead><tbody>
            @forelse($sales as $sale)
                <tr><td>{{ $sale->order->created_at->format('d M Y') }}</td><td class="fw-semibold">{{ $sale->product?->title ?? 'Produk Dihapus' }}</td><td>{{ $sale->order->user?->full_name ?? $sale->order->user?->username }}</td><td><span class="badge bg-primary">{{ ucfirst($sale->order->status) }}</span></td><td>{{ $sale->quantity }}</td><td class="text-end">Rp {{ number_format((float) $sale->price * $sale->quantity, 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penjualan.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
