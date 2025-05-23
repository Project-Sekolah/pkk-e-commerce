<?php

class Cart extends Controller
{
  private $cartModel;

  public function __construct()
  {
    parent::__construct();
    $this->checkLogin();
    $this->cartModel = $this->model("Cart_model");
  }

  public function getCart()
  {
    $userId = $_SESSION["user"]["id"];
    $cart = $this->cartModel->getOrCreateCart($userId);
    $items = $this->cartModel->getCartItems($cart["id"]);

    echo json_encode(["items" => $items]);
  }

  public function addItem()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["error" => "Method Not Allowed"]);
      exit();
    }

    $userId = $_SESSION["user"]["id"];
    $productId = $_POST["product_id"];
    $quantity = (int) $_POST["quantity"];

    if (empty($productId) || $quantity <= 0) {
      http_response_code(400);
      echo json_encode(["error" => "Invalid product_id or quantity"]);
      exit();
    }

    $cart = $this->cartModel->getOrCreateCart($userId);
    $result = $this->cartModel->addItem($cart["id"], $productId, $quantity);

    echo json_encode(["success" => $result]);
  }

  public function increaseItem()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["error" => "Method Not Allowed"]);
      exit();
    }

    $itemId = $_POST["item_id"];
    $result = $this->cartModel->increaseItemQuantity($itemId);
    echo json_encode(["success" => $result]);
  }

  public function decreaseItem()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["error" => "Method Not Allowed"]);
      exit();
    }

    $itemId = $_POST["item_id"];
    $result = $this->cartModel->decreaseItemQuantity($itemId);
    echo json_encode(["success" => $result]);
  }

  public function deleteItem()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["error" => "Method Not Allowed"]);
      exit();
    }

    $itemId = $_POST["item_id"];
    $result = $this->cartModel->deleteCartItemById($itemId);
    echo json_encode(["success" => $result]);
  }

  public function clearCart()
  {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["error" => "Method Not Allowed"]);
      exit();
    }

    $userId = $_SESSION["user"]["id"];
    $cart = $this->cartModel->getOrCreateCart($userId);
    $result = $this->cartModel->clearCart($cart["id"]);
    echo json_encode(["success" => $result]);
  }

  public function validateDiscount() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
        exit();
    }

    $discountName = $_POST["discount_name"] ?? null;
    
    // Validasi input harus berupa teks dan tidak hanya angka
    if (empty($discountName)) {
        http_response_code(400);
        echo json_encode(["error" => "Discount name is required"]);
        exit();
    }
    if (preg_match('/^[0-9]+$/', $discountName)) {
        http_response_code(400);
        echo json_encode(["error" => "Discount name cannot be a number"]);
        exit();
    }

    $userId = $_SESSION["user"]["id"];
    $cart = $this->cartModel->getOrCreateCart($userId);
    $cartItems = $this->cartModel->getCartItems($cart["id"]);

    $result = $this->cartModel->validateDiscount($discountName, $cartItems);

    echo json_encode($result);
}

}
