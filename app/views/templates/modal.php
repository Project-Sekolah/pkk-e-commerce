<!-- Product Detail Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Product Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex flex-column flex-md-row align-items-center gap-4">
        <img id="modalImage" src="" class="img-fluid rounded shadow" style="max-width: 300px;" alt="Product Image">
        <div>
          <h5 id="modalTitle"></h5>
          <p class="text-muted">Category: <span id="modalCategory"></span></p>
          <p class="text-muted">Gender: <span id="modalGender"></span></p>
          <p id="modalDescription"></p>
          <p class="fw-bold">Price: $<span id="modalPrice"></span></p>
          <p class="fw-bold">Stock: <span id="modalStock"></span></p>
          <button class="btn btn-primary add-to-cart">Add to Cart</button>
        </div>
      </div>
    </div>
  </div>
</div>