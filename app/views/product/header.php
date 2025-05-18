<!DOCTYPE html>
<html lang="en">  
<head>  
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">  
  <title><?= $data["judul"] ?></title>  
  <link rel="icon" href="<?= BASEURL ?>/assets/img/logotoko.png" type="image/x-icon">

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    const isLoggedIn = <?= isset($_SESSION["logged_in"]) &&
    $_SESSION["logged_in"] === true
      ? "true"
      : "false" ?>;
  </script>

  <!-- Bootstrap CSS -->
  <link href="<?= BASEURL ?>/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">   

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/styles.css">  

  <!-- Icons & Fonts -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
</head>  
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg topbar fixed-top shadow-sm">
  <div class="container-fluid d-flex justify-content-between px-3">
    <!-- Tombol hamburger (menu) -->
    <button class="btn border-0 me-2" id="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
      <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>

    <!-- Nama brand yang posisinya di sebelah tombol hamburger -->
    <a class="navbar-brand fw-bold" href="#">Lunerburg & Co</a>

    <!-- Cart button -->
    <div class="me-3">
      <button class="btn btn-cart position-relative" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-warning text-white px-35">0</span>
      </button>
    </div>
  </div>
</nav>


<!-- Shopping Cart Offcanvas -->  <div class="offcanvas offcanvas-end" id="offcanvasCart">  
  <div class="offcanvas-hea<?php

class User extends <?php

class User extends Controller
{
  // ============================
  //         AUTH HANDLERS
  // ============================

  public function register()
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: " . BASEURL . "/");
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $username = $_POST["username"];
      $full_name = $_POST["full_name"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirm_password = $_POST["confirm_password"];

      if (
        empty($username) ||
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
      ) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "All fields are required.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      if ($password !== $confirm_password) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Passwords do not match.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      $passwordHash = password_hash($password, PASSWORD_BCRYPT);

      $userModel = $this->model("User_model");
      $userModel->register($username, $full_name, $email, $passwordHash);

      $_SESSION["alert"] = [
        "type" => "success",
        "message" => "Registration successful. Please login.",
      ];
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  public function login()
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: " . BASEURL . "/");
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $email = $_POST["email"];
      $password = $_POST["password"];

      if (empty($email) || empty($password)) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Both fields are required.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      $userModel = $this->model("User_model");
      $user = $userModel->getUserByEmail($email);

      if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        $_SESSION["logged_in"] = true;

        $_SESSION["alert"] = [
          "type" => "success",
          "message" => "Login successful.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      } else {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Invalid credentials.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }
    }
  }

  public function logout()
  {
    session_destroy();
    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "You have been logged out successfully.",
    ];
    header("Location: " . BASEURL . "/");
    exit();
  }

  // ============================
  //        USER PROFILE
  // ============================

  public function profile()
  {
    $this->checkLogin();

    $data["judul"] = "Profile";
    $userModel = $this->model("User_model");
    $data["user"] = $userModel->getUserById($_SESSION["user"]["id"]);
    $data["addresses"] = $userModel->getAddressesByUserId(
      $_SESSION["user"]["id"]
    );

    $this->render(["user/profile"], $data);
  }

  public function updateProfile()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $username = $_POST["username"];
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];

    $userModel = $this->model("User_model");
    $user = $userModel->getUserById($userId);
    $imagePath = $user["image"];

    if (!empty($_FILES["image"]["name"])) {
      try {
        $uploadedImage = $this->cloudinary
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

        $imagePath = $uploadedImage["secure_url"];
      } catch (Exception $e) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" =>
            "Gagal mengunggah foto ke Cloudinary: " . $e->getMessage(),
        ];
        header("Location: " . BASEURL . "/user/profile");
        exit();
      }
    }

    $userModel->updateProfile(
      $userId,
      $username,
      $full_name,
      $email,
      $imagePath
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Profile updated successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  // ============================
  //     ADDRESS MANAGEMENT
  // ============================

  public function addAddress()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $label = $_POST["label"];
    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $city = $_POST["city"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $phone_number = $_POST["phone_number"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $userModel = $this->model("User_model");
    $userModel->addUserAddress(
      $userId,
      $label,
      $address_line_1,
      $address_line_2,
      $city,
      $postal_code,
      $country,
      $phone_number,
      $is_default
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address added successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  public function updateAddress()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $addressId = $_POST["address_id"];
    $label = $_POST["label"];
    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $city = $_POST["city"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $phone_number = $_POST["phone_number"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $userModel = $this->model("User_model");
    $userModel->updateUserAddress(
      $addressId,
      $userId,
      $label,
      $address_line_1,
      $address_line_2,
      $city,
      $postal_code,
      $country,
      $phone_number,
      $is_default
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address updated successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  public function deleteAddress($id)
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $userModel = $this->model("User_model");
    $userModel->deleteUserAddress($id, $userId);

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address deleted successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }
}

{
  // ============================
  //         AUTH HANDLERS
  // ============================

  public function register()
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: " . BASEURL . "/");
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $username = $_POST["username"];
      $full_name = $_POST["full_name"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirm_password = $_POST["confirm_password"];

      if (
        empty($username) ||
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
      ) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "All fields are required.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      if ($password !== $confirm_password) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Passwords do not match.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      $passwordHash = password_hash($password, PASSWORD_BCRYPT);

      $userModel = $this->model("User_model");
      $userModel->register($username, $full_name, $email, $passwordHash);

      $_SESSION["alert"] = [
        "type" => "success",
        "message" => "Registration successful. Please login.",
      ];
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  public function login()
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: " . BASEURL . "/");
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $email = $_POST["email"];
      $password = $_POST["password"];

      if (empty($email) || empty($password)) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Both fields are required.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      $userModel = $this->model("User_model");
      $user = $userModel->getUserByEmail($email);

      if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        $_SESSION["logged_in"] = true;

        $_SESSION["alert"] = [
          "type" => "success",
          "message" => "Login successful.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      } else {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Invalid credentials.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }
    }
  }

  public function logout()
  {
    session_destroy();
    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "You have been logged out successfully.",
    ];
    header("Location: " . BASEURL . "/");
    exit();
  }

  // ============================
  //        USER PROFILE
  // ============================

  public function profile()
  {
    $this->checkLogin();

    $data["judul"] = "Profile";
    $userModel = $this->model("User_model");
    $data["user"] = $userModel->getUserById($_SESSION["user"]["id"]);
    $data["addresses"] = $userModel->getAddressesByUserId(
      $_SESSION["user"]["id"]
    );

    $this->render(["user/profile"], $data);
  }

  public function updateProfile()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $username = $_POST["username"];
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];

    $userModel = $this->model("User_model");
    $user = $userModel->getUserById($userId);
    $imagePath = $user["image"];

    if (!empty($_FILES["image"]["name"])) {
      try {
        $uploadedImage = $this->cloudinary
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

        $imagePath = $uploadedImage["secure_url"];
      } catch (Exception $e) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" =>
            "Gagal mengunggah foto ke Cloudinary: " . $e->getMessage(),
        ];
        header("Location: " . BASEURL . "/user/profile");
        exit();
      }
    }

    $userModel->updateProfile(
      $userId,
      $username,
      $full_name,
      $email,
      $imagePath
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Profile updated successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  // ============================
  //     ADDRESS MANAGEMENT
  // ============================

  public function addAddress()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $label = $_POST["label"];
    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $city = $_POST["city"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $phone_number = $_POST["phone_number"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $userModel = $this->model("User_model");
    $userModel->addUserAddress(
      $userId,
      $label,
      $address_line_1,
      $address_line_2,
      $city,
      $postal_code,
      $country,
      $phone_number,
      $is_default
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address added successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  public function updateAddress()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $addressId = $_POST["address_id"];
    $label = $_POST["label"];
    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $city = $_POST["city"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $phone_number = $_POST["phone_number"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $userModel = $this->model("User_model");
    $userModel->updateUserAddress(
      $addressId,
      $userId,
      $label,
      $address_line_1,
      $address_line_2,
      $city,
      $postal_code,
      $country,
      $phone_number,
      $is_default
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address updated successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  public function deleteAddress($id)
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $userModel = $this->model("User_model");
    $userModel->deleteUserAddress($id, $userId);

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address deleted successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }
}
der">  
    <h5>Shopping Cart</h5>  
    <button type="button" class="<?php

class User extends Controller
{
  // ============================
  //         AUTH HANDLERS
  // ============================

  public function register()
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: " . BASEURL . "/");
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $username = $_POST["username"];
      $full_name = $_POST["full_name"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirm_password = $_POST["confirm_password"];

      if (
        empty($username) ||
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
      ) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "All fields are required.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      if ($password !== $confirm_password) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Passwords do not match.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      $passwordHash = password_hash($password, PASSWORD_BCRYPT);

      $userModel = $this->model("User_model");
      $userModel->register($username, $full_name, $email, $passwordHash);

      $_SESSION["alert"] = [
        "type" => "success",
        "message" => "Registration successful. Please login.",
      ];
      header("Location: " . BASEURL . "/");
      exit();
    }
  }

  public function login()
  {
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
      header("Location: " . BASEURL . "/");
      exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $email = $_POST["email"];
      $password = $_POST["password"];

      if (empty($email) || empty($password)) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Both fields are required.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }

      $userModel = $this->model("User_model");
      $user = $userModel->getUserByEmail($email);

      if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        $_SESSION["logged_in"] = true;

        $_SESSION["alert"] = [
          "type" => "success",
          "message" => "Login successful.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      } else {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" => "Invalid credentials.",
        ];
        header("Location: " . BASEURL . "/");
        exit();
      }
    }
  }

  public function logout()
  {
    session_destroy();
    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "You have been logged out successfully.",
    ];
    header("Location: " . BASEURL . "/");
    exit();
  }

  // ============================
  //        USER PROFILE
  // ============================

  public function profile()
  {
    $this->checkLogin();

    $data["judul"] = "Profile";
    $userModel = $this->model("User_model");
    $data["user"] = $userModel->getUserById($_SESSION["user"]["id"]);
    $data["addresses"] = $userModel->getAddressesByUserId(
      $_SESSION["user"]["id"]
    );

    $this->render(["user/profile"], $data);
  }

  public function updateProfile()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $username = $_POST["username"];
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];

    $userModel = $this->model("User_model");
    $user = $userModel->getUserById($userId);
    $imagePath = $user["image"];

    if (!empty($_FILES["image"]["name"])) {
      try {
        $uploadedImage = $this->cloudinary
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

        $imagePath = $uploadedImage["secure_url"];
      } catch (Exception $e) {
        $_SESSION["alert"] = [
          "type" => "error",
          "message" =>
            "Gagal mengunggah foto ke Cloudinary: " . $e->getMessage(),
        ];
        header("Location: " . BASEURL . "/user/profile");
        exit();
      }
    }

    $userModel->updateProfile(
      $userId,
      $username,
      $full_name,
      $email,
      $imagePath
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Profile updated successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  // ============================
  //     ADDRESS MANAGEMENT
  // ============================

  public function addAddress()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $label = $_POST["label"];
    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $city = $_POST["city"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $phone_number = $_POST["phone_number"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $userModel = $this->model("User_model");
    $userModel->addUserAddress(
      $userId,
      $label,
      $address_line_1,
      $address_line_2,
      $city,
      $postal_code,
      $country,
      $phone_number,
      $is_default
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address added successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  public function updateAddress()
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $addressId = $_POST["address_id"];
    $label = $_POST["label"];
    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $city = $_POST["city"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $phone_number = $_POST["phone_number"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $userModel = $this->model("User_model");
    $userModel->updateUserAddress(
      $addressId,
      $userId,
      $label,
      $address_line_1,
      $address_line_2,
      $city,
      $postal_code,
      $country,
      $phone_number,
      $is_default
    );

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address updated successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }

  public function deleteAddress($id)
  {
    $this->checkLogin();

    $userId = $_SESSION["user"]["id"];
    $userModel = $this->model("User_model");
    $userModel->deleteUserAddress($id, $userId);

    $_SESSION["alert"] = [
      "type" => "success",
      "message" => "Address deleted successfully.",
    ];
    header("Location: " . BASEURL . "/user/profile");
    exit();
  }
}
-close" data-bs-dismiss="offcanvas"></button>  
  </div>  
  <div class="offcanvas-body">  
    <ul class="list-group" id="cart-items"></ul>  
    <div class="mt-4 d-flex justify-content-between">  
      <span><strong>Total:</strong> $<span id="total-price">0.00</span></span>  
      <button class="btn btn-primary">Checkout</button>  
    </div>  
  </div>  
</div>  

<div class="row">
  <div class="col-lg-6ui">
    <?php Flasher::flash(); ?>
  </div>
</div>