<?php

class Product extends Controller
{
  private $perPage = 100;

  public function __construct()
  {
    parent::__construct();
    $this->db = new Database();
  }

  public function index($page = 1, $categorySlug = null)
  {
    $productModel = $this->model("Product_model");
    $categoryModel = $this->model("Category_model");

    $data["categories"] = $categoryModel->getAllCategories();

    $categories = $_GET["categories"] ?? ($categorySlug ? [$categorySlug] : []);
    $genders = $_GET["gender"] ?? [];
    $search = $_GET["search"] ?? "";

    if ($categorySlug) {
      $category = $categoryModel->getCategoryBySlug($categorySlug);
      $data["category"] = $category;
    }

    $data["products"] = $productModel->getFilteredProductsPaginated(
      $categories,
      $genders,
      $search,
      $this->perPage,
      ($page - 1) * $this->perPage
    );
    $data["totalFilteredProducts"] = $productModel->getTotalFilteredProducts(
      $categories,
      $genders,
      $search
    );
    $data["totalPages"] = ceil($data["totalFilteredProducts"] / $this->perPage);
    $data["currentPage"] = $page;
    $data["judul"] = "Produk";

    $productIds = array_column($data["products"], "id");
    $data["ratings"] = $productModel->getAllRatingsForProductIds($productIds);

    $this->render(["templates/hero", "product/index"], $data);
  }

  public function addRating()
  {
    if (
      !isset($_POST["user_id"]) ||
      !isset($_POST["product_id"]) ||
      !isset($_POST["rating"]) ||
      empty($_POST["rating"])
    ) {
      Flasher::setFlash(
        "Error",
        "Input tidak valid, rating gagal ditambahkan.",
        "danger"
      );
      header("Location: " . BASEURL . "/product");
      exit();
    }

    $result = $this->model("Product_model")->addDataRating($_POST);

    if ($result > 0) {
      Flasher::setFlash("Sukses", "Rating berhasil ditambahkan.", "success");
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal menambahkan rating. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/product");
    exit();
  }

  // =======================
  // CRUD Product
  // =======================

  public function delete($id)
  {
    $this->checkRole(["seller", "admin"]);
    $result = $this->model("Product_model")->deleteProduct($id);

    if ($result > 0) {
      Flasher::setFlash("Sukses", "Produk berhasil dihapus.", "success");
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal menghapus produk. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/product");
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

  public function seller($page = 1)
  {
    $this->checkRole(["admin", "seller"]);
    $productModel = $this->model("Product_model");

    $userId = $_SESSION["user"]["id"] ?? null;
    if (!$userId) {
      Flasher::setFlash(
        "Error",
        "Anda tidak memiliki izin untuk mengakses halaman ini.",
        "danger"
      );
      header("Location: " . BASEURL . "/product");
      exit();
    }

    $data["products"] = $productModel->getProductsBySeller(
      $userId,
      $this->perPage,
      ($page - 1) * $this->perPage
    );
    $data["totalFilteredProducts"] = $productModel->getTotalProductsBySeller(
      $userId
    );
    $data["totalPages"] = ceil($data["totalFilteredProducts"] / $this->perPage);
    $data["currentPage"] = $page;
    $data["judul"] = "Produk Saya";

    $this->render(["templates/hero", "product/seller"], $data);
  }

  public function add()
  {
    $this->checkRole(["seller", "admin"]);
    $categoryModel = $this->model("Category_model");
    $data["categories"] = $categoryModel->getAllCategories();
    $data["judul"] = "Tambah Produk";
    $this->render(["product/add"], $data);
  }

  public function store()
  {
    $this->checkRole(["seller", "admin"]);

    if (
      !isset($_POST["title"]) ||
      !isset($_POST["description"]) ||
      !isset($_POST["price"]) ||
      !isset($_POST["category_id"]) ||
      !isset($_POST["gender"])
    ) {
      Flasher::setFlash(
        "Error",
        "Input tidak valid, produk gagal ditambahkan.",
        "danger"
      );
      header("Location: " . BASEURL . "/product/add");
      exit();
    }

    $data = [
      "category_id" => $_POST["category_id"],
      "user_id" => $_SESSION["user"]["id"],
      "title" => $_POST["title"],
      "slug" => strtolower(str_replace(" ", "-", $_POST["title"])),
      "price" => $_POST["price"],
      "stock" => $_POST["stock"] ?? 0,
      "description" => $_POST["description"],
      "gender" => $_POST["gender"],
      "is_active" => 1,
    ];

    // Simpan produk dan dapatkan UUID yang valid
    $productId = $this->model("Product_model")->addProduct($data);

    if ($productId) {
      // Unggah gambar
      if (isset($_FILES["images"]) && is_array($_FILES["images"]["tmp_name"])) {
        foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
          if ($_FILES["images"]["error"][$key] === 0) {
            // Perbaiki pengecekan error per file
            try {
              $upload = $this->cloudinary
                ->uploadApi()
                ->upload($tmp_name, ["folder" => "product_images/"]);
              $this->model("Product_model")->addProductImage(
                $productId,
                $upload["secure_url"]
              );
            } catch (Exception $e) {
              error_log("Cloudinary upload error: " . $e->getMessage());
              Flasher::setFlash(
                "Error",
                "Gagal mengunggah beberapa gambar.",
                "danger"
              );
            }
          }
        }
      }

      Flasher::setFlash("Sukses", "Produk berhasil ditambahkan.", "success");
    } else {
      Flasher::setFlash("Error", "Gagal menambahkan produk.", "danger");
    }

    header("Location: " . BASEURL . "/product");
    exit();
  }

  private function ensureCloudinaryFolderExists($folder)
  {
    // Check if the folder exists in Cloudinary
    try {
      $folders = $this->cloudinary->api()->folders($folder);
      if (empty($folders["folders"])) {
        // Create the folder if it doesn't exist
        $this->cloudinary->api()->create_folder($folder);
      }
    } catch (Exception $e) {
      error_log("Cloudinary folder check error: " . $e->getMessage());
    }
  }

  public function edit($id)
  {
    $this->checkRole(["seller", "admin"]);
    $productModel = $this->model("Product_model");
    $discountModel = $this->model("Discount_model");
    $data["product"] = $productModel->getProductById($id);
    $data["categories"] = $this->model("Category_model")->getAllCategories();
    $data["discounts"] = $discountModel->getAllDiscounts();
    $data["productDiscounts"] = $productModel->getProductDiscounts($id);

    // Ensure product_images is available
    if (!isset($data["product"]["images"])) {
      $data["product_images"] = [];
    } else {
      $data["product_images"] = $data["product"]["images"];
    }

    $data["judul"] = "Edit Produk";
    $this->render(["product/edit"], $data);
  }

  public function update($id)
{
    $this->checkRole(["seller", "admin"]);
    if (
        !isset($_POST["title"]) ||
        !isset($_POST["description"]) ||
        !isset($_POST["price"]) ||
        !isset($_POST["category_id"]) ||
        !isset($_POST["gender"])
    ) {
        Flasher::setFlash(
            "Error",
            "Input tidak valid, produk gagal diupdate.",
            "danger"
        );
        header("Location: " . BASEURL . "/product/edit/$id");
        exit();
    }

    $data = [
        "id" => $id,
        "title" => $_POST["title"],
        "slug" => strtolower(str_replace(" ", "-", $_POST["title"])),
        "price" => $_POST["price"],
        "stock" => $_POST["stock"] ?? 0,
        "description" => $_POST["description"],
        "gender" => $_POST["gender"],
    ];

    $result = $this->model("Product_model")->updateProduct($data);

    if ($result > 0) {
        // Hapus semua relasi diskon produk lama
        $discountModel = $this->model("Discount_model");
        $productDiscounts = $this->model("Product_model")->getProductDiscounts($id);
        foreach ($productDiscounts as $discount) {
            $discountModel->deleteProductDiscount($id, $discount["id"]);
        }

        // Tambahkan relasi diskon produk baru
        if (isset($_POST["discounts"]) && is_array($_POST["discounts"])) {
            foreach ($_POST["discounts"] as $discountId) {
                $discountModel->addProductDiscount($id, $discountId);
            }
        }

        Flasher::setFlash("Sukses", "Produk berhasil diupdate.", "success");
    } else {
        Flasher::setFlash(
            "Error",
            "Gagal mengupdate produk. Coba lagi nanti.",
            "danger"
        );
    }

    header("Location: " . BASEURL . "/product");
    exit();
}



  public function deleteImage($imageId)
  {
    $this->checkRole(["seller", "admin"]);
    $result = $this->model("Product_model")->deleteProductImage($imageId);

    if ($result > 0) {
      Flasher::setFlash("Sukses", "Gambar produk berhasil dihapus.", "success");
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal menghapus gambar produk. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/product/edit/" . $_POST["product_id"]);
    exit();
  }

  public function editImage($imageId)
  {
    $this->checkRole(["seller", "admin"]);
    // Fetch the image details
    $image = $this->model("Product_model")->getProductImageById($imageId);

    if ($image) {
      // Display an edit form for the image
      $data["image"] = $image;
      $this->render(["product/edit_image"], $data);
    } else {
      Flasher::setFlash("Error", "Gambar tidak ditemukan.", "danger");
      header("Location: " . BASEURL . "/product");
      exit();
    }
  }

  public function updateImage($imageId)
  {
    $this->checkRole(["seller", "admin"]);
    if (!isset($_POST["image_url"])) {
      Flasher::setFlash(
        "Error",
        "Input tidak valid, gambar gagal diupdate.",
        "danger"
      );
      header("Location: " . BASEURL . "/product/editImage/$imageId");
      exit();
    }

    $data = [
      "id" => $imageId,
      "image_url" => $_POST["image_url"],
    ];

    $result = $this->model("Product_model")->updateProductImage($data);

    if ($result > 0) {
      Flasher::setFlash(
        "Sukses",
        "Gambar produk berhasil diupdate.",
        "success"
      );
    } else {
      Flasher::setFlash(
        "Error",
        "Gagal mengupdate gambar produk. Coba lagi nanti.",
        "danger"
      );
    }

    header("Location: " . BASEURL . "/product");
    exit();
  }

  public function addImage($productId)
  {
    $this->checkRole(["seller", "admin"]);
    if (!isset($_FILES["new_image"]) || $_FILES["new_image"]["error"] !== 0) {
      Flasher::setFlash("Error", "Gambar gagal diunggah.", "danger");
      header("Location: " . BASEURL . "/product/edit/$productId");
      exit();
    }

    try {
      $upload = $this->cloudinary
        ->uploadApi()
        ->upload($_FILES["new_image"]["tmp_name"], [
          "folder" => "product_images/",
        ]);
      $imageUrl = $upload["secure_url"];
      $this->model("Product_model")->addProductImage($productId, $imageUrl);
      Flasher::setFlash(
        "Sukses",
        "Gambar produk berhasil ditambahkan.",
        "success"
      );
    } catch (Exception $e) {
      error_log("Cloudinary upload error: " . $e->getMessage());
      Flasher::setFlash("Error", "Gagal mengunggah gambar.", "danger");
    }

    header("Location: " . BASEURL . "/product/edit/$productId");
    exit();
  }
}


