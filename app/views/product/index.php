<!-- Product Section -->
<div class="container my-5">
  <h4 id="produk" class="mb-4">Popular Products</h4>

  <!-- Filter Section -->
  <div class="row g-3 mb-4">
    <!-- Search Input -->
    <div class="col-12 col-md-6">
      <input type="text" id="searchInput" class="form-control" placeholder="Search Products..." onkeyup="searchProducts()">
    </div>

    <!-- Filter Buttons -->
    <div class="col-12 col-md-6">
      <div class="d-flex flex-wrap gap-2 justify-content-md-end">
        <!-- All Filter -->
        <button class="btn btn-outline-dark" id="allFilterBtn" onclick="clearFilters()">All</button>

        <!-- Category Filters -->
        <?php foreach ($data["categories"] as $category): ?>
          <button class="btn btn-outline-dark category-filter"
                  data-category="<?= strtolower($category["slug"]) ?>"
                  onclick="toggleFilter('category', '<?= strtolower(
                    $category["slug"]
                  ) ?>')">
            <?= htmlspecialchars($category["name"]) ?>
          </button>
        <?php endforeach; ?>

        <!-- Gender Filters -->
        <button class="btn btn-outline-dark gender-filter" data-gender="pria" onclick="toggleFilter('gender', 'pria')">Pria</button>
        <button class="btn btn-outline-dark gender-filter" data-gender="wanita" onclick="toggleFilter('gender', 'wanita')">Wanita</button>
        <button class="btn btn-outline-dark gender-filter" data-gender="all" onclick="toggleFilter('gender', 'all')">Pria & Wanita</button>
      </div>
    </div>
  </div>

<!-- Product Cards -->
<?php $user = $data["user"] ?? null; ?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="productGrid">
  <?php foreach ($data["products"] as $product):

    $ratingsForProduct = $data["ratings"][$product["id"]] ?? [];
    $count = count($ratingsForProduct);
    $avg =
      $count > 0
        ? array_sum(array_column($ratingsForProduct, "rating")) / $count
        : 0;

    $image = explode(",", $product["images"])[0];
    $imageUrl = strpos($image, 'res.cloudinary.com') !== false
      ? htmlspecialchars($image)
      : BASEURL . '/assets/img/' . htmlspecialchars($image);

    // Siapkan data reviewer untuk atribut data-reviewers
    $reviewers = [];
    foreach ($ratingsForProduct as $rating) {
      $reviewers[] = [
        "username" => $rating["user_name"] ?? "Anonymous",
        "userImage" =>
          $rating["user_image"] ?? BASEURL . "/assets/img/default-avatar.jpg",
        "rating" => (float) $rating["rating"],
        "review" => $rating["review_text"] ?? "",
      ];
    }
    ?>
    <div class="col product-item"
         data-category="<?= strtolower(htmlspecialchars($product["category_slug"])) ?>"
         data-gender="<?= strtolower(htmlspecialchars($product["gender"])) ?>">
      <div class="card border card-3d interactive h-100">
        <img src="<?= $imageUrl ?>"
             class="card-img-top product-img"
             alt="product"
             data-userId ="<?= htmlspecialchars($user["id"]) ?>"
             data-username="<?= htmlspecialchars($user["username"]) ?>"
             data-userimage="<?= htmlspecialchars($user["image"]) ?>"
             data-productId ="<?= htmlspecialchars($product["id"]) ?>"
             data-bs-toggle="modal"
             data-bs-target="#productModal"
             data-title="<?= htmlspecialchars($product["title"], ENT_QUOTES) ?>"
             data-price="<?= number_format($product["price"], 2) ?>"
             data-category="<?= htmlspecialchars($product["category_name"], ENT_QUOTES) ?>"
             data-description="<?= htmlspecialchars($product["description"], ENT_QUOTES) ?>"
             data-gender="<?= htmlspecialchars($product["gender"], ENT_QUOTES) ?>"
             data-image="<?= $imageUrl ?>"
             data-stock="<?= (int) $product["stock"] ?>"
             data-rating="<?= $count > 0 ? number_format($avg, 1) : "0" ?>"
             data-rating-count="<?= $count ?>"
             data-reviewers='<?= htmlspecialchars(json_encode($reviewers), ENT_QUOTES) ?>'
        >

        <div class="card-body text-center" style="background-color: #847e7b;">
          <h6 class="card-title"><?= htmlspecialchars($product["title"]) ?></h6>
          <p class="card-text">$<?= number_format($product["price"], 2) ?></p>

          <!-- Rating summary -->
          <?php if ($count > 0): ?>
            <p>
              Rating: <strong><?= number_format($avg, 1) ?></strong> / 5 (<?= $count ?> reviews)
            </p>
          <?php else: ?>
            <p class="text-muted">Belum ada review</p>
          <?php endif; ?>
          
          <button class="btn btn-sm add-to-cart"
                  data-id="<?= htmlspecialchars($product["id"]) ?>"
                  data-name="<?= htmlspecialchars($product["title"]) ?>"
                  data-price="<?= htmlspecialchars($product["price"]) ?>">
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

  <!-- Pagination -->
  <nav class="mt-5 d-flex justify-content-center">
    <ul class="pagination">
      <li class="page-item <?= $data["currentPage"] > 1 ? "" : "disabled" ?>">
        <a class="page-link" href="<?= BASEURL ?>/product/index/<?= $data[
  "currentPage"
] - 1 ?>">Previous</a>
      </li>

      <?php for ($i = 1; $i <= $data["totalPages"]; $i++): ?>
        <li class="page-item <?= $i == $data["currentPage"] ? "active" : "" ?>">
          <a class="page-link" href="<?= BASEURL ?>/product/index/<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $data["currentPage"] < $data["totalPages"]
        ? ""
        : "disabled" ?>">
        <a class="page-link" href="<?= BASEURL ?>/product/index/<?= $data[
  "currentPage"
] + 1 ?>">Next</a>
      </li>
    </ul>
  </nav>
</div>
