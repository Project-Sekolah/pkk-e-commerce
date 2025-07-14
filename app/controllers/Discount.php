<?php

class Discount extends Controller
{
  private $perPage = 100;

  public function __construct()
  {
    parent::__construct();
    $this->db = new Database();
  }

  public function index($page = 1, $categorySlug = null)
  {
    $discountModel = $this->model("Discount_model");

    $data["discounts"] = $discountModel->getAllDiscounts();
    $data["judul"] = "Discounts";

    $this->render(["templates/hero", "discount/index"], $data);
  }

  public function add()
  {
    $this->checkRole(["seller", "admin"]);
    $data["judul"] = "Tambah Discount";
    $this->render(["discount/add"], $data);
  }

  public function store()
  {
    $this->checkRole(["seller", "admin"]);

    if (
      !isset($_POST["name"]) ||
      !isset($_POST["percentage"]) ||
      !isset($_POST["start_date"]) ||
      !isset($_POST["end_date"])
    ) {
      Flasher::setFlash(
        "Error",
        "Input tidak valid, discount gagal ditambahkan.",
        "danger"
      );
      header("Location: " . BASEURL . "/discount/add");
      exit();
    }

    $data = [
      "name" => $_POST["name"],
      "percentage" => $_POST["percentage"],
      "start_date" => $_POST["start_date"],
      "end_date" => $_POST["end_date"],
      "is_active" => 1,
    ];

    $result = $this->model("Discount_model")->addDiscount($data);

    if ($result) {
      Flasher::setFlash("Sukses", "Discount berhasil ditambahkan.", "success");
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal menambahkan discount. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/discount");
    exit();
  }

  // Validasi pemilik diskon sebelum manipulasi
  private function validateDiscountOwnership($discountId)
  {
    $discountModel = $this->model("Discount_model");
    $discount = $discountModel->getDiscountById($discountId);

    if (!$discount) {
      Flasher::setFlash("Error", "Diskon tidak ditemukan.", "danger");
      header("Location: " . BASEURL . "/discount");
      exit();
    }

    $userId = $_SESSION["user"]["id"] ?? null;
    if ($discount["user_id"] !== $userId) {
      Flasher::setFlash("Error", "Anda tidak memiliki izin untuk diskon ini.", "danger");
      header("Location: " . BASEURL . "/discount");
      exit();
    }
  }

  public function edit($id)
  {
    $this->checkRole(["seller", "admin"]);
    $this->validateDiscountOwnership($id);
    $discountModel = $this->model("Discount_model");
    $data["discount"] = $discountModel->getDiscountById($id);
    $data["judul"] = "Edit Discount";
    $this->render(["discount/edit"], $data);
  }

  public function update($id)
  {
    $this->checkRole(["seller", "admin"]);
    $this->validateDiscountOwnership($id);
    if (
      !isset($_POST["name"]) ||
      !isset($_POST["percentage"]) ||
      !isset($_POST["start_date"]) ||
      !isset($_POST["end_date"])
    ) {
      Flasher::setFlash(
        "Error",
        "Input tidak valid, discount gagal diupdate.",
        "danger"
      );
      header("Location: " . BASEURL . "/discount/edit/$id");
      exit();
    }

    $data = [
      "id" => $id,
      "name" => $_POST["name"],
      "percentage" => $_POST["percentage"],
      "start_date" => $_POST["start_date"],
      "end_date" => $_POST["end_date"],
      "is_active" => $_POST["is_active"] ?? 1,
    ];

    $result = $this->model("Discount_model")->updateDiscount($data);

    if ($result > 0) {
      Flasher::setFlash("Sukses", "Discount berhasil diupdate.", "success");
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal mengupdate discount. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/discount");
    exit();
  }

  public function delete($id)
  {
    $this->checkRole(["seller", "admin"]);
    $this->validateDiscountOwnership($id);
    $result = $this->model("Discount_model")->deleteDiscount($id);

    if ($result > 0) {
      Flasher::setFlash("Sukses", "Discount berhasil dihapus.", "success");
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal menghapus discount. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/discount");
    exit();
  }

  public function addDiscountToProduct($productId, $discountId)
{
    $this->checkRole(["seller", "admin"]);
    $result = $this->model("Discount_model")->addProductDiscount($productId, $discountId);

    if ($result) {
        Flasher::setFlash(
            "Sukses",
            "Discount berhasil ditambahkan ke produk.",
            "success"
        );
    } else {
        Flasher::setFlash(
            "Error",
            "Gagal menambahkan discount ke produk. Coba lagi nanti.",
            "danger"
        );
    }

    header("Location: " . BASEURL . "/product/edit/$productId");
    exit();
}

public function deleteDiscountFromProduct($productId, $discountId)
{
    $this->checkRole(["seller", "admin"]);
    $result = $this->model("Discount_model")->deleteProductDiscount($productId, $discountId);

    if ($result) {
        Flasher::setFlash(
            "Sukses",
            "Discount berhasil dihapus dari produk.",
            "success"
        );
    } else {
        Flasher::setFlash(
            "Error",
            "Gagal menghapus discount dari produk. Coba lagi nanti.",
            "danger"
        );
    }

    header("Location: " . BASEURL . "/product/edit/$productId");
    exit();
}



  private function checkRole($allowedRoles)
  {
    $userRole = $_SESSION["user"]["role"] ?? null;
    if (!in_array($userRole, $allowedRoles)) {
      Flasher::setFlash(
        "Error",
        "Anda tidak memiliki izin untuk mengakses halaman ini.",
        "danger"
      );
      header("Location: " . BASEURL . "/product");
      exit();
    }
  }
}
