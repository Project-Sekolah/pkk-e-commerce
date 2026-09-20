const BASEURL = window.BASEURL || window.location.origin;

// Auto attach CSRF token to fetch requests
const _originalFetch = window.fetch;
window.fetch = function(url, options = {}) {
    options = options || {};
    options.headers = options.headers || {};
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        if (options.headers instanceof Headers) {
            if (!options.headers.has('X-CSRF-TOKEN')) options.headers.set('X-CSRF-TOKEN', token);
        } else {
            if (!options.headers['X-CSRF-TOKEN']) options.headers['X-CSRF-TOKEN'] = token;
        }
    }
    return _originalFetch(url, options);
};

const $cartItems = document.getElementById("cart-items");
const $subtotal = document.getElementById("subtotal");
const $delivery = document.getElementById("delivery");
const $taxes = document.getElementById("taxes");
const $discount = document.getElementById("discount");
const $total = document.getElementById("total");
const $discountInput = document.getElementById("discountInput");
const $agreeTerms = document.getElementById("agreeTerms");
const $cartCount = document.getElementById("cart-count");
const $totalPriceElement = document.getElementById("total-price");

let cart = [];
let delivery = 0;
let taxes = 0;
let discount = 0;
let activeDiscount = null;

// Function to apply discount
function applyDiscount() {
    const discountName = $discountInput?.value.trim();

    if (!discountName) {
        Swal.fire({
            icon: "warning",
            title: "Nama diskon kosong",
            text: "Please enter a valid discount name"
        });
        return;
    }

    if (/^[0-9]+$/.test(discountName)) {
        Swal.fire({
            icon: "warning",
            title: "Nama diskon tidak valid",
            text: "Discount name cannot be a number"
        });
        return;
    }

    fetch(`${BASEURL}/Cart/validateDiscount`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ discount_name: discountName })
    })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                document.getElementById("applyDiscountBtn").disabled = true;
                activeDiscount = {
                    percentage: parseFloat(data.discount?.percentage || 0),
                    applicableProducts: (data.applicable_products || []).map(String)
                };
                document.getElementById('checkoutDiscountName')?.setAttribute('value', data.discount?.name || discountName);

                cart.forEach(item => {
                    if (activeDiscount.applicableProducts.includes(String(item.id))) {
                        item.discount_name = discountName;
                        item.discount_percentage = activeDiscount.percentage;
                    }
                });

                const discountAmount = cart.reduce((sum, item) => {
                    const price = parseFloat(item.price) || 0;
                    const quantity = item.quantity || 0;
                    return sum + (item.discount_percentage ? price * quantity * (item.discount_percentage / 100) : 0);
                }, 0);

                updateDisplay(calculateSubtotal(), discountAmount);
                Swal.fire({
                    icon: "success",
                    title: "Diskon berhasil!",
                    text: `${data.discount?.percentage || 0}% untuk produk yang memenuhi syarat`
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Diskon gagal",
                    text: data.message
                });
            }
        })
        .catch(err => {
            console.error("Discount validation error:", err);
            Swal.fire({
                icon: "error",
                title: "Validasi diskon gagal",
                text: "Failed to validate discount. Please try again."
            });
        });
}

// Attach event listener to discount button
document.getElementById("applyDiscountBtn")?.addEventListener("click", applyDiscount);

// Function to format currency
function formatDollar(num) {
    return `Rp ${Number(num || 0).toLocaleString('id-ID')}`;
}

// Function to update display
function updateDisplay(subtotal, discountAmount = 0) {
    const courier = document.getElementById('checkoutCourier');
    delivery = Number(courier?.selectedOptions?.[0]?.dataset.fee || 15000);
    taxes = 0;

    if (!$subtotal || !$delivery || !$taxes || !$discount || !$total) return;

    $subtotal.innerText = formatDollar(subtotal);
    $delivery.innerText = formatDollar(delivery);
    $taxes.innerText = formatDollar(taxes);

    discountAmount = isNaN(discountAmount) ? 0 : discountAmount;
    $discount.innerText = discountAmount > 0 ? "- " + formatDollar(discountAmount) : "- Rp 0";

    const total = subtotal + delivery + taxes - discountAmount;
    $total.innerText = formatDollar(total);
    if ($totalPriceElement) $totalPriceElement.innerText = formatDollar(total);
}

// Function to calculate subtotal
function calculateSubtotal() {
    return cart.reduce((sum, item) => {
        const price = parseFloat(item.price);
        const quantity = item.quantity;
        return sum + price * quantity;
    }, 0);
}


// Function to render cart items
function renderCartItems() {
    if (!$cartItems) return;

    $cartItems.innerHTML = "";
    let total = 0;
    let itemCount = 0;
    let discountAmount = 0;

    const discountButton = document.getElementById("applyDiscountBtn");
    const isDiscountApplied = discountButton?.disabled ?? false;

    cart.forEach(item => {
        const price = parseFloat(item.price);
        const quantity = item.quantity;
        const subtotal = price * quantity;
        let itemDiscount = 0;

        if (isDiscountApplied && item.discount_percentage) {
            itemDiscount = subtotal * (item.discount_percentage / 100);
            discountAmount += itemDiscount;
        }

        total += subtotal;
        itemCount += quantity;

        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";

        const itemInfo = document.createElement("div");
        itemInfo.className = "me-3";
        itemInfo.innerHTML = `
            <strong>${item.name}</strong>
            <br>
            ${formatDollar(price)} x ${quantity} = ${formatDollar(subtotal)}
            ${
                isDiscountApplied && item.discount_name
                    ? `<br><small class="text-danger">- ${item.discount_percentage}% (${formatDollar(itemDiscount)})</small>`
                    : ""
            }
        `;

        const btnGroup = document.createElement("div");
        btnGroup.className = "btn-group";

        const minusBtn = document.createElement("button");
        minusBtn.className = "btn btn-sm btn-outline-secondary";
        minusBtn.textContent = "-";
        minusBtn.addEventListener("click", () => syncDecreaseItemFromServer(item.item_id));

        const plusBtn = document.createElement("button");
        plusBtn.className = "btn btn-sm btn-outline-secondary";
        plusBtn.textContent = "+";
        plusBtn.addEventListener("click", () => syncAddItemToServer(item.id, 1));

        const removeBtn = document.createElement("button");
        removeBtn.className = "btn btn-sm btn-danger";
        removeBtn.textContent = "Remove";
        removeBtn.addEventListener("click", () => syncDeleteItemFromServer(item.item_id));

        btnGroup.appendChild(minusBtn);
        btnGroup.appendChild(plusBtn);
        btnGroup.appendChild(removeBtn);

        li.appendChild(itemInfo);
        li.appendChild(btnGroup);
        $cartItems.appendChild(li);
    });

    updateDisplay(total, discountAmount);
    if ($cartCount) $cartCount.textContent = itemCount;
}


async function loadCartFromServer() {
    try {
        const response = await fetch(`${BASEURL}/Cart/getCart`);
        const data = await response.json();
        cart = data.items.map(item => ({
            id: item.product_id,
            item_id: item.item_id,
            name: item.title,
            price: parseFloat(item.price),
            quantity: item.quantity,
            discount_name: item.discount_name,
            discount_percentage: parseFloat(item.discount_percentage)
        }));
        renderCartItems();
    } catch (err) {
        console.error("Gagal memuat keranjang:", err);
    }
}


function syncDecreaseItemFromServer(itemId) {
    fetch(`${BASEURL}/Cart/decreaseItem`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ item_id: itemId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadCartFromServer();
        })
        .catch(err => console.error("Decrease item error:", err));
}

function syncAddItemToServer(productId, quantity = 1) {
    fetch(`${BASEURL}/Cart/addItem`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ product_id: productId, quantity })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadCartFromServer();
        })
        .catch(err => console.error("Add item error:", err));
}

function syncDeleteItemFromServer(itemId) {
    fetch(`${BASEURL}/Cart/deleteItem`, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ item_id: itemId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) loadCartFromServer();
        })
        .catch(err => console.error("Delete item error:", err));
}
document.addEventListener("DOMContentLoaded", () => {
    // Load cart data from server when the page loads
    loadCartFromServer();

    document.getElementById('checkoutCourier')?.addEventListener('change', () => {
        updateDisplay(calculateSubtotal());
    });

    // Add event listeners to all "Add to Cart" buttons
    document.querySelectorAll(".add-to-cart:not(#modalAddToCartBtn)").forEach(btn => {
        btn.addEventListener("click", () => {
            // Check if the user is logged in
            if (!IS_LOGGED_IN) {
                Swal.fire({
                    icon: "warning",
                    title: "Login Required!",
                    text: "You need to log in to add products to the cart.",
                    confirmButtonText: "OK"
                });
                return;
            }
            // If logged in, get the product ID and send it to the server
            const productId = btn.getAttribute("data-id");
            syncAddItemToServer(productId);
        });
    });

    // SweetAlert konfirmasi hapus produk
    document.querySelectorAll('.btn-hapus-produk').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = btn.getAttribute('href');
            Swal.fire({
                title: 'Konfirmasi Hapus Produk',
                text: 'Apakah Anda yakin ingin menghapus produk ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });

    document.querySelectorAll('.delete-product-image').forEach(button => {
        button.addEventListener('click', async function () {
            const result = await Swal.fire({
                title: 'Hapus foto ini?',
                text: 'Foto akan dihapus dari produk.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            });
            if (!result.isConfirmed) return;

            const response = await fetch(button.dataset.deleteUrl, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
            });
            if (!response.ok) {
                Swal.fire('Gagal', 'Foto tidak dapat dihapus.', 'error');
                return;
            }

            button.closest('.product-image-choice')?.remove();
            Swal.fire({ icon: 'success', title: 'Foto dihapus', timer: 1200, showConfirmButton: false });
        });
    });

    // SweetAlert konfirmasi hapus diskon
    document.querySelectorAll('.btn-hapus-diskon').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const href = btn.getAttribute('href');
            Swal.fire({
                title: 'Konfirmasi Hapus Diskon',
                text: 'Apakah Anda yakin ingin menghapus diskon ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });

    // Product Image Click - Modal Detail
    document.querySelectorAll(".product-img").forEach(img => {
        img.addEventListener("click", function () {
            document.getElementById("modalTitle").textContent = this.dataset.title;
            document.getElementById("modalPrice").textContent = this.dataset.price;
            document.getElementById("modalCategory").textContent = this.dataset.category;
            document.getElementById("modalGender").textContent = this.dataset.gender;
            document.getElementById("modalDescription").textContent = this.dataset.description || "No description available.";
            document.getElementById("modalStock").textContent = this.dataset.stock;
        });
    });
});
// === Filter Logic ===
const filters = {
    category: [],
    gender: []
};

function filterProducts() {
    const items = document.querySelectorAll(".product-item");
    items.forEach(item => {
        const category = item.getAttribute("data-category").toLowerCase();
        const gender = item.getAttribute("data-gender").toLowerCase();
        const matchCategory =
            filters.category.length === 0 ||
            filters.category.includes(category);
        const matchGender =
            filters.gender.length === 0 || filters.gender.includes(gender);
        item.style.display = matchCategory && matchGender ? "block" : "none";
    });
}

function toggleFilter(type, value) {
    const index = filters[type].indexOf(value);
    index === -1 ? filters[type].push(value) : filters[type].splice(index, 1);
    toggleButtonActive(type, value, index === -1);
    filterProducts();
}

function toggleButtonActive(type, value, isActive) {
    const button = document.querySelector(
        `.${type}-filter[data-${type}="${value}"]`
    );
    button.classList.toggle("active", isActive);
}

function clearFilters() {
    filters.category = [];
    filters.gender = [];
    document
        .querySelectorAll(".btn-outline-light")
        .forEach(btn => btn.classList.remove("active"));
    filterProducts();
}

// === Search Logic ===
function searchProducts() {
    const searchInput = document
        .getElementById("searchInput")
        .value.toLowerCase();
    const items = document.querySelectorAll(".product-item");

    items.forEach(item => {
        const title = item
            .querySelector(".card-title")
            .textContent.toLowerCase();
        const description = item
            .querySelector(".card-text")
            .textContent.toLowerCase();
        const match =
            title.includes(searchInput) || description.includes(searchInput);

        item.style.display = match ? "block" : "none";
        if (match) highlightSearchTerm(item, searchInput);
    });
}

function highlightSearchTerm(item, term) {
    ["card-title", "card-text"].forEach(cls => {
        const el = item.querySelector(`.${cls}`);
        el.innerHTML = el.textContent.replace(
            new RegExp(`(${term})`, "gi"),
            '<span class="highlight">$1</span>'
        );
    });
}

// === 3D Card Hover Effect ===
document.querySelectorAll(".card-3d.interactive").forEach(card => {
    card.addEventListener("mousemove", e => {
        const img = card.querySelector("img");
        const rect = card.getBoundingClientRect();
        const rotateX = (rect.height / 2 - (e.clientY - rect.top)) / 10;
        const rotateY = (e.clientX - rect.left - rect.width / 2) / 10;
        img.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });

    card.addEventListener("mouseleave", () => {
        card.querySelector("img").style.transform = "rotateX(0) rotateY(0)";
    });
});
// === DOM Ready Actions ===
document.addEventListener("DOMContentLoaded", () => {
    // Product Image Click - Modal Detail
    document.querySelectorAll(".product-img").forEach(img => {
        img.addEventListener("click", function () {
            document.getElementById("modalTitle").textContent =
                this.dataset.title;
            document.getElementById("modalPrice").textContent =
                this.dataset.price;
            document.getElementById("modalCategory").textContent =
                this.dataset.category;
            document.getElementById("modalGender").textContent =
                this.dataset.gender;
            document.getElementById("modalDescription").textContent =
                this.dataset.description || "No description available.";
            document.getElementById("modalStock").textContent =
                this.dataset.stock;
            document.getElementById("modalRatingCount").textContent =
                `${this.dataset.commentCount || 0} komentar`;
        });
    });
});

// === Auth Section Visibility ==
function showRegister() {
    document.getElementById("login-section").style.display = "none";
    document.getElementById("register-section").style.display = "block";
}

function showLogin() {
    document.getElementById("register-section").style.display = "none";
    document.getElementById("login-section").style.display = "block";
}

// === Modal Logic ===
function handleProductModalShow(event) {
    const button = event.relatedTarget;
    const modal = document.getElementById("productModal");
    if (!button || !modal) return;

    // Ambil data produk dari atribut data-*
    const userName = button.dataset.username;
    const userImage = button.dataset.userimage;
    const title = button.dataset.title;
    const price = button.dataset.price;
    const category = button.dataset.category;
    const description = button.dataset.description;
    const gender = button.dataset.gender;
    const image = button.dataset.image;
    const stock = button.dataset.stock;
    const rating = parseFloat(button.dataset.rating) || 0;
    const ratingCount = button.dataset.ratingcount || "";
    const ownerPhone = button.dataset.ownerPhone || "";
    let productImages = [];
    try {
        productImages = JSON.parse(button.dataset.images || "[]");
    } catch (error) {
        productImages = button.dataset.image ? [button.dataset.image] : [];
    }

    // Ambil product_id dan user_id
    const productId = button.dataset.productid || button.dataset.id;
    const userId = button.dataset.userid;

    // Isi data produk di modal
    setModalText(modal, {
        id: productId,
        userName,
        userImage,
        title,
        price,
        category,
        description,
        gender,
        image,
        images: productImages,
        stock,
        rating,
        ratingCount
    });
    // Set owner phone in modal
    const phoneSpan = document.getElementById("modalOwnerPhone");
    if (phoneSpan){ phoneSpan.textContent = ownerPhone;}
    const ownerLink = modal.querySelector('#modalOwnerLink');
    if (ownerLink && button.dataset.userid) ownerLink.href = `${BASEURL}/store/${button.dataset.userid}`;

    // Set hidden input di form rating
    modal.querySelector("input[name='product_id']")?.setAttribute("value", productId || "");
    modal.querySelector("input[name='user_id']")?.setAttribute("value", userId || "");

    const modalCartButton = modal.querySelector('#modalAddToCartBtn');
    if (modalCartButton) {
        modalCartButton.dataset.id = productId || '';
        modalCartButton.onclick = async () => {
            if (!IS_LOGGED_IN) {
                Swal.fire({ icon: 'warning', title: 'Login diperlukan', text: 'Silakan login untuk memasukkan produk ke keranjang.' });
                return;
            }
            const response = await fetch(`${BASEURL}/Cart/addItem`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ product_id: productId, quantity: 1 })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Produk tidak dapat ditambahkan.' });
                return;
            }
            await loadCartFromServer();
            Swal.fire({ icon: 'success', title: 'Masuk keranjang', timer: 1200, showConfirmButton: false });
        };
    }

    // Ambil komentar terbaru dari endpoint produk agar popup selalu sinkron.
    loadProductReviews(modal, productId);
}

async function loadProductReviews(modal, productId, page = 1) {
    const commentsContainer = modal.querySelector("#modalCommentsContainer");
    const commentsStatus = modal.querySelector("#modalCommentsStatus");
    if (!commentsContainer || !productId) return;

    commentsContainer.innerHTML = '<p class="text-muted small">Memuat komentar...</p>';
    try {
        const response = await fetch(`${BASEURL}/product/${productId}?reviews_page=${page}`, {
            headers: { Accept: "application/json" }
        });
        if (!response.ok) throw new Error("Komentar gagal dimuat.");
        const data = await response.json();
        if (commentsStatus) commentsStatus.textContent = `${data.total_comments || 0} komentar`;
        renderProductReviews(modal, data.reviews || [], data.reviews_meta || {}, productId);
        const countElement = modal.querySelector("#modalRatingCount");
        if (countElement) countElement.textContent = `${data.total_comments || 0} komentar`;
    } catch (error) {
        commentsContainer.innerHTML = '<p class="text-danger small">Komentar tidak dapat dimuat.</p>';
        console.error("[Product Comments]", error);
    }
}

function setModalText(modal, data) {
    const textFields = {
        modalUsername: data.userName,
        modalTitle: data.title,
        modalPrice: data.price,
        modalCategory: data.category,
        modalDescription: data.description,
        modalGender: data.gender,
        modalStock: data.stock,
    };
    Object.entries(textFields).forEach(([id, value]) => {
        const element = modal.querySelector(`#${id}`);
        if (element) element.textContent = value || "";
    });

    const userImage = modal.querySelector("#modalUserImage");
    if (userImage) userImage.src = data.userImage || "/assets/img/default.jpg";
    const gallery = modal.querySelector("#modalImageGallery");
    if (gallery) {
        const slides = gallery.querySelector("#modalImageSlides");
        const images = (data.images?.length ? data.images : [data.image]).filter(Boolean);
        if (!slides) return;
        slides.replaceChildren();
        images.forEach((image, index) => {
            const productImage = document.createElement("img");
            productImage.src = image;
            productImage.alt = `${data.title || "Product"} ${index + 1}`;
            productImage.className = "d-block w-100 modal-image";
            productImage.style.cssText = "height: 320px; object-fit: cover;";
            const slide = document.createElement("div");
            slide.className = `carousel-item${index === 0 ? " active" : ""}`;
            slide.appendChild(productImage);
            slides.appendChild(slide);
        });
    }

    const detailLink = modal.querySelector("#modalDetailLink");
    if (detailLink && data.id) detailLink.href = `${BASEURL}/product/${data.id}`;

    const starsHtml = generateStarsHtml(data.rating);
    modal.querySelectorAll(".rating-stars-static").forEach(element => {
        element.innerHTML = starsHtml;
    });

    const ratingElement = modal.querySelector("#modalRating");
    if (ratingElement) ratingElement.textContent = `${data.rating} / 5 (${data.ratingCount} reviews)`;
}

function generateStarsHtml(rating) {
    const maxStars = 5;
    let stars = "";
    for (let i = 1; i <= maxStars; i++) {
        if (i <= Math.floor(rating)) {
            stars += '<i class="bi bi-star-fill"></i>';
        } else if (i - rating < 1) {
            stars += '<i class="bi bi-star-half"></i>';
        } else {
            stars += '<i class="bi bi-star"></i>';
        }
    }
    return stars;
}

function renderProductReviews(modal, reviewers, meta, productId) {
    const commentsContainer = modal.querySelector("#modalCommentsContainer");
    if (!commentsContainer) return;
    commentsContainer.replaceChildren();

    if (!reviewers.length) {
        commentsContainer.innerHTML = '<p class="text-muted">Belum ada review</p>';
        return;
    }

    reviewers.forEach(reviewer => {
        const stars = generateStarsHtml(reviewer.rating || 0);
        const commentHtml = `<div class="d-flex align-items-start mb-3 review-item" data-review-id="${reviewer.id}">
                <img src="${escapeHtml(reviewer.user_image || '/assets/img/default.jpg')}" class="rounded-circle me-2" alt="User" width="40" height="40">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between gap-2">
                        <strong>${escapeHtml(reviewer.username || 'Anonymous')}</strong>
                        ${reviewer.can_edit ? `<span class="review-actions d-inline-flex gap-1"><button type="button" class="btn btn-sm btn-light border edit-review" title="Edit komentar" aria-label="Edit komentar" data-review-id="${reviewer.id}" data-rating="${reviewer.rating}" data-review-text="${escapeHtml(encodeURIComponent(reviewer.review_text || ''))}"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-sm btn-light border text-danger delete-review" title="Hapus komentar" aria-label="Hapus komentar" data-review-id="${reviewer.id}"><i class="bi bi-trash"></i></button></span>` : ''}
                    </div>
                    <div class="text-warning rating-stars-static">${stars}</div>
                    <p class="review-comment mb-0">${escapeHtml(reviewer.review_text || '') || '<span class="text-muted">Tanpa komentar tertulis</span>'}</p>
                </div>
            </div>`;
        commentsContainer.insertAdjacentHTML("beforeend", commentHtml);
    });

    commentsContainer.querySelectorAll(".edit-review").forEach(button => {
        button.addEventListener("click", () => editReview(button, modal, productId));
    });
    commentsContainer.querySelectorAll(".delete-review").forEach(button => {
        button.addEventListener("click", () => deleteReview(button, modal, productId));
    });

    const pagination = modal.querySelector("#modalCommentsPagination");
    if (pagination) {
        pagination.replaceChildren();
        if ((meta.last_page || 1) > 1) {
            const previous = document.createElement("button");
            previous.className = "btn btn-sm btn-outline-secondary";
            previous.textContent = "Sebelumnya";
            previous.disabled = meta.current_page <= 1;
            previous.addEventListener("click", () => loadProductReviews(modal, productId, meta.current_page - 1));
            const label = document.createElement("span");
            label.className = "small text-muted";
            label.textContent = `Halaman ${meta.current_page} dari ${meta.last_page}`;
            const next = document.createElement("button");
            next.className = "btn btn-sm btn-outline-secondary";
            next.textContent = "Berikutnya";
            next.disabled = meta.current_page >= meta.last_page;
            next.addEventListener("click", () => loadProductReviews(modal, productId, meta.current_page + 1));
            pagination.append(previous, label, next);
        }
    }
}

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function editReview(button, modal, productId) {
    let currentReviewText = button.dataset.reviewText || "";
    try {
        currentReviewText = decodeURIComponent(currentReviewText);
    } catch (error) {
        currentReviewText = button.dataset.reviewText || "";
    }

    const result = await Swal.fire({
        title: "Edit komentar",
        input: "textarea",
        inputValue: currentReviewText,
        inputPlaceholder: "Tulis komentar Anda...",
        inputAttributes: {
            autocapitalize: "off",
            autocorrect: "on",
            spellcheck: "true"
        },
        didOpen: () => {
            Swal.getInput()?.removeAttribute("readonly");
            Swal.getInput()?.removeAttribute("disabled");
            Swal.getInput()?.focus();
        },
        showCancelButton: true,
        confirmButtonText: "Simpan",
        cancelButtonText: "Batal",
        inputValidator: value => String(value || '').length > 1000 ? "Maksimal 1000 karakter." : undefined
    });
    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`${BASEURL}/product/rating/${button.dataset.reviewId}`, {
            method: "PATCH",
            headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": csrfToken() },
            body: JSON.stringify({ rating: Number(button.dataset.rating), review_text: String(result.value || '') })
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || "Komentar tidak dapat diperbarui.");
        await loadProductReviews(modal, productId);
    } catch (error) {
        Swal.fire("Gagal", error.message, "error");
    }
}

async function deleteReview(button, modal, productId) {
    const result = await Swal.fire({
        title: "Hapus komentar?",
        text: "Komentar ini akan dihapus permanen.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus",
        cancelButtonText: "Batal"
    });
    if (!result.isConfirmed) return;

    const response = await fetch(`${BASEURL}/product/rating/${button.dataset.reviewId}`, {
        method: "DELETE",
        headers: { Accept: "application/json", "X-CSRF-TOKEN": csrfToken() }
    });
    if (!response.ok) return Swal.fire("Gagal", "Komentar tidak dapat dihapus.", "error");
    await loadProductReviews(modal, productId);
}

// Pasang handler saat modal ditampilkan
document.getElementById("productModal")?.addEventListener("show.bs.modal", handleProductModalShow);

//input rating
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("#starInput i").forEach(star => {
        star.addEventListener("click", () => {
            const rating = Number(star.dataset.val || 0);
            const ratingInput = document.getElementById("ratingValue");
            if (ratingInput) ratingInput.value = rating;
            updateStarUI(rating);
        });
    });

    function updateStarUI(rating) {
        document.querySelectorAll("#starInput i").forEach(star => {
            const active = Number(star.dataset.val || 0) <= rating;
            star.classList.toggle("bi-star-fill", active);
            star.classList.toggle("bi-star", !active);
        });
    }

    document.getElementById("productModal")?.addEventListener("hidden.bs.modal", () => {
        const ratingInput = document.getElementById("ratingValue");
        if (ratingInput) ratingInput.value = "";
        updateStarUI(0);
        const reviewInput = document.querySelector("#ratingForm textarea[name='review_text']");
        if (reviewInput) reviewInput.value = "";
    });
});

document.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const ownerName = button.getAttribute('data-owner');
    const ownerPhone = button.getAttribute('data-owner-phone');
    const modalOwnerName = document.getElementById('modalOwnerName');
    const modalOwnerPhone = document.getElementById('modalOwnerPhone');

    if (modalOwnerName) {
        modalOwnerName.textContent = ownerName || 'Unknown';
    }
    if (modalOwnerPhone) {
        modalOwnerPhone.textContent = ownerPhone || '-';
    }
});

// Function to view invoice
document.querySelectorAll('.view-invoice-btn').forEach(button => {
    button.addEventListener('click', function () {
        const orderId = this.dataset.orderId;
        if (!orderId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Order ID is missing!'
            });
            return;
        }

        window.open(`${BASEURL}/OrderController/viewInvoice/${orderId}`, '_blank');
    });
});

