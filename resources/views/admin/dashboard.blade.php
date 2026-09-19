@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Dashboard Admin</h2>
            <p class="text-muted small">Ringkasan operasional dan statistik toko Lunerburg & Co</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-people-fill me-1"></i> Manajemen Pengguna
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-3 me-3 fs-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Pengguna</small>
                        <h3 class="fw-bold mb-0">{{ $total_users }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="p-3 bg-info bg-opacity-10 text-info rounded-3 me-3 fs-3">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Produk</small>
                    <h3 class="fw-bold mb-0">{{ $total_products }}</h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3 me-3 fs-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Transaksi</small>
                    <h3 class="fw-bold mb-0">{{ $total_orders }}</h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-3 me-3 fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Pendapatan</small>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($total_revenue, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Grafik Penjualan Bulanan ({{ date('Y') }})</h5>
                <div style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3">Tipe Pengguna</h5>
                <div style="height: 300px;">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products & Categories -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-light py-3">
                    <h6 class="fw-bold mb-0">5 Produk Paling Laris</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Produk</th>
                                <th>Penjual</th>
                                <th>Kategori</th>
                                <th class="text-center">Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top_products as $p)
                                <tr>
                                    <td class="fw-bold">{{ $p->title }}</td>
                                    <td>{{ $p->seller_name }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $p->category }}</span></td>
                                    <td class="text-center fw-bold text-success">{{ $p->sold }} item</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-3">Kategori Produk Tersedia</h6>
                <ul class="list-group list-group-flush">
                    @foreach($categories as $cat)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ $cat->name }}
                            <span class="badge bg-secondary rounded-pill">{{ $cat->products()->count() }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Sales Chart
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: {!! json_encode($monthly_sales) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // User Chart
    const ctxUser = document.getElementById('userChart').getContext('2d');
    new Chart(ctxUser, {
        type: 'doughnut',
        data: {
            labels: ['Buyer', 'Seller', 'Admin'],
            datasets: [{
                data: [
                    {{ $user_type_counts['buyer'] ?? 0 }},
                    {{ $user_type_counts['seller'] ?? 0 }},
                    {{ $user_type_counts['admin'] ?? 0 }}
                ],
                backgroundColor: ['#198754', '#0dcaf0', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endsection