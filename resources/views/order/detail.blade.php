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

```blade
{{-- =========================================================
     LIBRARY PDF
     ========================================================= --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>


{{-- =========================================================
     DATA ORDER
     ========================================================= --}}
<script>
    const orderDetailState = @json($order);

    // Data produk dibuat terpisah karena di Blade kamu menggunakan
    // variabel $order_items untuk menampilkan produk.
    const orderItems = @json($order_items);
</script>


{{-- =========================================================
     MIDTRANS
     Hanya dijalankan jika order masih pending
     ========================================================= --}}
@if ($order['status'] === 'pending' && !empty($order['snap_token']))

    <script
        src="{{ config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('services.midtrans.client_key') }}">
    </script>

    <script>

        /**
         * Mengubah tampilan status pesanan
         */
        function applyOrderStatus(data) {

            const badge = document.getElementById('orderStatusBadge');

            if (badge) {

                const classes = {
                    pending: 'bg-warning text-dark',
                    paid: 'bg-success',
                    completed: 'bg-primary',
                    shipped: 'bg-info text-dark',
                    cancelled: 'bg-danger',
                };

                const label =
                    data.status_label ||
                    data.status ||
                    'pending';

                badge.className =
                    `badge fs-6 text-capitalize ${
                        classes[data.status] || 'bg-secondary'
                    }`;

                badge.textContent = label;
            }


            /**
             * Status pembayaran
             */
            const paymentStatusValue =
                document.getElementById('paymentStatusValue');

            if (paymentStatusValue) {

                paymentStatusValue.textContent =
                    data.payment_status ||
                    data.status_label ||
                    'Menunggu Pembayaran';
            }


            /**
             * Metode pembayaran
             */
            const paymentTypeValue =
                document.getElementById('paymentTypeValue');

            if (paymentTypeValue) {

                paymentTypeValue.textContent =
                    data.payment_type ||
                    'Belum dipilih';
            }


            /**
             * Status transaksi Midtrans
             */
            const transactionStatusValue =
                document.getElementById('transactionStatusValue');

            if (transactionStatusValue) {

                transactionStatusValue.textContent =
                    (
                        data.transaction_status ||
                        'pending'
                    ).toString();
            }
        }


        /**
         * Mengambil status terbaru dari server
         */
        async function refreshOrderState() {

            try {

                const response = await fetch(
                    `/order/detail/${orderDetailState.id}`,
                    {
                        headers: {
                            Accept: 'application/json'
                        }
                    }
                );


                if (!response.ok) {
                    console.error(
                        'Gagal mengambil status order:',
                        response.status
                    );

                    return;
                }


                const data = await response.json();


                /**
                 * Update tampilan
                 */
                applyOrderStatus(data);


                /**
                 * Jika pembayaran berhasil,
                 * aktifkan tombol download PDF
                 */
                if (
                    ['paid', 'completed', 'shipped']
                        .includes(data.status)
                    ||
                    data.transaction_status !== 'pending'
                ) {

                    const paymentSection =
                        document.getElementById(
                            'downloadPaymentProofBtn'
                        );

                    if (paymentSection) {
                        paymentSection.disabled = false;
                    }
                }


                /**
                 * Update data order di browser
                 * supaya PDF memakai status terbaru.
                 */
                Object.assign(
                    orderDetailState,
                    data
                );

            } catch (error) {

                console.error(
                    'Gagal sinkronisasi status pembayaran:',
                    error
                );
            }
        }


        /**
         * Tombol Bayar Sekarang
         */
        document
            .getElementById('payNowButton')
            ?.addEventListener(
                'click',
                function () {

                    const button = this;

                    button.disabled = true;


                    /**
                     * Pastikan Midtrans berhasil dimuat
                     */
                    if (!window.snap) {

                        button.disabled = false;

                        alert(
                            'Layanan pembayaran belum termuat. ' +
                            'Periksa MIDTRANS_CLIENT_KEY dan koneksi internet.'
                        );

                        return;
                    }


                    /**
                     * Jalankan Snap Midtrans
                     */
                    window.snap.pay(
                        @json($order['snap_token']),
                        {

                            /**
                             * Pembayaran berhasil
                             */
                            onSuccess: async function () {

                                await refreshOrderState();

                                button.disabled = false;
                            },


                            /**
                             * Pembayaran masih pending
                             */
                            onPending: async function () {

                                await refreshOrderState();

                                button.disabled = false;
                            },


                            /**
                             * Pembayaran error
                             */
                            onError: async function () {

                                await refreshOrderState();

                                button.disabled = false;
                            },


                            /**
                             * User menutup popup
                             */
                            onClose: function () {

                                button.disabled = false;
                            }
                        }
                    );
                }
            );

    </script>

@endif

<script>

    /**
     * Membuat dan mendownload PDF
     */
    function downloadPaymentProof() {

        /**
         * Pastikan jsPDF tersedia
         */
        if (
            !window.jspdf ||
            !window.jspdf.jsPDF
        ) {

            alert(
                'Library PDF belum berhasil dimuat. ' +
                'Silakan refresh halaman dan coba lagi.'
            );

            console.error(
                'jsPDF tidak ditemukan pada window.jspdf'
            );

            return;
        }


        /**
         * Ambil constructor jsPDF
         */
        const {
            jsPDF
        } = window.jspdf;


        /**
         * Buat dokumen PDF
         */
        const doc = new jsPDF();


        /**
         * Data order
         */
        const orderId =
            orderDetailState.id ||
            'ORDER';


        const total =
            Number(
                orderDetailState.total || 0
            );


        const paymentType =
            orderDetailState.payment_type ||
            'Belum dipilih';


        const transactionStatus =
            orderDetailState.transaction_status ||
            'pending';


        const status =
            orderDetailState.status_label ||
            orderDetailState.status ||
            'Belum diketahui';


        /**
         * ==========================
         * HEADER
         * ==========================
         */

        doc.setFontSize(18);

        doc.setFont(
            'helvetica',
            'bold'
        );


        doc.text(
            'BUKTI PEMBAYARAN',
            105,
            20,
            {
                align: 'center'
            }
        );


        /**
         * ==========================
         * INFORMASI ORDER
         * ==========================
         */

        doc.setFontSize(11);

        doc.setFont(
            'helvetica',
            'normal'
        );


        doc.text(
            `Order ID: ${orderId}`,
            20,
            35
        );


        doc.text(
            `Status: ${status}`,
            20,
            42
        );


        doc.text(
            `Metode Pembayaran: ${paymentType}`,
            20,
            49
        );


        doc.text(
            `Status Transaksi: ${transactionStatus}`,
            20,
            56
        );


        /**
         * ==========================
         * DAFTAR PRODUK
         * ==========================
         */

        const tableBody =
            Array.isArray(orderItems)
                ? orderItems.map(item => {

                    const price =
                        Number(item.price || 0);

                    const quantity =
                        Number(item.quantity || 0);

                    const subtotal =
                        price * quantity;


                    return [

                        item.name ||
                            'Produk',

                        item.store_name ||
                            'Toko',

                        quantity,

                        `Rp ${subtotal.toLocaleString(
                            'id-ID'
                        )}`
                    ];

                })
                : [];


        /**
         * Pastikan AutoTable tersedia
         */
        if (
            typeof doc.autoTable === 'function'
        ) {

            doc.autoTable({

                startY: 68,

                head: [
                    [
                        'Produk',
                        'Toko',
                        'Qty',
                        'Subtotal'
                    ]
                ],

                body: tableBody,

                styles: {
                    fontSize: 9
                },

                headStyles: {
                    fillColor: [
                        43,
                        43,
                        43
                    ]
                },

                margin: {
                    left: 20,
                    right: 20
                }
            });

        } else {

            /**
             * Fallback jika AutoTable gagal
             */
            console.error(
                'jspdf-autotable tidak ditemukan.'
            );

            doc.text(
                'Daftar produk tidak dapat ditampilkan.',
                20,
                75
            );
        }


        /**
         * ==========================
         * TOTAL
         * ==========================
         */

        const totalY =
            (
                doc.lastAutoTable?.finalY ||
                75
            ) + 12;


        doc.setFont(
            'helvetica',
            'bold'
        );


        doc.text(
            `Total: Rp ${total.toLocaleString(
                'id-ID'
            )}`,
            20,
            totalY
        );


        /**
         * ==========================
         * FOOTER
         * ==========================
         */

        doc.setFont(
            'helvetica',
            'normal'
        );


        doc.text(
            'Terima kasih atas kepercayaan Anda.',
            20,
            totalY + 12
        );


        /**
         * ==========================
         * DOWNLOAD
         * ==========================
         */

        const safeOrderId =
            String(orderId)
                .substring(0, 8);


        doc.save(
            `Bukti-Pembayaran-${safeOrderId}.pdf`
        );
    }


    /**
     * Pasang event listener ke tombol Download PDF
     */
    document
        .getElementById('downloadPaymentProofBtn')
        ?.addEventListener(
            'click',
            downloadPaymentProof
        );

</script>

@endsection
