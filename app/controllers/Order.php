<?php
class Order extends Controller {
public function history() {
    $userId = $_SESSION['user']['id'];
    $orderModel = $this->model('Order_model');
    $orders = $orderModel->getOrderHistory($userId);
    $this->render(
        ['order/history'],
        [
            'orders' => $orders,
            'judul' => 'History'
        ]
    );
}

public function detail($orderId = null) {
    if (!$orderId) {
        Flasher::setFlash('Order tidak ditemukan!', 'ID order tidak valid.', 'error');
        header('Location: ' . BASEURL . '/order/history');
        exit;
    }
    $userId = $_SESSION['user']['id'];
    $orderModel = $this->model('Order_model');
    $order = $orderModel->getOrderById($userId, $orderId);
    $order_items = $orderModel->getOrderItems($orderId);
    $this->render(
        ['order/detail'],
        [
            'order' => $order,
            'order_items' => $order_items,
            'judul' => 'Order Detail'
        ]
    );
}

public function success() {
    $this->render(
        ['order/success'],
        [
            'judul' => 'Order'
        ]
    );
}


     public function index() {
        // Redirect ke halaman riwayat order atau tampilkan pesan
        header('Location: ' . BASEURL . '/order/history/');
        exit;
    }
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
