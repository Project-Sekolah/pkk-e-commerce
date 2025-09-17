<?php
class AdminDashboard extends Controller {
    public function index() {
        $this->checkAdmin();

        $userModel = $this->model("User_model");
        $userId = $_SESSION["user"]["id"];
        $data = [
            "judul" => "Profile",
            "user" => $userModel->getUserById($userId),
            "addresses" => $userModel->getAddressesByUserId($userId),
        ];
        $this->renderAdmin(['admin/dashboard'], $data);
    }
}
