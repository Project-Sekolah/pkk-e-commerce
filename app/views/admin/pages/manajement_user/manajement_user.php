<!-- <div class="main-content d-flex flex-column">
    <div class="d-block d-lg-none w-100 mb-3">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <header class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h2 class="mb-2 mb-md-0 d-none d-lg-block">Manajemen Pengguna</h2>
        
        <div class="d-flex align-items-center ms-auto">
            <h5 class="fw-bold mb-0 me-3">
                <?= isset($data['user']['username']) ? htmlspecialchars($data['user']['username']) : 'Pengguna' ?>
            </h5>
            
            <div class="profile-pic">
                <img 
                    src="<?= isset($data['user']['image']) ? htmlspecialchars($data['user']['image']) : 'https://via.placeholder.com/40' ?>" 
                    alt="Foto Profil" 
                    class="rounded-circle" 
                    width="40" 
                    height="40"
                >
            </div>
        </div>
    </header>

    <div class="row">
        <div class="col-12">
            <div class="card p-4">
                <h5 class="mb-3">Daftar Pengguna</h5>
                <div class="mb-4">
                    <h6 class="mb-2">Statistik Pengguna</h6>
                    <div style="max-width: 400px; margin: 0 auto;">
                        <canvas id="userChart2" width="400" height="120"></canvas>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="users-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th>Peran</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['users'] as $index => $user): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <form method="post" action="<?= BASEURL ?>/user/updateRole/<?= $user['id'] ?>" class="d-inline">
                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="buyer" <?= $user['role'] === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                                            <option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <?php if (isset($user['is_blocked']) && $user['is_blocked']): ?>
                                        <span class="badge bg-danger">Diblokir</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button 
                                        class="btn btn-info btn-sm me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#user-detail-modal"
                                        data-user-id="<?= $user['id'] ?>"
                                        data-username="<?= htmlspecialchars($user['username']) ?>"
                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-role="<?= htmlspecialchars($user['role']) ?>"
                                        data-created="<?= htmlspecialchars($user['created_at']) ?>"
                                        data-updated="<?= htmlspecialchars($user['updated_at']) ?>"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="<?= BASEURL ?>/user/toggleBlock/<?= $user['id'] ?>" class="btn btn-warning btn-sm">
                                        <?php if (isset($user['is_blocked']) && $user['is_blocked']): ?>
                                            <i class="bi bi-unlock-fill"></i>
                                        <?php else: ?>
                                            <i class="bi bi-lock-fill"></i>
                                        <?php endif; ?>
                                    </a>
                                    <a href="<?= BASEURL ?>/user/softDelete/<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Nonaktifkan akun ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="user-detail-modal" tabindex="-1" aria-labelledby="userDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailModalLabel">Detail Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Nama Pengguna:</strong> <span id="modal-username"></span>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> <span id="modal-email"></span>
                </div>
                <div class="mb-3">
                    <strong>Peran:</strong> <span id="modal-role"></span>
                </div>
                <div class="mb-3">
                    <strong>Tanggal Dibuat:</strong> <span id="modal-created"></span>
                </div>
                <div class="mb-3">
                    <strong>Terakhir Diperbarui:</strong> <span id="modal-updated"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Skrip untuk menginisialisasi DataTables
    $(document).ready(function() {
        $('#users-table').DataTable({
            // Untuk menghindari error CORS, hapus atau ganti ke lokal jika ada
            // "language": { "url": "<?= BASEURL ?>/assets/js/id.json" }
        });
    // Statistik chart user
    $(document).ready(function () {
        const userChart2 = document.getElementById('userChart2');
        if (userChart2 && typeof Chart !== 'undefined') {
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
                    maintainAspectRatio: false,
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

            const userCtx = userChart2.getContext('2d');
            new Chart(userCtx, userConfig);
        }
    });
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

      // Tampilkan data users di console
    console.log(<?= json_encode($data['users'] ?? [], JSON_PRETTY_PRINT); ?>);
</script> -->