@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Feedback Form -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h4 class="fw-bold mb-3">Kirim Masukan Anda</h4>
                <form action="https://formspree.io/f/xgvkreeb" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pesan / Masukan</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FAQ Header -->
    <div class="text-center mb-4">
        <h2 id="faqTitle" class="fw-bold">Frequently Asked Questions (FAQ)</h2>
        <p id="faqDescription" class="text-muted">Pertanyaan yang sering diajukan pelanggan kami.</p>
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary btn-sm" onclick="toggleLanguage('id')">
                <i class="bi bi-globe me-1"></i> Bahasa Indonesia
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="toggleLanguage('en')">
                <i class="bi bi-globe me-1"></i> English
            </button>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion shadow-sm rounded-3" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            <span id="faq1Title">Apa tujuan website ini?</span>
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted" id="faq1Body">
                            Website ini adalah toko online tempat pengguna dapat menelusuri dan membeli produk fashion dan gaya hidup dengan mudah dan aman.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            <span id="faq2Title">Bagaimana cara menambahkan barang ke keranjang?</span>
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted" id="faq2Body">
                            Cukup klik tombol ikon keranjang pada produk yang diinginkan, dan produk akan langsung masuk ke keranjang belanja Anda.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            <span id="faq3Title">Bagaimana cara mendaftar akun?</span>
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted" id="faq3Body">
                            Buka menu sidebar di pojok kiri atas, pilih "Belum punya akun? Daftar", lengkapi form pendaftaran, lalu klik Daftar.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            <span id="faq4Title">Bagaimana cara melakukan checkout pesanan?</span>
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted" id="faq4Body">
                            Pastikan Anda telah login dan menambahkan alamat pengiriman utama di profil, buka keranjang belanja, masukkan kode diskon jika ada, lalu klik tombol Checkout.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLanguage(lang) {
    if (lang === 'en') {
        document.getElementById('faqTitle').innerText = "Frequently Asked Questions (FAQ)";
        document.getElementById('faqDescription').innerText = "Find quick answers to common questions below.";
        document.getElementById('faq1Title').innerText = "What is this website for?";
        document.getElementById('faq1Body').innerText = "This website is an online store where users can browse and purchase quality lifestyle and fashion products conveniently.";
        document.getElementById('faq2Title').innerText = "How do I add items to my cart?";
        document.getElementById('faq2Body').innerText = "Simply click the cart icon button on any product card, and it will appear in your shopping cart.";
        document.getElementById('faq3Title').innerText = "How do I register an account?";
        document.getElementById('faq3Body').innerText = "Click the sidebar button on the top left, select 'Don't have an account? Register', fill in the form, and submit.";
        document.getElementById('faq4Title').innerText = "How do I checkout an order?";
        document.getElementById('faq4Body').innerText = "Ensure you are logged in and have added a default address in your profile, open your shopping cart, and click Checkout.";
    } else {
        document.getElementById('faqTitle').innerText = "Frequently Asked Questions (FAQ)";
        document.getElementById('faqDescription').innerText = "Pertanyaan yang sering diajukan pelanggan kami.";
        document.getElementById('faq1Title').innerText = "Apa tujuan website ini?";
        document.getElementById('faq1Body').innerText = "Website ini adalah toko online tempat pengguna dapat menelusuri dan membeli produk fashion dan gaya hidup dengan mudah dan aman.";
        document.getElementById('faq2Title').innerText = "Bagaimana cara menambahkan barang ke keranjang?";
        document.getElementById('faq2Body').innerText = "Cukup klik tombol ikon keranjang pada produk yang diinginkan, dan produk akan langsung masuk ke keranjang belanja Anda.";
        document.getElementById('faq3Title').innerText = "Bagaimana cara mendaftar akun?";
        document.getElementById('faq3Body').innerText = "Buka menu sidebar di pojok kiri atas, pilih 'Belum punya akun? Daftar', lengkapi form pendaftaran, lalu klik Daftar.";
        document.getElementById('faq4Title').innerText = "Bagaimana cara melakukan checkout pesanan?";
        document.getElementById('faq4Body').innerText = "Pastikan Anda telah login dan menambahkan alamat pengiriman utama di profil, buka keranjang belanja, masukkan kode diskon jika ada, lalu klik tombol Checkout.";
    }
}
</script>
@endsection