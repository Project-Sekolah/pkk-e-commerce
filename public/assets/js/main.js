// Cart logic with quantity
const cart = [];
const cartItemsElement = document.getElementById('cart-items');
const cartCountElement = document.getElementById('cart-count');
const totalPriceElement = document.getElementById('total-price');

//cart
function renderCartItems() {
    cartItemsElement.innerHTML = '';
    let total = 0;
    let itemCount = 0; // Track the total quantity of items in the cart
    cart.forEach((item, index) => {
        total += item.price * item.quantity; // Perhitungan total berdasarkan quantity
        itemCount += item.quantity; // Menambahkan jumlah kuantitas item

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
    cartCountElement.textContent = itemCount; // Update total quantity of items in cart
}

function updateItemQuantity(index, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(index);
    } else {
        cart[index].quantity = newQuantity;
        updateCart();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}



// Global filter criteria
const filters = {
    category: [], // Stores selected categories
    gender: []    // Stores selected genders
};

// Function to apply filters based on selected category and gender
function filterProducts() {
    const items = document.querySelectorAll('.product-item');
    
    items.forEach(item => {
        const category = item.getAttribute('data-category').toLowerCase();
        const gender = item.getAttribute('data-gender').toLowerCase();
        
        // Check if item matches all selected filters
        const isCategoryMatch = filters.category.length === 0 || filters.category.includes(category);
        const isGenderMatch = filters.gender.length === 0 || filters.gender.includes(gender);

        if (isCategoryMatch && isGenderMatch) {
            item.style.display = 'block'; // Show item if matches filters
        } else {
            item.style.display = 'none'; // Hide item if doesn't match filters
        }
    });
}

// Toggle category or gender filter
function toggleFilter(type, value) {
    const index = filters[type].indexOf(value);
    const filterButtons = document.querySelectorAll(`.${type}-filter`);

    if (index === -1) {
        // Add filter
        filters[type].push(value);
        // Add active class to the selected button
        toggleButtonActive(type, value, true);
    } else {
        // Remove filter
        filters[type].splice(index, 1);
        // Remove active class from the deselected button
        toggleButtonActive(type, value, false);
    }

    // Apply the filters after toggling
    filterProducts();
}

// Toggle active class on buttons
function toggleButtonActive(type, value, isActive) {
    const button = document.querySelector(`.${type}-filter[data-${type}="${value}"]`);
    if (isActive) {
        button.classList.add('active');
    } else {
        button.classList.remove('active');
    }
}

// Clear all filters (reset to "All")
function clearFilters() {
    filters.category = [];
    filters.gender = [];
    // Remove active class from all filter buttons
    document.querySelectorAll('.btn-outline-light').forEach(button => {
        button.classList.remove('active');
    });
    filterProducts(); // Apply "All" filter (show all items)
}

// Search products based on input
function searchProducts() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.product-item');
    
    items.forEach(item => {
        const title = item.querySelector('.card-title').textContent.toLowerCase();
        const description = item.querySelector('.card-text').textContent.toLowerCase();
        if (title.includes(searchInput) || description.includes(searchInput)) {
            item.style.display = 'block';
            highlightSearchTerm(item, searchInput);
        } else {
            item.style.display = 'none';
        }
    });
}

function highlightSearchTerm(item, searchTerm) {
    const title = item.querySelector('.card-title');
    const description = item.querySelector('.card-text');

    // Remove existing highlights
    title.innerHTML = title.textContent;
    description.innerHTML = description.textContent;

    // Highlight the search term in the title and description
    if (title.textContent.toLowerCase().includes(searchTerm)) {
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        title.innerHTML = title.textContent.replace(regex, '<span class="highlight">$1</span>');
    }
    if (description.textContent.toLowerCase().includes(searchTerm)) {
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        description.innerHTML = description.textContent.replace(regex, '<span class="highlight">$1</span>');
    }
}

   





//modal

// Set modal content dynamically
$('#productModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var title = button.data('title');
    var price = button.data('price');
    var category = button.data('category');
    var description = button.data('description');
    var gender = button.data('gender');
    var image = button.data('image');
    var stock = button.data('stock');

    var modal = $(this);
    modal.find('#modalTitle').text(title);
    modal.find('#modalPrice').text(price);
    modal.find('#modalCategory').text(category);
    modal.find('#modalDescription').text(description);
    modal.find('#modalGender').text(gender);
    modal.find('#modalImage').attr('src', image);
    modal.find('#modalStock').text(stock);
});

// Tambahkan event listener setelah DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Modal detail produk
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

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
            let productName, productPrice, productImage;

            if (btn.closest('.product-item')) {
                productName = btn.parentElement.querySelector('.card-title').textContent;
                productPrice = parseFloat(btn.parentElement.querySelector('.card-text').textContent.replace('$', ''));
                productImage = btn.closest('.product-item').querySelector('.product-img').src;
            }

            if (btn.closest('.modal-body')) {
                productName = document.getElementById('modalTitle').textContent;
                productPrice = parseFloat(document.getElementById('modalPrice').textContent);
                productImage = document.getElementById('modalImage').src;
            }

            if (productPrice && productName) {
                // Check if product already exists in cart
                const existingProduct = cart.find(item => item.name === productName);
                
                if (existingProduct) {
                    // Increase quantity if product exists
                    existingProduct.quantity += 1;
                } else {
                   
                                        // Tambahkan produk baru ke keranjang
                    cart.push({
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        quantity: 1
                    });
                }
                updateCart();
            }
        });
    });
});

function updateCart() {
    renderCartItems();
}
