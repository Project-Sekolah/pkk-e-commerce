   
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>

<script>
    const allData = <?php echo json_encode($data); ?>;
    console.log("Data lengkap:", JSON.stringify(allData, null, 2));
</script>

<script>
    

    $(document).ready(function () {
        // Init DataTable
        $('#top-selling-products-table').DataTable();


        // Chart Distribusi Pengguna
        const userTypeCounts = <?php echo isset($data['user_type_counts']) 
            ? json_encode(array_values($data['user_type_counts'])) 
            : '[0,0,0]'; ?>;
        const userData = {
            labels: ['Buyer', 'Seller', 'Admin'],
            datasets: [{
                label: 'Jumlah Pengguna',
                data: userTypeCounts,
                backgroundColor: [
                    'rgba(70, 85, 95, 0.8)',
                    'rgba(139, 154, 109, 0.8)',
                    'rgba(62, 42, 71, 0.8)'
                ],
                hoverOffset: 10
            }]
        };

        const userConfig = {
            type: 'doughnut',
            data: userData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                let label = tooltipItem.label || '';
                                if (label) label += ': ';
                                label += tooltipItem.raw;
                                return label;
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribusi Pengguna',
                        padding: { top: 10, bottom: 10 },
                        font: { size: 16, weight: 'bold' }
                    }
                }
            }
        };

        const userCtx = document.getElementById('userChart').getContext('2d');
        new Chart(userCtx, userConfig);
    });


        // Grafik Penjualan Bulanan
        const monthlySales = <?php echo isset($data['monthly_sales']) ? json_encode($data['monthly_sales']) : '[]'; ?>;
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        // Buat array 12 bulan, isi 0 jika tidak ada data
        const salesDataArr = Array(12).fill(0);
        monthlySales.forEach(item => {
            if (item.month && item.total) {
                salesDataArr[item.month - 1] = item.total;
            }
        });
        const salesData = {
            labels: monthNames,
            datasets: [{
                label: 'Penjualan Bulanan',
                data: salesDataArr,
                borderColor: 'rgba(62, 42, 71, 0.9)',
                backgroundColor: 'rgba(139, 154, 109, 0.3)',
                pointBackgroundColor: 'rgba(62, 42, 71, 1)',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 8,
                tension: 0.4,
                fill: true
            }]
        };
        const salesConfig = {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return 'Penjualan: ' + context.parsed.y;
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Grafik Penjualan Bulanan',
                        font: { size: 16, weight: 'bold' },
                        padding: { top: 10, bottom: 10 }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#eee' } }
                }
            }
        };
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, salesConfig);
</script>



<script>
    // Skrip untuk menginisialisasi DataTables
    $(document).ready(function() {
        $('#users-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/id.json"
            }
        });

        // Statistik chart user
        var ctx = document.getElementByClass('userChart');
        if (ctx) {
            var userData = <?php
                $roles = ['buyer' => 0, 'seller' => 0, 'admin' => 0];
                foreach ($data['users'] as $u) {
                    if (isset($u['role'])) {
                        $roles[$u['role']] = isset($roles[$u['role']]) ? $roles[$u['role']] + 1 : 1;
                    }
                }
                echo json_encode(array_values($roles));
            ?>;
            var userLabels = ['Buyer', 'Seller', 'Admin'];
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: userLabels,
                    datasets: [{
                        data: userData,
                        backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

    });

    // Skrip untuk mengisi data ke modal saat tombol detail diklik
    var userDetailModal = document.getElementById('user-detail-modal');
    userDetailModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var username = button.getAttribute('data-username');
        var email = button.getAttribute('data-email');
        var role = button.getAttribute('data-role');
        var created = button.getAttribute('data-created');
        var updated = button.getAttribute('data-updated');

        var modalUsername = userDetailModal.querySelector('#modal-username');
        var modalEmail = userDetailModal.querySelector('#modal-email');
        var modalRole = userDetailModal.querySelector('#modal-role');
        var modalCreated = userDetailModal.querySelector('#modal-created');
        var modalUpdated = userDetailModal.querySelector('#modal-updated');

        modalUsername.textContent = username || '-';
        modalEmail.textContent = email || '-';
        modalRole.textContent = role || '-';
        modalCreated.textContent = created || '-';
        modalUpdated.textContent = updated || '-';
    });


    $(document).ready(function () {
    // Data user dari PHP
    const userTypeCounts = <?php 
        $roles = ['buyer'=>0,'seller'=>0,'admin'=>0];
        foreach($data['users'] as $u){
            if(isset($u['role'])) $roles[$u['role']]++;
        }
        echo json_encode(array_values($roles));
    ?>;

    const userData = {
        labels: ['Buyer', 'Seller', 'Admin'],
        datasets: [{
            label: 'Jumlah Pengguna',
            data: userTypeCounts,
            backgroundColor: ['#36A2EB', '#FFCE56', '#FF6384'],
            hoverOffset: 10
        }]
    };

    const userConfig = {
        type: 'doughnut',
        data: userData,
        options: {
            responsive: true,
            maintainAspectRatio: false, // penting agar chart tidak melebar
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: true,
                    text: 'Distribusi Pengguna',
                    padding: { top: 10, bottom: 10 },
                    font: { size: 16, weight: 'bold' }
                }
            }
        }
    };

    const userCtx = document.getElementById('userChart2').getContext('2d');
    new Chart(userCtx, userConfig);

    // Tampilkan data users di console
    console.log(<?= json_encode($data['users'] ?? [], JSON_PRETTY_PRINT); ?>);
});


      // Tampilkan data users di console
    console.log(<?= json_encode($data['users'] ?? [], JSON_PRETTY_PRINT); ?>);
</script>
</body>
</html>
