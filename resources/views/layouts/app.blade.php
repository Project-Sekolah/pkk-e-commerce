<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F0E8DA">
    <meta name="description" content="Toko online Lunerburg & Co. Menyediakan produk-produk terbaik dengan layanan checkout, login, register, dan FAQ.">
    <meta name="keywords" content="lunerburg, e-commerce, produk, checkout, login, register, FAQ, belanja online">
    <meta name="author" content="Lunerburg & Co">

    <title>{{ $judul ?? 'Lunerburg & Co - Toko Online Terpercaya' }}</title>

    <link rel="icon" href="https://res.cloudinary.com/dripo32vz/image/upload/v1757645375/8dcd840d-3393-4a92-b52a-d2d116a63a51.png" type="image/png">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jsPDF & AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ time() }}">

    <!-- Icons & Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite('resources/js/app.js')

    <script>
        const BASEURL = "{{ url('/') }}";
        const IS_LOGGED_IN = {{ Auth::check() ? 'true' : 'false' }};
    </script>
</head>
<body>

    <!-- NAVBAR TOPBAR -->
    <nav class="navbar navbar-expand-lg topbar fixed-top shadow-sm">
        <div class="container-fluid d-flex justify-content-between px-3">
            <button class="btn border-0 me-2" id="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="bi bi-list" style="font-size: 1.5rem;"></i>
            </button>
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">Lunerburg & Co</a>
            <div class="me-3 position-relative">
                <button class="btn btn-light position-relative" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
                    <i class="bi bi-cart3 fs-5"></i>
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- OFFCANVAS CART -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasCartLabel" class="fw-bold">Shopping Cart</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- List Keranjang -->
            <ul class="list-group mb-4" id="cart-items"></ul>

            <!-- Ringkasan Pembayaran -->
            <div class="summary-box p-4 border rounded shadow-sm bg-light">
                <h5 class="mb-3 fw-bold">Ringkasan Belanja</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span id="subtotal">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkir & Penanganan</span>
                    <span id="delivery">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Pajak & Biaya</span>
                    <span id="taxes">Rp 0</span>
                </div>

                <div class="form-check mb-3 p-0">
                    <label for="discountInput" class="form-label">Nama Diskon</label>
                    <div class="input-group">
                        <input type="text" id="discountInput" class="form-control" placeholder="Masukkan kode diskon">
                        <button class="btn btn-outline-primary" id="applyDiscountBtn">Terapkan</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Diskon</span>
                    <span id="discount" class="text-danger">- Rp 0</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
                    <span>Total</span>
                    <span id="total">Rp 0</span>
                </div>

                @auth
                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->phone_number }}" readonly>
                    </div>
                    <form action="{{ route('order.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="discount_name" id="checkoutDiscountName" value="">
                        <div class="mb-3">
                            <label class="form-label">Alamat Pengiriman</label>
                            <select name="address_id" class="form-select" required>
                                @foreach(Auth::user()->addresses as $address)
                                    <option value="{{ $address->id }}" {{ $address->is_default ? 'selected' : '' }}>
                                        {{ $address->label ?: 'Alamat' }} - {{ $address->address_line_1 }}, {{ $address->city }}
                                    </option>
                                @endforeach
                            </select>
                            @if(Auth::user()->addresses->isEmpty())
                                <small class="text-danger">Tambahkan alamat terlebih dahulu di profil.</small>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kurir</label>
                            <select name="courier" id="checkoutCourier" class="form-select" required>
                                <option value="jne_reg" data-fee="15000">JNE Reguler - Rp 15.000</option>
                                <option value="jnt_reg" data-fee="14000">J&T Reguler - Rp 14.000</option>
                                <option value="sicepat_reg" data-fee="16000">SiCepat Reguler - Rp 16.000</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required checked>
                            <label class="form-check-label small" for="agreeTerms">
                                Saya menyetujui <a href="#">syarat & ketentuan</a>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2 py-2 fw-semibold">Checkout Sekarang</button>
                    </form>
                @else
                    <div class="alert alert-info small text-center">
                        Silakan login melalui menu sidebar untuk melakukan checkout.
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- OFFCANVAS SIDEBAR -->
    <div class="offcanvas offcanvas-start overflow-auto" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <img src="{{ asset('assets/img/logotoko.png') }}" alt="Lunerburg & Co" class="img-fluid" style="height: 40px;">
            <h5 class="sidebar-title ms-2 mb-0 fw-bold">Lunerburg & Co</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>

        @guest
            <!-- Login / Register Section -->
            <div id="auth-section">
                <div class="px-3 py-2">
                    <!-- Login Form -->
                    <div id="login-section">
                        <h5 class="mb-3 fw-bold">Login</h5>
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="login-username" class="form-label">Username atau Email</label>
                                <input type="text" class="form-control" id="login-username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="login-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="login-password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                        </form>
                        <div class="mt-2 text-center">
                            <span class="toggle-link text-primary" onclick="showRegister()" style="cursor: pointer;">
                                Belum punya akun? Daftar
                            </span>
                        </div>
                    </div>

                    <!-- Register Form -->
                    <div id="register-section" style="display: none;">
                        <h5 class="mb-3 fw-bold">Daftar Akun Baru</h5>
                        <form action="{{ route('register') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="reg-username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="reg-username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="reg-fullname" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="reg-fullname" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="reg-phone" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="reg-phone" name="phone_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="reg-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="reg-email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="reg-password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="reg-password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="reg-confirm-password" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="reg-confirm-password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">Daftar Sekarang</button>
                        </form>
                        <div class="mt-2 text-center">
                            <span class="toggle-link text-primary" onclick="showLogin()" style="cursor: pointer;">
                                Sudah punya akun? Login
                            </span>
                        </div>
                    </div>
                </div>
                <hr>
            </div>
        @endguest

        @auth
            <!-- User Info -->
            <div class="px-3 py-2" id="user-info-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold">{{ Auth::user()->full_name }}</h6>
                        <small class="text-muted">{{ Auth::user()->email }}</small>
                        <br>
                        <span class="badge bg-secondary text-uppercase">{{ Auth::user()->role }}</span>
                    </div>
                    <img src="{{ Auth::user()->image ? Auth::user()->image : asset('assets/img/default.jpg') }}"
                         alt="Foto Profil" class="rounded-circle border" width="48" height="48" style="object-fit: cover;">
                </div>
                <div class="mt-3">
                    <a href="{{ route('user.profile') }}" class="btn btn-outline-primary btn-sm w-100">Lihat Profil & Alamat</a>
                </div>
                <hr>
            </div>
        @endauth

        <!-- Navigation Links -->
        <div class="px-3">
            <a href="{{ route('home') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                <i class="bi bi-house-door-fill me-2 fs-5"></i><span>Home</span>
            </a>
            <a href="{{ route('about') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i><span>About</span>
            </a>
            <a href="{{ route('products.index') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                <i class="bi bi-bag-fill me-2 fs-5"></i><span>Produk</span>
            </a>
            @auth
                @if(Auth::user()->isSeller())
                    <a href="{{ route('products.storefront', Auth::user()->id) }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                        <i class="bi bi-shop me-2 fs-5"></i><span>Toko Saya</span>
                    </a>
                @endif
            @endauth
            @auth
                <a href="{{ route('order.history') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                    <i class="bi bi-hourglass-split me-2 fs-5"></i><span>Riwayat Order</span>
                </a>
            @endauth
            <a href="{{ route('faq') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                <i class="bi bi-question-circle-fill me-2 fs-5"></i><span>FAQ</span>
            </a>
            <hr>

            @auth
                @if(Auth::user()->isSeller())
                    <a href="{{ route('products.seller') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                        <i class="bi bi-cart-fill me-2 fs-5"></i><span>Produk Saya</span>
                    </a>
                    <a href="{{ route('products.sales-report') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                        <i class="bi bi-bar-chart-line-fill me-2 fs-5"></i><span>Laporan Penjualan</span>
                    </a>
                    <a href="{{ route('discounts.index') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                        <i class="bi bi-tag-fill me-2 fs-5"></i><span>Diskon Saya</span>
                    </a>
                @endif
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                        <i class="bi bi-speedometer2 me-2 fs-5"></i><span>Admin Dashboard</span>
                    </a>
                    <a href="{{ route('admin.users') }}" class="d-flex align-items-center mb-3 text-decoration-none text-dark">
                        <i class="bi bi-people-fill me-2 fs-5"></i><span>Manajemen Pengguna</span>
                    </a>
                @endif
                <a href="{{ route('logout') }}" class="d-flex align-items-center mb-3 text-decoration-none text-danger"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-door-closed-fill me-2 fs-5"></i><span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endauth
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main style="padding-top: 60px; min-height: 80vh;">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="footer bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">&copy; {{ date('Y') }} <strong>Lunerburg & Co</strong>. All rights reserved.</p>
            <small class="text-muted">Dibuat dengan dedikasi, kualitas, dan gaya abadi.</small>
        </div>
    </footer>

    <!-- PRODUCT DETAIL MODAL -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal text-dark">
                <button type="button" class="btn-close btn-close-modal m-2 ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body px-4 py-3">
                    <div class="row g-4">
                        <div class="col-md-5 d-flex justify-content-center align-items-center">
                            <div class="modal-image-container text-center w-100">
                                <div id="modalImageGallery" class="carousel slide" data-bs-ride="false">
                                    <div id="modalImageSlides" class="carousel-inner rounded"></div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#modalImageGallery" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#modalImageGallery" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                                </div>
                                <div class="price-tag mt-2 fs-5 fw-bold text-primary">
                                    Rp <span id="modalPrice"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 modal-product-info">
                            <h4 id="modalTitle" class="fw-bold mb-2"></h4>
                            <hr class="my-2">
                            <div class="mb-2 small">
                                <p class="mb-1"><i class="bi bi-tags-fill me-2"></i><strong>Kategori:</strong> <span id="modalCategory"></span></p>
                                <p class="mb-1"><i class="bi bi-gender-ambiguous me-2"></i><strong>Gender:</strong> <span id="modalGender"></span></p>
                                <p class="mb-1"><i class="bi bi-box-seam me-2"></i><strong>Stok:</strong> <span id="modalStock"></span></p>
                                <p class="mb-1"><i class="bi bi-person-fill me-2"></i><strong>Penjual:</strong> <a id="modalOwnerLink" href="#" class="text-success text-decoration-none"><span id="modalOwnerName"></span></a></p>
                                <p class="mb-1"><i class="bi bi-chat-left-text-fill me-2"></i><strong>Komentar:</strong> <span id="modalRatingCount">0 komentar</span></p>
                            </div>
                            <p id="modalDescription" class="small text-muted mb-3"></p>

                            <a id="modalDetailLink" href="#" class="btn btn-sm btn-outline-dark mb-3">
                                <i class="bi bi-eye me-1"></i> Lihat Detail Lengkap
                            </a>

                            <div class="product-comments mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">Komentar pelanggan</h6>
                                    <span class="small text-muted" id="modalCommentsStatus">Memuat...</span>
                                </div>
                                <div id="modalCommentsContainer" class="border-top pt-3"></div>
                                <div id="modalCommentsPagination" class="d-flex justify-content-between align-items-center mt-3"></div>
                            </div>

                            <button id="modalAddToCartBtn" class="btn btn-primary add-to-cart w-100 mb-3" data-id="">
                                <i class="bi bi-cart-plus-fill me-1"></i> Tambah ke Keranjang
                            </button>
                        </div>
                    </div>

                    @auth
                        <hr>
                        <!-- Rating Form -->
                        <form action="{{ route('product.rating') }}" method="POST" id="ratingForm" class="mt-3">
                            @csrf
                            <input type="hidden" name="product_id" id="ratingProductId">
                            <input type="hidden" name="rating" id="ratingValue" value="5">

                            <h6 class="fw-bold">Beri Ulasan Produk</h6>
                            <div id="starInput" class="text-warning mb-2 fs-5" style="cursor: pointer;">
                                <i class="bi bi-star-fill star-item" data-val="1"></i>
                                <i class="bi bi-star-fill star-item" data-val="2"></i>
                                <i class="bi bi-star-fill star-item" data-val="3"></i>
                                <i class="bi bi-star-fill star-item" data-val="4"></i>
                                <i class="bi bi-star-fill star-item" data-val="5"></i>
                            </div>
                            <textarea class="form-control mb-2" name="review_text" rows="2" placeholder="Tulis komentar ulasan Anda..."></textarea>
                            <button type="submit" class="btn btn-sm btn-primary">Kirim Ulasan</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert Session Handlers -->
    @if(session('alert'))
        <script>
            Swal.fire({
                title: "{{ session('alert')['type'] === 'success' ? 'Sukses!' : 'Peringatan' }}",
                text: "{{ session('alert')['message'] }}",
                icon: "{{ session('alert')['type'] }}",
                confirmButtonText: "OK"
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            const validationErrors = @json($errors->toArray());
            const validationErrorText = Object.entries(validationErrors)
                .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                .join('\n');
            console.error(`[Upload/Validation Error]\n${validationErrorText}`);

            Swal.fire({
                title: "Perhatian",
                html: Object.values(validationErrors).flat().join('<br>'),
                icon: "error",
                confirmButtonText: "OK"
            });
        </script>
    @endif

    @if (session('upload_error'))
        <script>
            const uploadError = @json(session('upload_error'));
            console.error('[Upload/Request Error]', {
                message: uploadError,
                status: 413,
                postMaxSize: @json(ini_get('post_max_size')),
                uploadMaxFilesize: @json(ini_get('upload_max_filesize'))
            });

            Swal.fire({
                title: "Upload gagal",
                text: uploadError,
                icon: "error",
                confirmButtonText: "OK"
            });
        </script>
    @endif

    <script>
        function showRegister() {
            document.getElementById("login-section").style.display = "none";
            document.getElementById("register-section").style.display = "block";
        }

        function showLogin() {
            document.getElementById("register-section").style.display = "none";
            document.getElementById("login-section").style.display = "block";
        }

        // Star rating picker
        document.querySelectorAll('#starInput .star-item').forEach(star => {
            star.addEventListener('click', function() {
                const val = parseInt(this.getAttribute('data-val'));
                document.getElementById('ratingValue').value = val;
                document.querySelectorAll('#starInput .star-item').forEach((s, idx) => {
                    if (idx < val) {
                        s.classList.remove('bi-star');
                        s.classList.add('bi-star-fill');
                    } else {
                        s.classList.remove('bi-star-fill');
                        s.classList.add('bi-star');
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>