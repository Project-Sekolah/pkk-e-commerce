<?php

class User extends Controller
{
  // ============================
  //         AUTH HANDLERS
  // ============================

  public function register()
  {
    if (!empty($_SESSION["logged_in"])) {
      return $this->redirectHome();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $username = trim($_POST["username"] ?? "");
      $full_name = trim($_POST["full_name"] ?? "");
      $email = trim($_POST["email"] ?? "");
      $password = $_POST["password"] ?? "";
      $confirm_password = $_POST["confirm_password"] ?? "";

      if (
        !$username ||
        !$full_name ||
        !$email ||
        !$password ||
        !$confirm_password
      ) {
        return $this->redirectWithFlash("All fields are required.", "error");
      }

      if ($password !== $confirm_password) {
        return $this->redirectWithFlash("Passwords do not match.", "error");
      }

      $passwordHash = password_hash($password, PASSWORD_BCRYPT);
      $this->model("User_model")->register(
        $username,
        $full_name,
        $email,
        $passwordHash
      );

      return $this->redirectWithFlash(
        "Registration successful. Please login.",
        "success"
      );
    }
  }

  public function login()
  {
    if (!empty($_SESSION["logged_in"])) {
      return $this->redirectHome();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $email = trim($_POST["email"] ?? "");
      $password = $_POST["password"] ?? "";

      if (!$email || !$password) {
        return $this->redirectWithFlash("Both fields are required.", "error");
      }

      $user = $this->model("User_model")->getUserByEmail($email);

      if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        $_SESSION["logged_in"] = true;
        return $this->redirectWithFlash("Login successful.", "success");
      }

      return $this->redirectWithFlash("Invalid credentials.", "error");
    }
  }

  public function logout()
  {
    // Hapus hanya data user dari session
    unset($_SESSION["user"], $_SESSION["logged_in"]);

    // Set flash sebelum redirect
    Flasher::setFlash(
      "Success",
      "You have been logged out successfully.",
      "success"
    );

    header("Location: " . BASEURL . "/");
    exit();
  }

  // ============================
  //        USER PROFILE
  // ============================

  public function profile()
  {
    $this->checkLogin();

    $userModel = $this->model("User_model");
    $userId = $_SESSION["user"]["id"];

    $data = [
      "judul" => "Profile",
      "user" => $userModel->getUserById($userId),
      "addresses" => $userModel->getAddressesByUserId($userId),
    ];

    $this->render(["user/profile"], $data);
  }

  public function updateProfile()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $username = trim($_POST["username"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $userModel = $this->model("User_model");
    $user = $userModel->getUserById($userId);
    $imagePath = $user["image"];

    if (!empty($_FILES["image"]["name"])) {
      try {
        $upload = $this->cloudinary
          ->uploadApi()
          ->upload($_FILES["image"]["tmp_name"], [
            "folder" => "profile_pics/",
            "public_id" =>
              uniqid() .
              "_" .
              pathinfo($_FILES["image"]["name"], PATHINFO_FILENAME),
            "overwrite" => true,
            "resource_type" => "image",
          ]);
        $imagePath = $upload["secure_url"];
      } catch (Exception $e) {
        return $this->redirectWithFlash(
          "Gagal mengunggah foto: " . $e->getMessage(),
          "error",
          "/user/profile"
        );
      }
    }

    $userModel->updateProfile(
      $userId,
      $username,
      $full_name,
      $email,
      $imagePath
    );
    return $this->redirectWithFlash(
      "Profile updated successfully.",
      "success",
      "/user/profile"
    );
  }

  // ============================
  //     ADDRESS MANAGEMENT
  // ============================

  public function addAddress()
  {
    $this->checkLogin();
    $userId = $_SESSION["user"]["id"];

    $data = $this->getAddressInput();
    $data["user_id"] = $userId;

    $this->model("User_model")->addUserAddress(...array_values($data));
    return $this->redirectWithFlash(
      "Address added successfully.",
      "success",
      "/user/profile"
    );
  }

  public function updateAddress()
  {
    $this->checkLogin();
    $userId = $_SESSION["user"]["id"];
    $addressId = $_POST["address_id"] ?? null;

    if (!$addressId) {
      return $this->redirectWithFlash(
        "Address ID is required.",
        "error",
        "/user/profile"
      );
    }

    $data = $this->getAddressInput();
    $this->model("User_model")->updateUserAddress(
      $addressId,
      $userId,
      ...array_values($data)
    );

    return $this->redirectWithFlash(
      "Address updated successfully.",
      "success",
      "/user/profile"
    );
  }

  public function deleteAddress($id)
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $this->model("User_model")->deleteUserAddress($id, $userId);

    return $this->redirectWithFlash(
      "Address deleted successfully.",
      "success",
      "/user/profile"
    );
  }

  // ============================
  //         UTILITIES
  // ============================

  private function redirectHome()
  {
    header("Location: " . BASEURL . "/");
    exit();
  }

  private function redirectWithFlash(
    $message,
    $type = "success",
    $redirect = "/"
  ) {
    Flasher::setFlash(ucfirst($type), $message, $type);
    header("Location: " . BASEURL . $redirect);
    exit();
  }

  private function getAddressInput()
  {
    return [
      "label" => $_POST["label"] ?? "",
      "address_line_1" => $_POST["address_line_1"] ?? "",
      "address_line_2" => $_POST["address_line_2"] ?? "",
      "city" => $_POST["city"] ?? "",
      "postal_code" => $_POST["postal_code"] ?? "",
      "country" => $_POST["country"] ?? "",
      "phone_number" => $_POST["phone_number"] ?? "",
      "is_default" => isset($_POST["is_default"]) ? 1 : 0,
    ];
  }
}
