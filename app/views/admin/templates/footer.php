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
</script>
</body>
</html>
