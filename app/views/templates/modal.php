<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content custom-modal text-dark">
  <!-- Close Button -->
  <button type="button" class="btn-close btn-close-modal" data-bs-dismiss="modal" aria-label="Close"></button>

  <div class="modal-body px-3 py-2">
    <div class="row g-0">

      <!-- Left: Product Image -->
      <div class="col-md-5 d-flex justify-content-center align-items-center p-3">
        <div class="modal-image-container">
          <img id="modalImage" src="" class="img-fluid rounded shadow-sm modal-image" alt="Product Image">
          <div class="price-tag">
            <i class="bi bi-currency-rupee"></i><span id="modalPrice"></span>
          </div>
        </div>
      </div>

      <!-- Right: Product Info -->
      <div class="col-md-7 p-3 modal-product-info">
        <h4 id="modalTitle" class="fw-bold mb-2"></h4>
        <hr class="my-2">
        <div class="mb-2 small">
          <p><i class="bi bi-tags-fill text-dark me-2"></i><strong>Category:</strong> <span id="modalCategory"></span></p>
          <p><i class="bi bi-gender-ambiguous text-dark me-2"></i><strong>Gender:</strong> <span id="modalGender"></span></p>
          <p><i class="bi bi-box-seam text-dark me-2"></i><strong>Stock:</strong> <span id="modalStock"></span></p>
        </div>

        <p class="mb-2"><i class="bi bi-card-text text-dark me-2"></i><strong>Description:</strong></p>
        <p id="modalDescription" class="small text-muted mb-3"></p>

<!-- Average Rating -->
<p id="modalRatingSummary" class="mb-2 fw-semibold text-dark">
  Rating: <span id="modalRating"></span>
</p>

<div class="rating-stars-static text-warning fs-5 mb-2"></div>
        

        <button id="modalAddToCartBtn" class="btn add-to-cart">
          <i class="bi bi-cart-plus-fill"></i> Add To Cart
        </button>
      </div>
    </div>

    <!-- Rating Input -->
    <?php if (isset($_SESSION["user"])): ?>
    <div class="mt-2">
      <form action="<?= BASEURL ?>/product/addRating" method="POST" id="ratingForm">
  <!-- Hidden Inputs -->
  <input type="hidden" name="user_id" value="<?= $userId ?>">
  <input type="hidden" name="product_id" value="<?= $productId ?>">
  <input type="hidden" name="rating" id="ratingValue">

  <!-- Rating UI -->
  <div class="mt-2">
    <h5 class="fw-bold">Give Your Rating</h5>
    <div id="starInput" class="text-warning mb-2" style="cursor: pointer;">
      <i class="bi bi-star" data-value="1"></i>
      <i class="bi bi-star" data-value="2"></i>
      <i class="bi bi-star" data-value="3"></i>
      <i class="bi bi-star" data-value="4"></i>
      <i class="bi bi-star" data-value="5"></i>
    </div>
    
    <!-- Comment -->
    <textarea class="form-control mb-2" name="review_text" rows="2" placeholder="Write a comment..."></textarea>

    <!-- Submit -->
    <button type="submit" class="btn btn-submit-rating">Submit Rating</button>
  </div>
</form>
    </div>
<?php endif; ?>

       <!-- Comments -->
        <div class="mt-4">
          <h5 class="fw-bold">User Reviews</h5>
          <div id="modalCommentsContainer"></div>
            
  </div>
</div>
</div>
</div>
</div>

