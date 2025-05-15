<?php

class Product extends Controller {
    private $perPage = 8;

    public function index($page = 1, $categorySlug = null) {
        $productModel = $this->model('Product_model');
        $categoryModel = $this->model('Category_model');

        // Get all categories for the filter options
        $data['categories'] = $categoryModel->getAllCategories();

        // Get filters from URL query parameters
        $categories = $_GET['categories'] ?? ($categorySlug ? [$categorySlug] : []);
        $genders = $_GET['gender'] ?? [];
        $search = $_GET['search'] ?? '';

        // If categorySlug is passed, apply category filtering, otherwise all categories
        if ($categorySlug) {
            $category = $categoryModel->getCategoryBySlug($categorySlug);
            $data['category'] = $category;
        }

        // Apply pagination and filtering
        $data['products'] = $productModel->getFilteredProductsPaginated($categories, $genders, $search, $this->perPage, ($page - 1) * $this->perPage);
        $data['totalFilteredProducts'] = $productModel->getTotalFilteredProducts($categories, $genders, $search);

        // Calculate total pages for pagination
        $data['totalPages'] = ceil($data['totalFilteredProducts'] / $this->perPage);
        $data['currentPage'] = $page;
        $data['judul'] = "Produk";
    
        $this->render(['templates/hero', 'product/index'], $data);

    }
}
