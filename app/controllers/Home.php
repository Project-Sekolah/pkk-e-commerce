<?php

class Home extends Controller {
    public function index() {
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
