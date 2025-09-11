// const BASEURL = "http://localhost/raj/ecommerce/pkk-e-commerce/public";
const BASEURL = "https://pkk-e-commerce-production.up.railway.app";

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
            if (data.success) {
                document.getElementById("applyDiscountBtn").disabled = true;
                activeDiscount = {
                    percentage: parseFloat(data.discount_percentage),
                    applicableProducts: data.applicable_products
                };

                cart.forEach(item => {
                    if (activeDiscount.applicableProducts.includes(item.id)) {
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
                    text: `${data.discount_percentage}% off applicable items`
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
    return "$" + num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&, ");
}

// Function to update display
function updateDisplay(subtotal, discountAmount = 0) {
    delivery = subtotal * 0.1;
    taxes = cart.reduce(
        (sum, item) => sum + (parseFloat(item.price) || 0) * (item.quantity || 0) * 0.05,
        0
    );

    $subtotal.innerText = formatDollar(subtotal);
    $delivery.innerText = formatDollar(delivery);
    $taxes.innerText = formatDollar(taxes);

    discountAmount = isNaN(discountAmount) ? 0 : discountAmount;
    $discount.innerText = discountAmount > 0 ? "- " + formatDollar(discountAmount) : "- $0.00";

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
    $cartItems.innerHTML = "";
    let total = 0;
    let itemCount = 0;
    let discountAmount = 0;

    const isDiscountApplied = document.getElementById("applyDiscountBtn").disabled;

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

    // Add event listeners to all "Add to Cart" buttons
    document.querySelectorAll(".add-to-cart").forEach(btn => {
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
            document.getElementById("modalImage").src = this.dataset.image;
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
    const button = $(event.relatedTarget);
    const modal = $(this);

    // Ambil data produk dari atribut data-*
    const userName = button.data("username");
    const userImage = button.data("userimage");
    const title = button.data("title");
    const price = button.data("price");
    const category = button.data("category");
    const description = button.data("description");
    const gender = button.data("gender");
    const image = button.data("image");
    const stock = button.data("stock");
    const rating = parseFloat(button.data("rating")) || 0;
    const ratingCount = button.data("ratingcount") || "";
    const ownerPhone = button.data("owner-phone") || "";

    // Ambil product_id dan user_id
    const productId = button.data("productid");
    const userId = button.data("userid");

    // Isi data produk di modal
    setModalText(modal, {
        userName,
        userImage,
        title,
        price,
        category,
        description,
        gender,
        image,
        stock,
        rating,
        ratingCount
    });
    // Set owner phone in modal
    const phoneSpan = document.getElementById("modalOwnerPhone");
    if (phoneSpan){ phoneSpan.textContent = ownerPhone;}

    // Set hidden input di form rating
    modal.find("input[name='product_id']").val(productId);
    modal.find("input[name='user_id']").val(userId); // Opsional, jika tidak pakai session

    // Ambil dan render komentar
    const reviewersJson = button.attr("data-reviewers");
    renderProductReviews(modal, reviewersJson);
}

function setModalText(modal, data) {
    modal.find("#modalUsername").text(data.userName);
    modal.find("#modalUserImage").attr("src", data.userImage);
    modal.find("#modalTitle").text(data.title);
    modal.find("#modalPrice").text(data.price);
    modal.find("#modalCategory").text(data.category);
    modal.find("#modalDescription").text(data.description);
    modal.find("#modalGender").text(data.gender);
    modal.find("#modalImage").attr("src", data.image);
    modal.find("#modalStock").text(data.stock);

    const starsHtml = generateStarsHtml(data.rating);
    modal.find(".rating-stars-static").html(starsHtml);

    modal
        .find("#modalRating")
        .text(`${data.rating} / 5 (${data.ratingCount} reviews)`);
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

function renderProductReviews(modal, reviewersJson) {
    const commentsContainer = modal.find("#modalCommentsContainer");
    commentsContainer.empty();

    let reviewers = [];
    try {
        reviewers = JSON.parse(reviewersJson);
    } catch (e) {
        console.error("Invalid reviewers JSON", e);
    }

    if (!reviewers.length) {
        commentsContainer.html('<p class="text-muted">Belum ada review</p>');
        return;
    }

    reviewers.forEach(reviewer => {
        const stars = generateStarsHtml(reviewer.rating || 0);
        const commentHtml = `
            <div class="d-flex align-items-start mb-3">
                <img src="${reviewer.userImage}" class="rounded-circle me-2" alt="User" width="40" height="40">
                <div>
                    <strong>${reviewer.username}</strong>
                    <div class="text-warning rating-stars-static">${stars}</div>
                    <p class="review-comment mb-0">${reviewer.review}</p>
                </div>
            </div>
        `;
        commentsContainer.append(commentHtml);
    });
}

// Pasang handler saat modal ditampilkan
$("#productModal").on("show.bs.modal", handleProductModalShow);

//input rating
$(document).ready(function () {
    // Fungsi handle modal produk (sudah kamu tulis dengan baik)
    $("#productModal").on("show.bs.modal", handleProductModalShow);

    // Handle klik bintang untuk input rating
    $("#starInput i").on("click", function () {
        const rating = $(this).data("value");
        $("#ratingValue").val(rating); // Set nilai ke hidden input
        updateStarUI(rating);
    });

    // Fungsi untuk update tampilan bintang saat diklik
    function updateStarUI(rating) {
        $("#starInput i").each(function () {
            const starValue = $(this).data("value");
            if (starValue <= rating) {
                $(this).removeClass("bi-star").addClass("bi-star-fill");
            } else {
                $(this).removeClass("bi-star-fill").addClass("bi-star");
            }
        });
    }

    // Optional: reset tampilan bintang dan form saat modal ditutup
    $("#productModal").on("hidden.bs.modal", function () {
        $("#ratingValue").val(""); // Reset hidden rating
        updateStarUI(0); // Reset tampilan bintang
        $("#ratingForm textarea[name='review_text']").val(""); // Kosongkan komentar
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


async function checkout() {
    if (!$agreeTerms.checked) {
        Swal.fire({
            icon: "warning",
            title: "Syarat & Ketentuan",
            text: "Harap setujui syarat & ketentuan terlebih dahulu.",
            confirmButtonColor: "#d33"
        });
        return;
    }

    const totalText = $total ? $total.innerText : '';
    const phone = document.getElementById('phoneInput')?.value.trim();
    const pass = document.getElementById('passwordInput')?.value.trim();

    if (!phone || !pass) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap',
            text: 'Nomor HP dan Password akun wajib diisi!',
            confirmButtonColor: "#f39c12"
        });
        return;
    }

    const transaksiId = "TRX" + Date.now();
    const tanggal = new Date().toLocaleString();

    const isDiscountApplied = document.getElementById("applyDiscountBtn")?.disabled;
    const discountAmount = isDiscountApplied
        ? cart.reduce((sum, item) => {
              const price = parseFloat(item.price) || 0;
              const quantity = item.quantity || 0;
              return sum + (item.discount_percentage ? price * quantity * (item.discount_percentage / 100) : 0);
          }, 0)
        : 0;

    Swal.fire({
        title: `Konfirmasi Pembelian`,
        html: `
            <p><b>Nomor HP:</b> ${phone}</p>
            <p><b>Total Bayar:</b> ${totalText}</p>
            ${isDiscountApplied ? `<p><b>Diskon:</b> -${formatDollar(discountAmount)}</p>` : ''}
            <p><b>Tanggal:</b> ${tanggal}</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: "#27ae60",
        cancelButtonColor: "#c0392b"
    }).then(async result => {
        if (result.isConfirmed) {
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // Header
                doc.setFontSize(18);
                doc.setFont("helvetica", "bold");
                doc.text("STRUK PEMBAYARAN", 105, 20, { align: "center" });

                doc.setFontSize(11);
                doc.setFont("helvetica", "normal");
                doc.text(`ID Transaksi: ${transaksiId}`, 20, 35);
                doc.text(`Nomor HP   : ${phone}`, 20, 42);
                doc.text(`Tanggal     : ${tanggal}`, 20, 49);

                // Data produk → tabel
                const tableData = cart.map(item => [
                    item.name,
                    item.quantity,
                    formatDollar(item.price),
                    formatDollar(item.price * item.quantity)
                ]);

                doc.autoTable({
                    startY: 60,
                    head: [["Produk", "Qty", "Harga", "Subtotal"]],
                    body: tableData,
                    styles: { fontSize: 10 },
                    headStyles: { fillColor: [39, 174, 96] }, // hijau
                });

                // Total
                let finalY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(13);
                doc.setFont("helvetica", "bold");
                doc.text(`TOTAL: ${totalText}`, 20, finalY);
                if (isDiscountApplied) {
                    doc.text(`DISKON: -${formatDollar(discountAmount)}`, 20, finalY + 7);
                }

                // Footer
                finalY += 20;
                doc.setFontSize(10);
                doc.setFont("helvetica", "italic");
                doc.text("Terima kasih telah berbelanja di PKK E-Commerce.", 105, finalY, { align: "center" });

                // Simpan PDF
                doc.save(`struk-${transaksiId}.pdf`);

                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil 🎉',
                    html: `Transaksi <b>${transaksiId}</b> berhasil.<br>Struk berhasil diunduh.`,
                    confirmButtonColor: "#27ae60"
                });
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membuat Struk',
                    text: err.message,
                    confirmButtonColor: "#c0392b"
                });
            }
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Transaksi Dibatalkan',
                text: 'Anda membatalkan proses pembayaran.',
                confirmButtonColor: "#3498db"
            });
        }
    });
}


