@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manajemen Pesanan</h2>
            <p class="text-muted small mb-0">Pantau pembayaran Midtrans dan proses pemenuhan pesanan.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="row g-2 mb-4">
        @foreach(['pending' => 'Menunggu', 'paid' => 'Dibayar', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Batal'] as $key => $label)
            <div class="col-6 col-md">
                <a href="{{ route('admin.orders', ['status' => $key]) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 p-3">
                        <small class="text-muted">{{ $label }}</small>
                        <strong class="fs-4 text-dark">{{ $statusCounts[$key] ?? 0 }}</strong>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <form class="card border-0 shadow-sm p-3 mb-4" method="GET" action="{{ route('admin.orders') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold" for="search">Cari order atau pelanggan</label>
                <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="ID order, nama, email">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach(['pending', 'paid', 'shipped', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.orders') }}" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 admin-orders-table">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <code>{{ substr($order->id, 0, 8) }}</code>
                                <small class="d-block text-muted">{{ $order->created_at->format('d M Y H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $order->user?->full_name ?? $order->user?->username ?? 'Guest' }}</strong>
                                <small class="d-block text-muted">{{ $order->user?->email }}</small>
                            </td>
                            <td>{{ $order->items->sum('quantity') }} item</td>
                            <td class="fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td><span class="small text-muted">{{ $order->transaction_status ?? 'Belum dibayar' }}</span></td>
                            <td><span class="badge {{ $order->status === 'cancelled' ? 'bg-danger' : ($order->status === 'paid' ? 'bg-success' : 'bg-warning text-dark') }} text-capitalize">{{ $order->status }}</span></td>
                            <td class="text-end">
                                @if($order->status === 'paid')
                                    <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="shipped">
                                        <button class="btn btn-sm btn-outline-primary" title="Tandai dikirim"><i class="bi bi-truck"></i></button>
                                    </form>
                                @elseif($order->status === 'shipped')
                                    <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button class="btn btn-sm btn-outline-success" title="Tandai selesai"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                @elseif($order->status === 'pending')
                                    <form method="POST" action="{{ route('admin.orders.status', $order->id) }}" onsubmit="return confirm('Batalkan order ini dan kembalikan stok?');">
                                        @csrf
                                        <input type="hidden" name="status" value="cancelled">
                                        <button class="btn btn-sm btn-outline-danger" title="Batalkan"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                @else
                                    <span class="text-muted small">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Belum ada pesanan yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
