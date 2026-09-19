@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('order.history') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Detail Pesanan</h2>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Order #{{ $order['id'] }}</h5>
                    <small class="text-muted">{{ $order['created_at'] }}</small>
                </div>
                <span class="badge bg-success fs-6 text-capitalize">{{ $order['status'] }}</span>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold mb-1">Alamat Pengiriman:</h6>
                <p class="text-muted mb-0">{{ $order['customer_address'] }}</p>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2">
                <span class="fw-bold fs-5">Total Pembayaran:</span>
                <span class="fw-bold text-primary fs-4">Rp {{ number_format($order['total'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-light py-3">
            <h6 class="mb-0 fw-bold">Daftar Produk yang Dibeli</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order_items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['quantity'] }}</td>
                            <td class="text-end">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection