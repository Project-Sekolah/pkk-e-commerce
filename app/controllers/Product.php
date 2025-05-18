<?php

class Product extends Controller
{
  private $perPage = 100;

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
      Flasher::setFlash("Error", "Input tidak valid, rating gagal ditambahkan.", "danger");
      header("Location: " . BASEURL . "/product");
      exit();
    }

    $result = $this->model("Product_model")->addDataRating($_POST);

    if ($result > 0) {
      Flasher::setFlash("Sukses", "Rating berhasil ditambahkan.", "success");
    } else {
      Flasher::setFlash("Error", "Gagal menambahkan rating. Coba lagi nanti.", "danger");
    }

    header("Location: " . BASEURL . "/product");
    exit();
  }
}