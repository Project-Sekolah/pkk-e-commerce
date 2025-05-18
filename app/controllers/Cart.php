<?php

class Cart extends Controller
{
  public function index()
  {
    $userId = $_SESSION["user"]["id"];
    $cartModel = $this->model("Cart_model");
    $productModel = $this->model("Product_model");

    $cart = $cartModel->getOrCreateCart($userId);
    $items = $cartModel->getCartItems($cart["id"]);

    foreach ($items as &$item) {
      $product = $productModel->getProductById($item["product_id"]);
      $item["product"] = $product;
    }

    // Kalau requestnya AJAX, kirim json, kalau biasa render view
    if ($this->isAjax()) {
      header("Content-Type: application/json");
      echo json_encode([
        "cart" => $cart,
        "items" => $items
      ]);
      exit();
    }

    $data["cart"] = $cart;
    $data["items"] = $items;
    $data["judul"] = "Keranjang";
    $this->render(["cart/index"], $data);
  }

  public function add()
  {
    if (!isset($_POST["product_id"]) || !isset($_POST["quantity"])) {
      return $this->jsonResponse(false, "Invalid input");
    }

    $userId = $_SESSION["user"]["id"];
    $productId = $_POST["product_id"];
    $quantity = (int) $_POST["quantity"];

    $cartModel = $this->model("Cart_model");
    $cart = $cartModel->getOrCreateCart($userId);
    $success = $cartModel->addItem($cart["id"], $productId, $quantity);

    if ($this->isAjax()) {
      return $this->jsonResponse($success, $success ? "Item added" : "Failed to add item");
    } else {
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  public function updateQuantity()
  {
    if (!isset($_POST["item_id"]) || !isset($_POST["action"])) {
      return $this->jsonResponse(false, "Invalid input");
    }

    $cartModel = $this->model("Cart_model");
    $itemId = $_POST["item_id"];
    $action = $_POST["action"];

    $item = $cartModel->getCartItemsByIds([$itemId]);
    if (!$item) {
      return $this->jsonResponse(false, "Item not found");
    }
    $item = $item[0];

    $newQty = $action === "increase" ? $item["quantity"] + 1 : $item["quantity"] - 1;

    if ($newQty < 1) {
      $success = $cartModel->deleteCartItemById($itemId);
    } else {
      $success = $cartModel->addItem(
        $item["cart_id"],
        $item["product_id"],
        $newQty - $item["quantity"]
      );
    }

    if ($this->isAjax()) {
      return $this->jsonResponse($success, $success ? "Quantity updated" : "Failed to update quantity");
    } else {
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  public function delete($itemId)
  {
    $cartModel = $this->model("Cart_model");
    $success = $cartModel->deleteCartItemById($itemId);

    if ($this->isAjax()) {
      return $this->jsonResponse($success, $success ? "Item deleted" : "Failed to delete item");
    } else {
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  public function clear()
  {
    $userId = $_SESSION["user"]["id"];
    $cartModel = $this->model("Cart_model");
    $cart = $cartModel->getOrCreateCart($userId);
    $success = $cartModel->clearCart($cart["id"]);

    if ($this->isAjax()) {
      return $this->jsonResponse($success, $success ? "Cart cleared" : "Failed to clear cart");
    } else {
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  // Helper function cek apakah request AJAX
  private function isAjax()
  {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  // Helper function kirim response JSON dengan status dan pesan
  private function jsonResponse($success, $message, $data = [])
  {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
      "success" => $success,
      "message" => $message
    ], $data));
    exit();
  }
}