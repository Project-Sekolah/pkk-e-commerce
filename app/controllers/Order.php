<?php

class Order extends Controller
{
  private $orderModel;

  public function __construct()
  {
    parent::__construct();
    $this->checkLogin(); // Menggunakan checkLogin() dari kelas Controller
    $this->orderModel = $this->model("Order_model");
  }

  public function index()
  {
    // Redirect to order history page as the default view
    header("Location: " . BASEURL . "/order/history");
    exit;
  }

  /**
   * Menampilkan riwayat pesanan
   */
  public function history()
  {
    $userId = $_SESSION["user"]["id"];
    $page = $_GET["page"] ?? 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $data = [
      "judul" => "Order History",
      "orders" => $this->orderModel->getOrderHistory($userId, $limit, $offset),
      "currentPage" => $page,
      "totalPages" => ceil(
        count($this->orderModel->getOrderHistory($userId, 1000)) / $limit
      ),
    ];

    $this->render(["order/history"], $data);
  }

  /**
   * Detail pesanan
   */
  public function detail($orderId)
{
    $userId = $_SESSION["user"]["id"];
    $order = $this->orderModel->getOrderById($userId, $orderId);

    if (!$order) {
        return $this->redirectWithFlash(
            "Order tidak ditemukan",
            "error",
            "/order/history"
        );
    }

    // Ambil detail item pesanan
    $order['items'] = $this->orderModel->getOrderItems($orderId);

    $data = [
        "judul" => "Order Detail",
        "order" => $order,
    ];

    $this->render(["order/detail"], $data);
}


  /**
   * Membuat pesanan baru
   */
  public function create()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      return $this->redirectWithFlash("Method Not Allowed", "error", "/cart");
    }

    $userId = $_SESSION["user"]["id"];
    $orderId = $this->orderModel->createOrderFromCart($userId);

    if ($orderId) {
      return $this->redirectWithFlash(
        "Order created successfully",
        "success",
        "/order/history"
      );
    } else {
      return $this->redirectWithFlash(
        "Failed to create order",
        "error",
        "/cart"
      );
    }
  }

  /**
   * Update status pesanan
   */
  public function updateStatus()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      return $this->redirectWithFlash(
        "Method Not Allowed",
        "error",
        "/order/history"
      );
    }

    $orderId = $_POST["order_id"] ?? null;
    $status = $_POST["status"] ?? null;

    if (!$orderId || !$status) {
      return $this->redirectWithFlash(
        "Invalid parameters",
        "error",
        "/order/history"
      );
    }

    $result = $this->orderModel->updateOrderStatus($orderId, $status);

    if ($result) {
      return $this->redirectWithFlash(
        "Order status updated successfully",
        "success",
        "/order/history"
      );
    } else {
      return $this->redirectWithFlash(
        "Failed to update order status",
        "error",
        "/order/history"
      );
    }
  }

  /**
   * Redirect dengan pesan
   */
  private function redirectWithFlash($message, $type, $redirect = "/")
  {
    Flasher::setFlash(ucfirst($type), $message, $type);
    header("Location: " . BASEURL . $redirect);
    exit();
  }
}
