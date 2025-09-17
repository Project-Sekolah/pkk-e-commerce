<?php
class AdminDashboard extends Controller {
    public function index() {
        $this->checkAdmin();

        $userModel = $this->model("User_model");
        $productModel = $this->model("Product_model");
        $orderModel = $this->model("Order_model");
        $categoryModel = $this->model("Category_model");

        $userId = $_SESSION["user"]["id"];

        $allUsers = $userModel->getAllUsers();
        error_log('DEBUG: allUsers=' . print_r($allUsers, true));
        $userTypeCounts = ["buyer" => 0, "seller" => 0, "admin" => 0];
        foreach ($allUsers as $u) {
            error_log('DEBUG: user=' . print_r($u, true));
            if (isset($u["role"])) {
                $role = strtolower($u["role"]);
                error_log('DEBUG: role=' . $role);
                if (array_key_exists($role, $userTypeCounts)) {
                    $userTypeCounts[$role]++;
                }
            } else {
                error_log('DEBUG: user missing role field');
            }
        }
        error_log('DEBUG: userTypeCounts=' . print_r($userTypeCounts, true));
        $data = [
            "judul" => "Dashboard Admin",
            "user" => $userModel->getUserById($userId),
            "addresses" => $userModel->getAddressesByUserId($userId),
            "total_users" => count($allUsers),
            "total_products" => $productModel->getTotalProducts(),
            "total_orders" => $orderModel->getTotalOrders(),
            "total_revenue" => $orderModel->getTotalRevenue(),
            "monthly_sales" => $orderModel->getMonthlySalesChart(),
            "top_products" => $productModel->getTopSellingProducts(5),
            "categories" => $categoryModel->getAllCategories(),
            "user_type_counts" => $userTypeCounts,
        ];
        $this->renderAdmin(['admin/dashboard'], $data);
    }
}
