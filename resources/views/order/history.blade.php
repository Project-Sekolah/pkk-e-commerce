@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Riwayat Pesanan</h2>
            <p class="text-muted small">Daftar transaksi pembelian Anda di Lunerburg & Co</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm">Belanja Lagi</a>
    </div>

    @if(empty($orders) || count($orders) === 0)
        <div class="card p-5 text-center shadow-sm border-0">
            <i class="bi bi-receipt fs-1 text-muted mb-3"></i>
            <h5>Belum ada pesanan</h5>
            <p class="text-muted">Jelajahi katalog dan temukan produk favorit Anda!</p>
            <div class="mt-2">
                <a href="{{ route('products.index') }}" class="btn btn-primary">Mulai Belanja</a>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0 p-3">
            <div class="table-responsive">
                <table id="ordersTable" class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Jumlah Item</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $orderJson = json_encode($order['id']);
                                $totalFormatted = number_format($order['total'], 0, ',', '.');
                                $tgl = date('Y-m-d', strtotime($order['created_at']));
                                $phone = $user->phone_number ?? '-';
                                $itemsJson = json_encode($order['items'] ?? []);
                                $statusClass = $order['badge_class'] ?? 'bg-secondary';
                                $statusLabel = $order['status_label'] ?? ucfirst($order['status']);
                            @endphp
                            <tr>
                                <td><code class="text-dark">{{ substr($order['id'], 0, 8) }}...</code></td>
                                <td>{{ $tgl }}</td>
                                <td>
                                    <span class="badge {{ $statusClass }} text-capitalize">{{ $statusLabel }}</span>
                                </td>
                                <td class="fw-semibold">Rp {{ $totalFormatted }}</td>
                                <td>{{ $order['item_count'] }} item</td>
                                <td class="text-center">
                                    <a href="{{ route('order.detail', $order['id']) }}" class="btn btn-sm btn-primary">Detail</a>
                                    <button class="btn btn-sm btn-success"
                                            onclick='downloadStruk({{ $orderJson }}, "{{ $totalFormatted }}", "{{ $tgl }}", "{{ $phone }}", {!! $itemsJson !!})'>
                                        <i class="bi bi-download me-1"></i> Struk
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#ordersTable').DataTable({
        order: [[1, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "Lanjut",
                previous: "Sebelum"
            }
        }
    });
});

async function downloadStruk(orderId, total, tanggal, phone, items) {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.setFont("helvetica", "bold");
        doc.text("STRUK PEMBAYARAN", 105, 20, { align: "center" });

        doc.setFontSize(11);
        doc.setFont("helvetica", "normal");
        doc.text(`ID Transaksi : ${orderId}`, 20, 35);
        doc.text(`Nomor HP     : ${phone || "-"}`, 20, 42);
        doc.text(`Tanggal       : ${tanggal}`, 20, 49);

        const body = (items || []).map(item => [
            item.name,
            item.quantity,
            `Rp ${Number(item.price).toLocaleString("id-ID")}`,
            `Rp ${(item.price * item.quantity).toLocaleString("id-ID")}`
        ]);

        doc.autoTable({
            startY: 60,
            head: [["Produk", "Qty", "Harga", "Subtotal"]],
            body: body,
            styles: { fontSize: 10 },
            headStyles: { fillColor: [43, 43, 43] },
        });

        let finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(13);
        doc.setFont("helvetica", "bold");
        doc.text(`TOTAL: Rp ${total}`, 20, finalY);

        doc.save(`Struk-Order-${orderId.substring(0, 8)}.pdf`);
    } catch (e) {
        console.error("Gagal cetak struk:", e);
        Swal.fire({
            icon: 'error',
            title: 'Gagal Membuat Struk',
            text: e.message
        });
    }
}
</script>
@endsection