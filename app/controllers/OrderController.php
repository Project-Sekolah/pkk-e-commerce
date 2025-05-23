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
        $discount = isset($json["discount"]) ? (float)$json["discount"] : 0;

        // Validasi data keranjang
        if (!$cart || !is_array($cart)) {
            http_response_code(400);
            echo json_encode(["error" => "Keranjang tidak valid"]);
            exit();
        }

        // Validasi diskon
        if ($discount < 0) {
            $discount = 0;
        }

        $subtotal = 0;
        $tax = 0;
        $items = [];

        foreach ($cart as $item) {
            // Validasi item keranjang
            if (!isset($item["id"], $item["name"], $item["price"], $item["quantity"])) {
                http_response_code(400);
                echo json_encode(["error" => "Data item tidak lengkap"]);
                exit();
            }

            $price = (float)$item["price"];
            $qty = (int)$item["quantity"];

            if ($price <= 0 || $qty <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Harga atau jumlah tidak valid"]);
                exit();
            }

            $subtotal += $price * $qty;
            $tax += ($price * $qty) * 0.05; // Pajak 5% per item
            $items[] = [
                "id" => $item["id"],
                "price" => $price,
                "quantity" => $qty,
                "name" => $item["name"],
            ];
        }

        $delivery = $subtotal * 0.1; // Ongkir 10% dari subtotal
        $total = $subtotal + $delivery + $tax - $discount;

        // Batasi diskon agar tidak melebihi total
        if ($discount > $total) {
            $discount = $total;
        }

        $transaction_details = [
            "order_id" => uniqid("ORDER-"),
            "gross_amount" => (int)round($total), // Pastikan integer untuk IDR
            "currency" => "IDR", // Spesifikasikan mata uang
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
            "enabled_payments" => ["credit_card", "gopay", "bank_transfer"], // Spesifikasikan metode pembayaran
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            echo json_encode(["snap_token" => $snapToken]);
        } catch (\Exception $e) {
            http_response_code(500);
            error_log("Error generating Snap Token: " . $e->getMessage());
            echo json_encode(["error" => "Gagal menginisiasi pembayaran"]);
        }
    }
}
