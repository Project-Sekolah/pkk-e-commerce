<?php
use Midtrans\Config;
use Dompdf\Dompdf;
use Dompdf\Options;

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

        // Save order to database
        $orderModel = $this->model("Order_model");
        $orderData = [
            "order_id" => $transaction_details["order_id"],
            "user_id" => $json["user"]["id"] ?? null,
            "items" => json_encode($items),
            "subtotal" => $subtotal,
            "tax" => $tax,
            "delivery" => $delivery,
            "discount" => $discount,
            "total" => $total,
            "status" => "pending",
            "created_at" => date("Y-m-d H:i:s"),
        ];

        $orderId = $orderModel->createOrder($orderData);

        // Generate PDF receipt
        $this->generatePdfReceipt($orderId, $orderData);
    }

    private function generatePdfReceipt($orderId, $orderData)
    {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($options);

        $html = "<h1>Receipt for Order #{$orderId}</h1>";
        $html .= "<p>Thank you for your order. Here are the details:</p>";
        $html .= "<p><strong>Items:</strong></p><ul>";

        $items = json_decode($orderData["items"], true);
        foreach ($items as $item) {
            $html .= "<li>{$item["name"]} - {$item["quantity"]} x " . number_format($item["price"], 2) . "</li>";
        }

        $html .= "</ul>";
        $html .= "<p><strong>Subtotal:</strong> " . number_format($orderData["subtotal"], 2) . "</p>";
        $html .= "<p><strong>Tax:</strong> " . number_format($orderData["tax"], 2) . "</p>";
        $html .= "<p><strong>Delivery:</strong> " . number_format($orderData["delivery"], 2) . "</p>";
        $html .= "<p><strong>Discount:</strong> " . number_format($orderData["discount"], 2) . "</p>";
        $html .= "<p><strong>Total:</strong> " . number_format($orderData["total"], 2) . "</p>";
        $html .= "<p>Status: {$orderData["status"]}</p>";
        $html .= "<p>Thank you for shopping with us!</p>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save the PDF file on the server
        $output = $dompdf->output();
        $pdfFilePath = "receipts/receipt_order_{$orderId}.pdf";
        file_put_contents($pdfFilePath, $output);

        // Optionally, send the PDF as an email attachment to the user
        $this->sendEmailReceipt($orderData["email"], $pdfFilePath);
    }

    private function sendEmailReceipt($to, $pdfFilePath)
    {
        $subject = "Your Order Receipt";
        $message = "Thank you for your order. Please find the attached receipt.";
        $headers = "From: no-reply@example.com";

        // Read the PDF file content
        $fileContent = chunk_split(base64_encode(file_get_contents($pdfFilePath)));

        // Attachment boundary
        $separator = md5(time());
        $eol = "\r\n";

        // Headers for attachment
        $headers .= "MIME-Version: 1.0" . $eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
        $headers .= "This is a multi-part message in MIME format." . $eol;

        // Message body
        $body = "--" . $separator . $eol;
        $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
        $body .= "Content-Transfer-Encoding: 7bit" . $eol;
        $body .= $message . $eol;

        // Attachment
        $body .= "--" . $separator . $eol;
        $body .= "Content-Type: application/pdf; name=\"" . basename($pdfFilePath) . "\"" . $eol;
        $body .= "Content-Transfer-Encoding: base64" . $eol;
        $body .= "Content-Disposition: attachment; filename=\"" . basename($pdfFilePath) . "\"" . $eol;
        $body .= $fileContent . $eol;
        $body .= "--" . $separator . "--";

        // Send the email
        mail($to, $subject, $body, $headers);
    }
}
