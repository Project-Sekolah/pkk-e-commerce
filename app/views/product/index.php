



<!-- Hero Section -->
<section class="hero d-flex align-items-center text-white">
  <div class="container text-start">
    <h1 class="display-4 fw-bold" id="home">Effortless Style</h1>
    <h1 class="display-4 fw-bold">Endless Comfort</h1>
    <p class="lead">Made with love, nature's colors, and a touch of warmth.</p>
    <a href="#produk" class="btn px-4 py-2 mt-3">Discover Now</a> 
  </div>
</section>

<!-- Product Section -->
<div class="container mb-4">
  <h4 id="produk" class="mb-3 py-4">Popular Products</h4>

  <!-- Filter Section -->
  <div class="row mb-4">
  <div class="col-12 col-md-6 mb-3 mb-md-0">
    <input type="text" id="searchInput" class="form-control" placeholder="Search Products..." onkeyup="searchProducts()">
  </div>
  <div class="col-12 col-md-6 text-md-end">
    <div class="btn-group" role="group">
      <!-- Default Filter: "All" -->
      <button class="btn btn-outline-light" id="allFilterBtn" onclick="clearFilters()">All</button>

      <!-- Dynamic Filter Buttons for Categories -->
      <?php foreach ($data['categories'] as $category): ?>
        <button class="btn btn-outline-light category-filter" data-category="<?= strtolower($category['slug']); ?>" onclick="toggleFilter('category', '<?= strtolower($category['slug']); ?>')">
          <?= htmlspecialchars($category['name']); ?>
        </button>
      <?php endforeach; ?>

      <!-- Filter for Gender -->
      <button class="btn btn-outline-light gender-filter" data-gender="pria" onclick="toggleFilter('gender', 'pria')">Pria</button>
      <button class="btn btn-outline-light gender-filter" data-gender="wanita" onclick="toggleFilter('gender', 'wanita')">Wanita</button>
    </div>
  </div>
</div>


  <!-- Product Cards -->
  <div class="row row-cols-1 row-cols-md-4 g-4" id="productGrid">
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

  <!-- Pagination -->
  <nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination">
        <li class="page-item <?= ($data['currentPage'] > 1) ? '' : 'disabled'; ?>">
            <a class="page-link" href="<?= BASEURL; ?>/product/index/<?= $data['currentPage'] - 1; ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
            <li class="page-item <?= ($i == $data['currentPage']) ? 'active' : ''; ?>">
                <a class="page-link" href="<?= BASEURL; ?>/product/index/<?= $i; ?>"><?= $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= ($data['currentPage'] < $data['totalPages']) ? '' : 'disabled'; ?>">
            <a class="page-link" href="<?= BASEURL; ?>/product/index/<?= $data['currentPage'] + 1; ?>">Next</a>
        </li>
    </ul>
  </nav>
</div>

