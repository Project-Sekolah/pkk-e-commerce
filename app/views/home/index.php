<!-- Hero Section -->
<section class="hero d-flex align-items-center text-white">
  <div class="container text-start">
    <h1 class="display-4 fw-bold" id="home">Effortless Style</h1>
    <h1 class="display-4 fw-bold">Endless Comfort</h1>
    <p class="lead">Made with love, nature's colors, and a touch of warmth.</p>
    <a href="#produk" class="btn px-4 py-2 mt-3">Discover Now</a>
  </div>
</section>

<!-- Kolase -->
<div class="container py-5">
  <div class="row g-3">
    <div class="col-md-6">
      <div class="collection-box bg-light">
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
  <h4 id="produk" class="mb-3 py-4">Popular Products</h4>
  <div class="row row-cols-1 row-cols-md-4 g-4">
    <?php foreach ($data['products'] as $product): ?>
            <div class="col product-item"
                 data-category="<?= strtolower($product['category_slug']); ?>"
                 data-gender="<?= strtolower($product['gender']); ?>">
                <div class="card border">
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

  <div class="text-end mt-3">
    <a href="<?= BASEURL; ?>/product" class="btn btn-outline-dark">Lihat Semua Produk</a>
  </div>
</div>

<!-- Footer Banner -->
<div class="container-fluid py-4" style="overflow: hidden;">
  <div class="collection-box bg-light">
    <img src="<?= BASEURL; ?>/assets/img/couple.png" alt="Men" class="img-fluid" style="max-width: 100%; height: auto; display: block; margin-left: auto; margin-right: auto;">
  </div>
</div>



