  // Variabel untuk menyimpan data cart
  const cart = [];
  const cartItemsElement = document.getElementById('cart-items');
  const cartCountElement = document.getElementById('cart-count');
  const totalPriceElement = document.getElementById('total-price');

  // Fungsi render isi keranjang
  function renderCartItems() {
    cartItemsElement.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
      total += item.price;
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center';
      li.innerHTML = `
        ${item.name} - $${item.price.toFixed(2)}
        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">Remove</button>
      `;
      cartItemsElement.appendChild(li);
    });

    totalPriceElement.textContent = total.toFixed(2);
    cartCountElement.textContent = cart.length;
  }

  // Fungsi update cart
  function updateCart() {
    renderCartItems();
  }

  // Fungsi hapus item dari cart
  function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
  }

  // Tambahkan event listener ke semua tombol "Add to Cart"
  document.querySelectorAll('.add-to-cart').forEach((btn) => {
    btn.addEventListener('click', () => {
      const productName = btn.parentElement.querySelector('.card-title').textContent;
      const productPrice = parseFloat(btn.parentElement.querySelector('.card-text').textContent.replace('$', ''));
      cart.push({ name: productName, price: productPrice });
      updateCart();
    });
  });
