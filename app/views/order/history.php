<?php
$orders = $data['orders'] ?? [];
$userPhone = $data['user']['phone_number'] ?? '-';
?>

<div class="container py-4 mt-4">
    <?php Flasher::flash(); ?>
    <?php if (!empty($orders)): ?>
        <table id="ordersTable" class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>ID Order</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Jumlah Item</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']); ?></td>
                        <td>
                            <?php 
                                $tgl = date("Y-m-d", strtotime($order['created_at']));
                                echo htmlspecialchars($tgl);
                            ?>
                        </td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($order['status']); ?></span></td>
                        <td>Rp <?= number_format($order['total'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($order['item_count']); ?></td>
                        <td>
                            <a href="<?= BASEURL . '/order/detail/' . $order['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                            <button 
                                class="btn btn-sm btn-success"
                                onclick='downloadStruk(
                                    <?= json_encode($order['id']); ?>,
                                    <?= json_encode(number_format($order['total'], 0, ',', '.')); ?>,
                                    <?= json_encode($tgl); ?>,
                                    <?= json_encode($userPhone); ?>,
                                    <?= json_encode($order['items'] ?? []); ?>
                                )'>
                                Download Struk
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">Belum ada order.</div>
    <?php endif; ?>
</div>

<!-- Include jQuery & DataTables CSS/JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#ordersTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[1, 'desc']] // default sorting by tanggal desc
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
        doc.text(`ID Transaksi: ${orderId}`, 20, 35);
        doc.text(`Nomor HP   : ${phone || "-"}`, 20, 42);
        doc.text(`Tanggal     : ${tanggal}`, 20, 49);

        const body = items.map(item => [
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
            headStyles: { fillColor: [39, 174, 96] },
        });

        let finalY = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(13);
        doc.setFont("helvetica", "bold");
        doc.text(`TOTAL: Rp ${total}`, 20, finalY);

        finalY += 20;
        doc.setFontSize(10);
        doc.setFont("helvetica", "italic");
        doc.text("Terima kasih telah berbelanja di PKK E-Commerce.", 105, finalY, { align: "center" });

        const pdfBlobUrl = doc.output("bloburl");
        const previewWindow = window.open(pdfBlobUrl, "_blank");

        if (previewWindow) {
            Swal.fire({
                title: "Preview Struk",
                text: "Apakah struk sudah sesuai dan ingin diunduh?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Download",
                cancelButtonText: "Batal",
                confirmButtonColor: "#27ae60",
                cancelButtonColor: "#c0392b"
            }).then(result => {
                if (result.isConfirmed) {
                    doc.save(`struk-${orderId}.pdf`);
                }
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Membuat Struk',
            text: err.message,
            confirmButtonColor: "#c0392b"
        });
    }
}
</script>
