@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Profil -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 text-center p-4">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <img src="{{ $user->image ? $user->image : asset('assets/img/default.jpg') }}"
                         alt="{{ $user->full_name }}" class="rounded-circle border shadow-sm" width="120" height="120" style="object-fit: cover;">
                </div>
                <h4 class="fw-bold mb-0">{{ $user->full_name }}</h4>
                <small class="text-muted mb-2 d-block">{{ '@' . $user->username }}</small>
                <span class="badge bg-dark text-uppercase mx-auto mb-3">{{ $user->role }}</span>
                <p class="text-muted small mb-1"><i class="bi bi-envelope me-1"></i> {{ $user->email }}</p>
                <p class="text-muted small mb-0"><i class="bi bi-telephone me-1"></i> {{ $user->phone_number }}</p>
            </div>
        </div>

        <!-- Form Edit & Alamat -->
        <div class="col-lg-8">
            <!-- Edit Biodata -->
            <div class="card shadow-sm border-0 p-4 mb-4">
                <h5 class="fw-bold mb-3">Perbarui Profil</h5>
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Telepon</label>
                            <input type="tel" name="phone_number" class="form-control" value="{{ old('phone_number', $user->phone_number) }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Foto Profil Baru</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </form>
            </div>

            @if($user->role === 'buyer')
                <div class="card shadow-sm border-0 p-4 mb-4">
                    <h5 class="fw-bold mb-2">Menjadi Seller</h5>
                    <p class="text-muted small">Lengkapi profil, foto, alamat utama, dan verifikasi password untuk mulai menjual produk.</p>
                    <form action="{{ route('user.become-seller') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Toko</label>
                            <input type="text" name="shop_name" class="form-control" required placeholder="Contoh: Toko Resmi Saya">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alamat Toko</label>
                            <textarea name="shop_address" class="form-control" rows="2" required placeholder="Alamat operasional toko"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">NPWP atau Dokumen Pendukung (Opsional)</label>
                            <input type="file" name="shop_document" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="seller_agreement" value="1" id="sellerAgreement" required>
                            <label class="form-check-label small" for="sellerAgreement">
                                Saya menyetujui tanggung jawab dan kebijakan sebagai seller.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Ajukan Menjadi Seller</button>
                    </form>
                </div>
            @endif

            <!-- Ganti Password -->
            <div class="card shadow-sm border-0 p-4 mb-4">
                <h5 class="fw-bold mb-3">Ganti Password</h5>
                <form action="{{ route('user.password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-dark px-4">Perbarui Password</button>
                </form>
            </div>

            <!-- Alamat Pengiriman -->
            <div class="card shadow-sm border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Alamat Pengiriman</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Alamat
                    </button>
                </div>

                @if($addresses->isEmpty())
                    <div class="alert alert-warning small mb-0">
                        Anda belum menambahkan alamat pengiriman. Tambahkan minimal 1 alamat untuk dapat melakukan checkout belanja.
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($addresses as $addr)
                            <div class="border rounded p-3 {{ $addr->is_default ? 'border-primary bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="fw-bold">
                                        {{ $addr->label ?? 'Alamat' }}
                                        @if($addr->is_default)
                                            <span class="badge bg-primary ms-1">Utama</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if(!$addr->is_default)
                                            <form action="{{ route('user.address.default', $addr->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-primary btn-sm py-0">Jadikan Utama</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('user.address.delete', $addr->id) }}" method="POST" onsubmit="return confirm('Hapus alamat ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger btn-sm py-0">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mb-1 text-dark small">{{ $addr->address_line_1 }}</p>
                                @if($addr->address_line_2)
                                    <p class="mb-1 text-muted small">{{ $addr->address_line_2 }}</p>
                                @endif
                                <small class="text-muted">{{ $addr->city }}, {{ $addr->postal_code }} - {{ $addr->country }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Alamat -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Alamat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.address.add') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Label Alamat</label>
                        <input type="text" name="label" class="form-control" placeholder="Contoh: Rumah, Kantor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Lengkap</label>
                        <input type="text" name="address_line_1" class="form-control" placeholder="Nama jalan, nomor rumah/gedung" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Detail Tambahan (Opsional)</label>
                        <input type="text" name="address_line_2" class="form-control" placeholder="Patokan, RT/RW, blok">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Kota / Kabupaten</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Kode Pos</label>
                            <input type="text" name="postal_code" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defaultCheck">
                        <label class="form-check-label small" for="defaultCheck">
                            Jadikan sebagai alamat utama pengiriman
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Alamat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection