<?php
class Order extends Controller {
    public function checkout() {
        $userId = $_SESSION['user']['id'];
        $userModel = $this->model('User_model');
        $orderModel = $this->model('Order_model');
        $productModel = $this->model('Product_model');

        // Ambil alamat default user
        $defaultAddress = $userModel->getDefaultAddress($userId);
        if (!$defaultAddress) {
            Flasher::setFlash('Alamat utama belum diatur!', 'Silakan atur alamat utama di profil.', 'error');
            header('Location: ' . BASEURL . '/cart');
            exit;
        }
        $customerAddress = $defaultAddress['address_line_1'];

        // Buat order dari cart
        $orderId = $orderModel->createOrderFromCart($userId, $customerAddress);
        if (!$orderId) {
            Flasher::setFlash('Gagal membuat order!', 'Coba ulangi atau hubungi admin.', 'error');
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        // Ambil semua item di order ini
        $cartItems = $orderModel->getOrderItems($orderId);

        // Kurangi stok produk
        if (!empty($cartItems)) {
            $productModel->reduceProductStock($cartItems);
        }

        // Redirect ke detail order
        header('Location: ' . BASEURL . '/order/detail/' . $orderId);
        exit;
    }
}
