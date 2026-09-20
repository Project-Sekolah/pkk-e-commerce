@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Riwayat Pembelian Produk</h2>
            <p class="text-muted small mb-0">Daftar produk Anda yang sudah dibayar dan dibeli pelanggan.</p>
        </div>
        <a href="{{ route('products.seller') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Produk
        </a>
    </div>

    @if($sales->isEmpty())
        <div class="card p-5 text-center shadow-sm border-0">
            <i class="bi bi-receipt fs-1 text-muted mb-3"></i>
            <h5>Belum ada pembelian</h5>
            <p class="text-muted mb-0">Riwayat pembelian produk Anda akan tampil di sini.</p>
        </div>
    @else
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Pembeli</th>
                            <th>Status</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            @php
                                $statusLabel = match($sale->order->status) {
                                    'paid' => 'Dibayar',
                                    'shipped' => 'Dikirim',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                    default => 'Menunggu Pembayaran',
                                };
                                $statusClass = match($sale->order->status) {
                                    'paid' => 'bg-success',
                                    'shipped' => 'bg-info text-dark',
                                    'completed' => 'bg-primary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td>{{ $sale->order->created_at->format('d M Y H:i') }}</td>
                                <td class="fw-semibold">{{ $sale->product?->title ?? 'Produk Dihapus' }}</td>
                                <td>
                                    {{ $sale->order->user?->full_name ?? $sale->order->user?->username ?? 'Pembeli' }}
                                    <small class="d-block text-muted">{{ $sale->order->user?->email ?? '-' }}</small>
                                </td>
                                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td class="text-center">{{ $sale->quantity }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format((float) $sale->price * $sale->quantity, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $sales->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
