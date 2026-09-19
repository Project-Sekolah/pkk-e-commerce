@extends('layouts.app')

@section('content')
<section class="py-5 bg-dark text-white text-center">
    <div class="container py-4">
        <h1 class="display-4 fw-bold">Lunerburg & Co</h1>
        <p class="lead text-light">Cerita kami, mimpi kami, dan komitmen abadi pada kualitas.</p>
    </div>
</section>

<section class="container py-5">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <h2 class="fw-bold mb-4">Tentang Kami</h2>
            <p class="lead text-muted mb-4">
                Segalanya bermula dari sebuah ruangan kecil di lantai dua rumah tua yang menghadap danau di kota fiktif <strong>Elmbrook</strong>, awal 2019. Seorang pemuda bernama <strong>Rajendra Athallah Fawwaz</strong> dan timnya mulai merancang mimpi kecil: menciptakan toko barang unik yang penuh makna.
            </p>
            <p class="text-muted">
                Mereka saling menopang sebagai 5 sahabat dekat: <strong>Najla Ajauza</strong>, <strong>Syafira Azahra Br. Ginting</strong>, <strong>Brenda Estella Silalahi</strong>, <strong>Alzira Ukhty Zaskia</strong>, dan <strong>Haidil Habibi</strong>. Bersama, mereka membentuk <strong>Lunerburg & Co</strong>. Perjalanan resmi dimulai pada <strong>21 November 2019</strong>.
            </p>
        </div>
    </div>

    <hr class="my-5">

    <h2 class="text-center fw-bold mb-5">Para Pendiri Kami</h2>
    <div class="row g-4 justify-content-center">
        <!-- Rajendra -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center h-100 p-3">
                <img src="{{ asset('assets/img/rajendra.jpg') }}" alt="Rajendra" class="rounded-circle mx-auto mt-3 border" width="100" height="100" style="object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Rajendra Athallah Fawwaz</h5>
                    <h6 class="text-muted mb-2 small">Founder & Creative Director</h6>
                    <blockquote class="blockquote-footer fst-italic small">“Saya ingin setiap barang yang kami jual terasa seperti hadiah yang penuh makna.”</blockquote>
                </div>
            </div>
        </div>

        <!-- Najla -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center h-100 p-3">
                <img src="{{ asset('assets/img/najla.jpg') }}" alt="Najla" class="rounded-circle mx-auto mt-3 border" width="100" height="100" style="object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Najla Ajauza</h5>
                    <h6 class="text-muted mb-2 small">Co-Founder & Head of Visual Arts</h6>
                    <blockquote class="blockquote-footer fst-italic small">“Saya ingin orang tersenyum ketika membuka paket kami.”</blockquote>
                </div>
            </div>
        </div>

        <!-- Syafira -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center h-100 p-3">
                <img src="{{ asset('assets/img/pira.jpg') }}" alt="Syafira" class="rounded-circle mx-auto mt-3 border" width="100" height="100" style="object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Syafira Azahra Br Ginting</h5>
                    <h6 class="text-muted mb-2 small">Co-Founder & Sustainability Strategist</h6>
                    <blockquote class="blockquote-footer fst-italic small">“Kita bisa hidup nyaman tanpa meninggalkan jejak yang merusak.”</blockquote>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <div class="row g-4 text-center justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 p-4 h-100">
                <i class="bi bi-eye-fill fs-1 text-primary mb-3"></i>
                <h4 class="fw-bold mb-3">Visi Kami</h4>
                <p class="text-muted">Menjadi merek lokal yang mendunia dengan produk bernilai, bercerita, dan penuh sentuhan personal.</p>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm border-0 p-4 h-100">
                <i class="bi bi-flag-fill fs-1 text-success mb-3"></i>
                <h4 class="fw-bold mb-3">Misi Kami</h4>
                <ul class="list-unstyled text-muted text-start mb-0">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Desain orisinal dan kualitas tinggi</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Dukungan terhadap pengrajin lokal</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Edukasi tentang keberlanjutan dan mindful living</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Contact & Map -->
    <div class="mt-5 pt-4">
        <h3 class="fw-bold text-center mb-4">Lokasi & Kontak Kami</h3>
        <div class="text-center text-muted mb-4">
            <p class="mb-1"><strong>Email:</strong> lunerburgh&co@gmail.com | <strong>Telepon:</strong> +62 812-3456-7890</p>
            <p>Jl. Lunerburgh No. 123, Medan, Indonesia</p>
        </div>
        <div id="leaflet-map" style="height: 350px; border-radius: 12px;" class="shadow-sm"></div>
    </div>
</section>

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    const cafeLocation = [-6.200000, 106.816666];
    const map = L.map('leaflet-map').setView(cafeLocation, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    L.marker(cafeLocation).addTo(map)
        .bindPopup('<b>Lunerburg & Co</b><br>Jl. Lunerburgh No. 123, Medan')
        .openPopup();
</script>
@endpush
@endsection