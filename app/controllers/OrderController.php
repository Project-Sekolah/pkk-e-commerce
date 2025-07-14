<?php
use Midtrans\Config;

// Konfigurasi Midtrans
Config::$serverKey = MIDTRANS_SERVER_KEY;
Config::$isProduction = MIDTRANS_PRODUCTION;
Config::$isSanitized = true;
Config::$is3ds = true;

class OrderController extends Controller
{
    public function checkout()
    {
        $json = json_decode(file_get_contents("php://input"), true);

        $cart = $json["cart"] ?? null;
        $discountName = $json["discount"] ?? null;

        if (!$cart || !is_array($cart)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid cart data"]);
            exit();
        }

        $subtotal = 0;
        $tax = 0;
        $items = [];

        foreach ($cart as $item) {
            if (!isset($item["id"], $item["name"], $item["price"], $item["quantity"])) {
                http_response_code(400);
                echo json_encode(["error" => "Incomplete item data"]);
                exit();
            }

            $price = (float)$item["price"];
            $qty = (int)$item["quantity"];

            if ($price <= 0 || $qty <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid price or quantity"]);
                exit();
            }

            $subtotal += $price * $qty;
            $tax += ($price * $qty) * 0.05;
            $items[] = [
                "id" => $item["id"],
                "price" => $price,
                "quantity" => $qty,
                "name" => $item["name"],
            ];
        }

        $delivery = $subtotal * 0.1;

        $discount = 0;
        if ($discountName) {
            $discountModel = $this->model("Discount_model");
            $cartItems = array_map(function ($item) {
                return ["product_id" => $item["id"]];
            }, $cart);

            $validation = $discountModel->validateDiscount($discountName, $cartItems);
            if ($validation["success"]) {
                $discount = $subtotal * ($validation["discount_percentage"] / 100);
            }
        }

        $total = $subtotal + $delivery + $tax - $discount;

        $transaction_details = [
            "order_id" => uniqid("ORDER-"),
            "gross_amount" => (int)round($total),
        ];

        $customer_details = [
            "first_name" => $json["user"]["name"] ?? "Guest",
            "email" => filter_var($json["user"]["email"] ?? "guest@example.com", FILTER_SANITIZE_EMAIL),
            "phone" => preg_replace('/[^0-9]/', '', $json["user"]["phone"] ?? "081234567890"),
        ];

        $payload = [
            "transaction_details" => $transaction_details,
            "item_details" => $items,
            "customer_details" => $customer_details,
            "enabled_payments" => ["credit_card", "gopay", "bank_transfer"],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            echo json_encode(["snap_token" => $snapToken]);
        } catch (\Exception $e) {
            http_response_code(500);
            error_log("Error generating Snap Token: " . $e->getMessage());
            echo json_encode(["error" => "Failed to initiate payment"]);
        }
    }
}
