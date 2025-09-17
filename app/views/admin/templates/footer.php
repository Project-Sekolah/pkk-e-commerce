</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#top-selling-products-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/id.json"
                }
            });
        });

        const salesData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
            datasets: [{
                label: 'Penjualan',
                data: [65, 59, 80, 81, 56, 55, 40],
                borderColor: 'rgb(139, 154, 109)',
                backgroundColor: 'rgba(139, 154, 109, 0.5)',
                tension: 0.4,
                fill: true,
                pointStyle: 'circle',
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        };

        const salesConfig = {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    title: {
                        display: true,
                        text: 'Penjualan Bulanan',
                        padding: {
                            top: 10,
                            bottom: 10
                        },
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, salesConfig);

        const userData = {
            labels: ['Buyer', 'Seller', 'Admin'],
            datasets: [{
                label: 'Jumlah Pengguna',
                data: [75, 20, 5],
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
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let label = tooltipItem.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += tooltipItem.raw + '%';
                                return label;
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribusi Pengguna',
                        padding: {
                            top: 10,
                            bottom: 10
                        },
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            }
        };

        const userCtx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(userCtx, userConfig);
    </script>
</body>
</html>