

<!-- Kolase -->
<div class="container py-5">
  <div class="row g-3">
    <div class="col-md-6">
      <div class="collection-box bg-dark">
        <img src="<?= BASEURL; ?>/assets/img/model1.jpg" alt="Women">
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
            <img src="<?= BASEURL; ?>/assets/img/model4.png" alt="Men">
            <div class="collection-content">
              <h6>HOT LIST</h6>
              <h5>MEN COLLECTION</h5>
              <a href="#">SHOP NOW</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="collection-box">
            <img src="<?= BASEURL; ?>/assets/img/model5.jpg" alt="Kids" style="height: 350px;">
            <div class="collection-content">
              <h6>HOT LIST</h6>
              <h5>COMFORT COLLECTIONS</h5>
              <a href="#">SHOP NOW</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="collection-box">
            <img src="<?= BASEURL; ?>/assets/img/model6.jpg" alt="Kids" style="height: 350px;">
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
<div class="container">
  <h4 id="produk" class="mb-3 py-4">Popular Product</h4>
  <div class="row row-cols-1 row-cols-md-4 g-4">
    <?php foreach ($data['products'] as $product): ?>
      <div class="col product-item"
           data-category="<?= strtolower($product['category_slug']); ?>"
           data-gender="<?= strtolower($product['gender']); ?>">
        <div class="card border card-3d interactive">
          <img src="<?= BASEURL; ?>/assets/img/<?= explode(',', $product['images'])[0]; ?>"
               class="card-img-top product-img"
               alt="product"
               data-bs-toggle="modal"
               data-bs-target="#productModal"
               data-title="<?= htmlspecialchars($product['title']); ?>"
               data-price="<?= number_format($product['price'], 2); ?>"
               data-category="<?= htmlspecialchars($product['category_name']); ?>"
               data-description="<?= htmlspecialchars($product['description']); ?>"
               data-gender="<?= htmlspecialchars($product['gender']); ?>"
               data-image="<?= BASEURL; ?>/assets/img/<?= explode(',', $product['images'])[0]; ?>"
               data-stock="<?= $product['stock']; ?>">

          <div class="card-body text-center" style="background-color: #847e7b;">
            <h6 class="card-title"><?= htmlspecialchars($product['title']); ?></h6>
            <p class="card-text">$<?= number_format($product['price'], 2); ?></p>
            <button class="btn btn-sm add-to-cart">Add to Cart</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-5 position-relative">
    <a href="<?= BASEURL; ?>/product" class="btn btn-dark px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2">
      Lihat Semua Produk
      <!-- Panah Kanan SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16">
        <path d="M16 8a8 8 0 1 0-16 0 8 8 0 0 0 16 0zM8.5 4a.5.5 0 0 1 .5.5v2.5h3a.5.5 0 0 1 0 1h-3V11a.5.5 0 0 1-1 0V8.5h-3a.5.5 0 0 1 0-1h3V4.5a.5.5 0 0 1 .5-.5z"/>
      </svg>
    </a>
  </div>
</div>

   


