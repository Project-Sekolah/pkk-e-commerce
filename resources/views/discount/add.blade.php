@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 600px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('discounts.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Tambah Diskon Baru</h2>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <form action="{{ route('discounts.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama / Kode Diskon <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: MERDEKA20">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Persentase Potongan (%) <span class="text-danger">*</span></label>
                <input type="number" name="percentage" class="form-control" value="{{ old('percentage') }}" required min="1" max="100" placeholder="20">
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_date" class="form-control" required value="{{ old('start_date') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="end_date" class="form-control" required value="{{ old('end_date') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('discounts.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Diskon</button>
            </div>
        </form>
    </div>
</div>
@endsection