<?php

class Cart extends Controller
{
  private $cartModel;

  public function __construct()
  {
    parent::__construct(); // Inisialisasi dari Controller
    $this->checkLogin(); // Wajib login
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
    $userId = $_SESSION["user"]["id"];
    $productId = $_POST["product_id"];
    $quantity = (int) $_POST["quantity"];

    $cart = $this->cartModel->getOrCreateCart($userId);
    $result = $this->cartModel->addItem($cart["id"], $productId, $quantity);

    echo json_encode(["success" => $result]);
  }

  public function increaseItem()
  {
    $itemId = $_POST["item_id"];
    $result = $this->cartModel->increaseItemQuantity($itemId);
    echo json_encode(["success" => $result]);
  }

  public function decreaseItem()
  {
    $itemId = $_POST["item_id"];
    $result = $this->cartModel->decreaseItemQuantity($itemId);
    echo json_encode(["success" => $result]);
  }

  public function deleteItem()
  {
    $itemId = $_POST["item_id"];
    $result = $this->cartModel->deleteCartItemById($itemId);
    echo json_encode(["success" => $result]);
  }

  public function clearCart()
  {
    $userId = $_SESSION["user"]["id"];
    $cart = $this->cartModel->getOrCreateCart($userId);
    $result = $this->cartModel->clearCart($cart["id"]);
    echo json_encode(["success" => $result]);
  }
}

$action = $_GET["action"] ?? "";
$cart = new Cart();

switch ($action) {
  case "getCart":
    $cart->getCart();
    break;
  case "addItem":
    $cart->addItem();
    break;
  case "increaseItem":
    $cart->increaseItem();
    break;
  case "decreaseItem":
    $cart->decreaseItem();
    break;
  case "deleteItem":
    $cart->deleteItem();
    break;
  case "clearCart":
    $cart->clearCart();
    break;
  default:
    echo json_encode(["error" => "Unknown action"]);
}
