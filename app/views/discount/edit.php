<div class="container my-5">
    <h4 id="editDiscount" class="mb-4">Edit Diskon</h4>
    <form action="<?= BASEURL ?>/discount/update/<?= $data["discount"]["id"] ?>" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Diskon</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= $data["discount"]["name"] ?>" required>
        </div>
        <div class="mb-3">
            <label for="percentage" class="form-label">Persentase Diskon</label>
            <input type="number" class="form-control" id="percentage" name="percentage" value="<?= $data["discount"]["percentage"] ?>" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Tanggal Mulai</label>
            <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= $data["discount"]["start_date"] ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Tanggal Berakhir</label>
            <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= $data["discount"]["end_date"] ?>" required>
        </div>
        <div class="mb-3">
            <label for="is_active" class="form-label">Status</label>
            <select class="form-select" id="_activeis" name="is_active" required>
                <option value="1" <?= $data["discount"]["is_active"] == 1 ? "selected" : "" ?>>Aktif</option>
                <option value="0" <?= $data["discount"]["is_active"] == 0 ? "selected" : "" ?>>Tidak Aktif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
