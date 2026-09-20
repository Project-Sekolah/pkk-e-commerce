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
                <span id="orderStatusBadge" class="badge {{ $order['badge_class'] ?? 'bg-warning text-dark' }} fs-6 text-capitalize">{{ $order['status_label'] }}</span>
            </div>

            @if ($order['status'] === 'pending' && !empty($order['snap_token']))
                <div class="border rounded-3 bg-light p-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <strong>Menunggu pembayaran</strong>
                            <div class="small text-muted">Selesaikan pembayaran sebelum pesanan kedaluwarsa.</div>
                        </div>
                        @if (config('services.midtrans.client_key'))
                            <button type="button" id="payNowButton" class="btn btn-primary">
                                <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
                            </button>
                        @else
                            <span class="small text-danger">Pembayaran belum dikonfigurasi.</span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Alamat Pengiriman:</h6>
                    <p class="text-muted mb-0">{{ $order['customer_address'] }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Status Pembayaran:</h6>
                    <p id="paymentStatusValue" class="mb-0 text-muted">{{ $order['payment_status'] }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Metode Pembayaran:</h6>
                    <p id="paymentTypeValue" class="mb-0 text-muted">{{ $order['payment_type'] }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Kurir:</h6>
                    <p class="mb-0 text-muted">{{ $order['courier'] ?? 'Belum dipilih' }} (Rp {{ number_format($order['shipping_fee'] ?? 0, 0, ',', '.') }})</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-1">Transaksi Midtrans:</h6>
                    <p id="transactionStatusValue" class="mb-0 text-muted text-capitalize">{{ $order['transaction_status'] ?? 'pending' }}</p>
                </div>
            </div>

            @if (in_array($order['status'], ['paid', 'completed', 'shipped'], true) || ($order['transaction_status'] ?? '') !== 'pending')
                <div class="border rounded-3 bg-success-subtle p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div>
                            <strong>Bukti Pembayaran</strong>
                            <div class="small text-muted">Transaksi berhasil diproses dan dapat diunduh dalam format PDF.</div>
                        </div>
                        <button type="button" id="downloadPaymentProofBtn" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i> Download PDF
                        </button>
                    </div>
                </div>
            @else
                <div class="border rounded-3 bg-light p-3 mb-3">
                    <strong>Bukti Pembayaran</strong>
                    <div class="small text-muted">Belum ada bukti pembayaran karena transaksi masih menunggu konfirmasi.</div>
                </div>
            @endif

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

@if ($order['status'] === 'pending' && !empty($order['snap_token']))
    <script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        const orderDetailState = @json($order);

        function applyOrderStatus(data) {
            const badge = document.getElementById('orderStatusBadge');
            if (badge) {
                const classes = {
                    pending: 'bg-warning text-dark',
                    paid: 'bg-success',
                    completed: 'bg-primary',
                    shipped: 'bg-info text-dark',
                    completed: 'bg-primary',
                    cancelled: 'bg-danger',
                };
                const label = data.status_label || data.status || 'pending';
                badge.className = `badge fs-6 text-capitalize ${classes[data.status] || 'bg-secondary'}`;
                badge.textContent = label;
            }

            const paymentStatusValue = document.getElementById('paymentStatusValue');
            if (paymentStatusValue) paymentStatusValue.textContent = data.payment_status || data.status_label || 'Menunggu Pembayaran';

            const paymentTypeValue = document.getElementById('paymentTypeValue');
            if (paymentTypeValue) paymentTypeValue.textContent = data.payment_type || 'Belum dipilih';

            const transactionStatusValue = document.getElementById('transactionStatusValue');
            if (transactionStatusValue) transactionStatusValue.textContent = (data.transaction_status || 'pending').toString();
        }

        async function refreshOrderState() {
            try {
                const response = await fetch(`/order/detail/${orderDetailState.id}`, {
                    headers: { Accept: 'application/json' }
                });
                if (!response.ok) return;
                const data = await response.json();
                applyOrderStatus(data);
                if (['paid', 'completed', 'shipped'].includes(data.status) || data.transaction_status !== 'pending') {
                    const paymentSection = document.getElementById('downloadPaymentProofBtn');
                    if (paymentSection) paymentSection.disabled = false;
                }
            } catch (error) {
                console.error('Gagal sinkronisasi status pembayaran:', error);
            }
        }

        function downloadPaymentProof() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const orderId = orderDetailState.id || 'ORDER';
            const total = Number(orderDetailState.total || 0);
            const paymentType = orderDetailState.payment_type || 'Belum dipilih';
            const transactionStatus = orderDetailState.transaction_status || 'pending';

            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('BUKTI PEMBAYARAN', 105, 20, { align: 'center' });

            doc.setFontSize(11);
            doc.setFont('helvetica', 'normal');
            doc.text(`Order ID: ${orderId}`, 20, 35);
            doc.text(`Status: ${orderDetailState.status_label || orderDetailState.status}`, 20, 42);
            doc.text(`Metode Pembayaran: ${paymentType}`, 20, 49);
            doc.text(`Transaksi: ${transactionStatus}`, 20, 56);
            doc.text(`Total: Rp ${Number(total).toLocaleString('id-ID')}`, 20, 63);
            doc.text('Terima kasih atas kepercayaan Anda.', 20, 75);
            doc.save(`Bukti-Pembayaran-${orderId.slice(0, 8)}.pdf`);
        }

        document.getElementById('downloadPaymentProofBtn')?.addEventListener('click', downloadPaymentProof);

        document.getElementById('payNowButton')?.addEventListener('click', function () {
            this.disabled = true;
            if (!window.snap) {
                this.disabled = false;
                alert('Layanan pembayaran belum termuat. Periksa MIDTRANS_CLIENT_KEY dan koneksi internet.');
                return;
            }
            window.snap.pay(@json($order['snap_token']), {
                onSuccess: async function () {
                    await refreshOrderState();
                },
                onPending: async function () {
                    await refreshOrderState();
                },
                onError: async function () {
                    await refreshOrderState();
                },
                onClose: () => {
                    this.disabled = false;
                }
            });
        });
    </script>
@endif
@endsection