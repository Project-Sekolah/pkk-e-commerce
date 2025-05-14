// === Cart Logic ===
const cart = [];
const cartItemsElement = document.getElementById('cart-items');
const cartCountElement = document.getElementById('cart-count');
const totalPriceElement = document.getElementById('total-price');

function renderCartItems() {
    cartItemsElement.innerHTML = '';
    let total = 0;
    let itemCount = 0;

    cart.forEach((item, index) => {
        total += item.price * item.quantity;
        itemCount += item.quantity;

        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            ${item.name} - $${item.price.toFixed(2)} x 
            <input type="number" value="${item.quantity}" min="1" class="quantity-input" 
                   onchange="updateItemQuantity(${index}, this.value)">
            <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">Remove</button>
        `;
        cartItemsElement.appendChild(li);
    });

    totalPriceElement.textContent = total.toFixed(2);
    cartCountElement.textContent = itemCount;
}

function updateItemQuantity(index, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(index);
    } else {
        cart[index].quantity = parseInt(newQuantity);
        updateCart();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

function updateCart() {
    renderCartItems();
}



// === Filter Logic ===
const filters = {
    category: [],
    gender: []
};

function filterProducts() {
    const items = document.querySelectorAll('.product-item');
    items.forEach(item => {
        const category = item.getAttribute('data-category').toLowerCase();
        const gender = item.getAttribute('data-gender').toLowerCase();
        const matchCategory = filters.category.length === 0 || filters.category.includes(category);
        const matchGender = filters.gender.length === 0 || filters.gender.includes(gender);
        item.style.display = (matchCategory && matchGender) ? 'block' : 'none';
    });
}

function toggleFilter(type, value) {
    const index = filters[type].indexOf(value);
    index === -1 ? filters[type].push(value) : filters[type].splice(index, 1);
    toggleButtonActive(type, value, index === -1);
    filterProducts();
}

function toggleButtonActive(type, value, isActive) {
    const button = document.querySelector(`.${type}-filter[data-${type}="${value}"]`);
    button.classList.toggle('active', isActive);
}

function clearFilters() {
    filters.category = [];
    filters.gender = [];
    document.querySelectorAll('.btn-outline-light').forEach(btn => btn.classList.remove('active'));
    filterProducts();
}



// === Search Logic ===
function searchProducts() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.product-item');

    items.forEach(item => {
        const title = item.querySelector('.card-title').textContent.toLowerCase();
        const description = item.querySelector('.card-text').textContent.toLowerCase();
        const match = title.includes(searchInput) || description.includes(searchInput);

        item.style.display = match ? 'block' : 'none';
        if (match) highlightSearchTerm(item, searchInput);
    });
}

function highlightSearchTerm(item, term) {
    ['card-title', 'card-text'].forEach(cls => {
        const el = item.querySelector(`.${cls}`);
        el.innerHTML = el.textContent.replace(new RegExp(`(${term})`, 'gi'), '<span class="highlight">$1</span>');
    });
}



// === Modal Logic ===
$('#productModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const modal = $(this);
    modal.find('#modalTitle').text(button.data('title'));
    modal.find('#modalPrice').text(button.data('price'));
    modal.find('#modalCategory').text(button.data('category'));
    modal.find('#modalDescription').text(button.data('description'));
    modal.find('#modalGender').text(button.data('gender'));
    modal.find('#modalImage').attr('src', button.data('image'));
    modal.find('#modalStock').text(button.data('stock'));
});



// === 3D Card Hover Effect ===
document.querySelectorAll('.card-3d.interactive').forEach(card => {
    card.addEventListener('mousemove', e => {
        const img = card.querySelector('img');
        const rect = card.getBoundingClientRect();
        const rotateX = (rect.height / 2 - (e.clientY - rect.top)) / 10;
        const rotateY = ((e.clientX - rect.left) - rect.width / 2) / 10;
        img.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });

    card.addEventListener('mouseleave', () => {
        card.querySelector('img').style.transform = 'rotateX(0) rotateY(0)';
    });
});



// === DOM Ready Actions ===
document.addEventListener('DOMContentLoaded', () => {
    // Product Image Click - Modal Detail
    document.querySelectorAll('.product-img').forEach(img => {
        img.addEventListener('click', function () {
            document.getElementById('modalTitle').textContent = this.dataset.title;
            document.getElementById('modalPrice').textContent = this.dataset.price;
            document.getElementById('modalCategory').textContent = this.dataset.category;
            document.getElementById('modalGender').textContent = this.dataset.gender;
            document.getElementById('modalDescription').textContent = this.dataset.description || "No description available.";
            document.getElementById('modalStock').textContent = this.dataset.stock;
            document.getElementById('modalImage').src = this.dataset.image;
        });
    });

    // Add to Cart Button
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!isLoggedIn) {
                Swal.fire({
                    title: "Login Required",
                    text: "Please login to add items to the cart.",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
                return;
            }

            let productName, productPrice, productImage;
            if (btn.closest('.product-item')) {
                const parent = btn.parentElement;
                productName = parent.querySelector('.card-title').textContent;
                productPrice = parseFloat(parent.querySelector('.card-text').textContent.replace('$', ''));
                productImage = btn.closest('.product-item').querySelector('.product-img').src;
            }

            if (btn.closest('.modal-body')) {
                productName = document.getElementById('modalTitle').textContent;
                productPrice = parseFloat(document.getElementById('modalPrice').textContent);
                productImage = document.getElementById('modalImage').src;
            }

            if (productPrice && productName) {
                const existing = cart.find(item => item.name === productName);
                existing ? existing.quantity++ : cart.push({ name: productName, price: productPrice, image: productImage, quantity: 1 });
                updateCart();
            }
        });
    });
});



// === Auth Section Visibility ===
document.addEventListener("DOMContentLoaded", () => {
    const show = (id) => document.getElementById(id).style.display = "block";
    const hide = (id) => document.getElementById(id).style.display = "none";

    if (isLoggedIn) {
        hide("auth-section");
        show("user-info-section");
        document.getElementById("logout-link").style.display = "flex";
    } else {
        show("auth-section");
        hide("user-info-section");
        hide("logout-link");
    }
});




function showRegister() {
    document.getElementById("login-section").style.display = "none";
    document.getElementById("register-section").style.display = "block";
  }

  function showLogin() {
    document.getElementById("register-section").style.display = "none";
    document.getElementById("login-section").style.display = "block";
  }