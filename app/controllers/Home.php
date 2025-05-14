<?php

class Home extends Controller {
    public function index() {
        // Cek apakah CSRF token sudah ada di session, jika belum maka buat baru
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $productModel = $this->model('Product_model');
        $data['judul'] = 'Lunerburg & Co';

        // Ambil 8 produk untuk ditampilkan di home
        $data['products'] = $productModel->getLimitedProducts(8);

        $this->view('templates/modal', $data);
        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer');
    }
}
?>
