<div class="container mt-4">
    <h3>Profil Pengguna</h3>
    <div class="row">
        <div class="col-md-6">
            <table class="table">
                <tr>
                    <th>Username</th>
                    <td><?= htmlspecialchars($data['user']['username']); ?></td>
                </tr>
                <tr>
                    <th>Full Name</th>
                    <td><?= htmlspecialchars($data['user']['full_name']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($data['user']['email']); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?= htmlspecialchars($data['user']['role']); ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <!-- Tambahkan gambar profil atau fitur lainnya jika diinginkan -->
        </div>
    </div>
</div>


