@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Manajemen Pengguna</h2>
            <p class="text-muted small">Kelola akun, peran, dan hak akses pengguna sistem</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Pengguna</th>
                        <th>Email & Telepon</th>
                        <th>Peran (Role)</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $u)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $u->full_name }}</div>
                                <small class="text-muted">{{ '@' . $u->username }}</small>
                            </td>
                            <td>
                                <div>{{ $u->email }}</div>
                                <small class="text-muted">{{ $u->phone_number }}</small>
                            </td>
                            <td>
                                <form action="{{ route('admin.user.updateRole', $u->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 110px;">
                                        <option value="buyer" {{ $u->role === 'buyer' ? 'selected' : '' }}>Buyer</option>
                                        <option value="seller" {{ $u->role === 'seller' ? 'selected' : '' }}>Seller</option>
                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                @if($u->is_blocked)
                                    <span class="badge bg-danger">Diblokir</span>
                                @else
                                    <span class="badge bg-success">Aktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.user.toggleBlock', $u->id) }}"
                                       class="btn btn-sm {{ $u->is_blocked ? 'btn-success' : 'btn-warning' }}"
                                       title="{{ $u->is_blocked ? 'Buka Blokir' : 'Blokir Akun' }}">
                                        <i class="bi {{ $u->is_blocked ? 'bi-unlock-fill' : 'bi-lock-fill' }}"></i>
                                    </a>
                                    <a href="{{ route('admin.user.softDelete', $u->id) }}"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Nonaktifkan akun pengguna ini?');"
                                       title="Nonaktifkan">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection