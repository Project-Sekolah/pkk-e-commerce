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

        <!-- Static Star Rating -->
        <div class="mb-3 text-warning rating-stars-static">
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star-fill"></i>
          <i class="bi bi-star"></i>
        </div>

        <button class="btn add-to-cart">
          <i class="bi bi-cart-plus-fill"></i> Buy Now
        </button>
      </div>
    </div>

    <!-- Rating Input -->
    <div class="mt-2">
      <h5 class="fw-bold">Give Your Rating</h5>
      <div id="starInput" class="text-warning mb-2">
        <i class="bi bi-star" data-value="1"></i>
        <i class="bi bi-star" data-value="2"></i>
        <i class="bi bi-star" data-value="3"></i>
        <i class="bi bi-star" data-value="4"></i>
        <i class="bi bi-star" data-value="5"></i>
      </div>
      <textarea class="form-control mb-2" id="ratingComment" rows="2" placeholder="Write a comment..."></textarea>
      <button class="btn btn-submit-rating" onclick="submitRating()">Submit Rating</button>
    </div>

    <!-- Comments -->
    <div class="mt-4">
      <h5 class="fw-bold">User Reviews</h5>

      <div class="d-flex align-items-start mb-3">
        <img src="https://media.istockphoto.com/id/1337144146/vector/default-avatar-profile-icon-vector.jpg?s=612x612&w=0&k=20&c=BIbFwuv7FxTWvh5S3vB6bkT0Qv8Vn8N5Ffseq84ClGI=" class="rounded-circle me-2" alt="User" width="40" height="40">
        <div>
          <strong>JohnDoe</strong>
          <div class="text-warning rating-stars-static">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star"></i>
            <i class="bi bi-star"></i>
          </div>
          <p class="review-comment mb-0">Great product, fast delivery!</p>
        </div>
      </div>

      <div class="d-flex align-items-start mb-3">
        <img src="https://media.istockphoto.com/id/1337144146/vector/default-avatar-profile-icon-vector.jpg?s=612x612&w=0&k=20&c=BIbFwuv7FxTWvh5S3vB6bkT0Qv8Vn8N5Ffseq84ClGI=" class="rounded-circle me-2" alt="User" width="40" height="40">
        <div>
          <strong>JaneSmith</strong>
          <div class="text-warning rating-stars-static">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
          </div>
          <p class="review-comment mb-0">Love the quality, will buy again!</p>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>

