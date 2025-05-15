<?php

class Home extends Controller {
    public function index() {
        // Cek apakah CSRF token sudah ada di session, jika belum maka buat baru
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $productModel = $this->model('Product_model');
        $data['judul'] = 'Lunerburg & Co';
        $data['products'] = $productModel->getLimitedProducts(8);

        $this->render(['templates/hero', 'home/index'], $data);
    }
}
?>
