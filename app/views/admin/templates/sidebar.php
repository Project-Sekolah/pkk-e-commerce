<div class="sidebar d-flex flex-column">
    <div class="d-flex justify-content-center align-items-center mb-4">
        <span class="navbar-brand fs-4 fw-bold text-center">Lunerburg & Co</span>
    </div>
    <div class="accordion accordion-flush flex-grow-1" id="sidebarMenu">
        <div class="accordion-item border-0">
            <h2 class="accordion-header">
                <a class="nav-link active py-2" href="#"><i class="bi bi-house-door me-2"></i>Beranda</a>
            </h2>
        </div>
        <div class="accordion-item border-0">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProduct">
                    <i class="bi bi-box-seam me-2"></i>Manajemen Produk
                </button>
            </h2>
            <div id="collapseProduct" class="accordion-collapse collapse" data-bs-parent="#sidebarMenu">
                <div class="accordion-body">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Produk</a></li>
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Kategori</a></li>
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Diskon</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item border-0">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUser">
                    <i class="bi bi-person-circle me-2"></i>Manajemen User
                </button>
            </h2>
            <div id="collapseUser" class="accordion-collapse collapse" data-bs-parent="#sidebarMenu">
                <div class="accordion-body">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Buyer</a></li>
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Seller</a></li>
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Admin</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item border-0">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReport">
                    <i class="bi bi-receipt me-2"></i>Laporan
                </button>
            </h2>
            <div id="collapseReport" class="accordion-collapse collapse" data-bs-parent="#sidebarMenu">
                <div class="accordion-body">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Faq</a></li>
                        <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Penjualan</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-auto pt-3 border-top">
        <a class="nav-link text-danger" href="<?= BASEURL ?>"><i class="bi bi-box-arrow-left me-2"></i>Keluar</a>
    </div>
</div>

<div class="offcanvas offcanvas-start bg-white" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Lunerburg & Co</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="accordion accordion-flush" id="offcanvasSidebarMenu">
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <a class="nav-link active py-2" href="#"><i class="bi bi-house-door me-2"></i>Beranda</a>
                </h2>
            </div>
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#offcanvasCollapseProduct">
                        <i class="bi bi-box-seam me-2"></i>Manajemen Produk
                    </button>
                </h2>
                <div id="offcanvasCollapseProduct" class="accordion-collapse collapse" data-bs-parent="#offcanvasSidebarMenu">
                    <div class="accordion-body">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Produk</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Kategori</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Diskon</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#offcanvasCollapseUser">
                        <i class="bi bi-person-circle me-2"></i>Manajemen User
                    </button>
                </h2>
                <div id="offcanvasCollapseUser" class="accordion-collapse collapse" data-bs-parent="#offcanvasSidebarMenu">
                    <div class="accordion-body">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Buyer</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Seller</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Admin</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#offcanvasCollapseReport">
                        <i class="bi bi-receipt me-2"></i>Laporan
                    </button>
                </h2>
                <div id="offcanvasCollapseReport" class="accordion-collapse collapse" data-bs-parent="#offcanvasSidebarMenu">
                    <div class="accordion-body">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Faq</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="#">Kelola Penjualan</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-auto pt-3 border-top">
            <a class="nav-link text-danger" href="<?= BASEURL ?>"><i class="bi bi-box-arrow-left me-2"></i>Keluar</a>
        </div>
    </div>
</div>