

<!-- Kolase -->
<div class="container py-5">
  <div class="row g-3">
    <div class="col-md-6">
      <div class="collection-box bg-dark">
        <img src="<?= BASEURL ?>/assets/img/model1.jpg" alt="Women">
        <div class="collection-content">
          <h6>HOT LIST</h6>
          <h5>WOMEN COLLECTION</h5>
          <a href="women.html">SHOP NOW</a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="row g-3">
        <div class="col-12">
          <div class="collection-box bg-light">
            <img src="<?= BASEURL ?>/assets/img/model4.png" alt="Men">
            <div class="collection-content">
              <h6>HOT LIST</h6>
              <h5>MEN COLLECTION</h5>
              <a href="#">SHOP NOW</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="collection-box">
            <img src="<?= BASEURL ?>/assets/img/model5.jpg" alt="Kids" style="height: 350px;">
            <div class="collection-content">
              <h6>HOT LIST</h6>
              <h5>COMFORT COLLECTIONS</h5>
              <a href="#">SHOP NOW</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="collection-box">
            <img src="<?= BASEURL ?>/assets/img/model6.jpg" alt="Kids" style="height: 350px;">
            <div class="collection-content">
              <h6>E-GIFT CARDS</h6>
              <p>Surprise someone with the gift they really want.</p>
              <a href="#">DISCOVER MORE</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Popular Products -->
<?php $user = $data["user"] ?? null; ?>
<div class="container">
  <h4 id="produk" class="mb-3 py-4">Popular Product</h4>
  <div class="row row-cols-1 row-cols-md-4 g-4" id="productGrid">
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

           <!-- Product Cards -->
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

  <div class="text-center mt-5 position-relative">
    <a href="<?= BASEURL ?>/product" class="btn btn-dark px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2">
      Lihat Semua Produk
      <!-- Panah Kanan SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16">
        <path d="M16 8a8 8 0 1 0-16 0 8 8 0 0 0 16 0zM8.5 4a.5.5 0 0 1 .5.5v2.5h3a.5.5 0 0 1 0 1h-3V11a.5.5 0 0 1-1 0V8.5h-3a.5.5 0 0 1 0-1h3V4.5a.5.5 0 0 1 .5-.5z"/>
      </svg>
    </a>
  </div>
</div>

   


