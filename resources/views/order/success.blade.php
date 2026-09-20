@extends('layouts.app')

@section('content')
<div class="container py-5 text-center" style="max-width: 600px;">
    <div class="card p-5 shadow-sm border-0">
        <div class="mb-3 text-success">
            <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
        </div>
        <h3 class="fw-bold mb-2">Pesanan Berhasil Dibuat!</h3>
        <p class="text-muted mb-4">Terima kasih telah berbelanja di Lunerburg & Co. Pesanan Anda telah berhasil diproses.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('order.history') }}" class="btn btn-primary">Lihat Riwayat Pesanan</a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-dark">Belanja Lagi</a>
        </div>
    </div>
</div>
@endsection