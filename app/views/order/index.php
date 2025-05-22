
    <style>
        :root {
            --primary: #3a3a3a;
            --bg: #f5f5f5;
            --text: #2c2c2c;
            --accent: #a89f91;
            --btn-bg: #5a5145;
            --btn-text: #fff;
            --radius: 10px;
        }

        .order{
          padding-top: 60px;
        }

        .order .summary-box {
            background: #fff;
            color: var(--text);
            padding: 24px;
            border-radius: var(--radius);
            width: 340px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.4s ease-out;
            border: 1px solid #ddd;
        }

        .order .summary-box h2 {
            margin-bottom: 18px;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .order .line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 0.95rem;
            color: #4b4b4b;
        }

        .order .line.total {
            font-weight: bold;
            font-size: 1rem;
            margin-top: 8px;
            color: var(--primary);
        }

        .order .btn {
            display: block;
            width: 100%;
            padding: 14px 0;
            margin-top: 14px;
            border: none;
            border-radius: 50px;
            background: var(--btn-bg);
            color: var(--btn-text);
            font-weight: bold;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .order .btn:hover {
            background: #403a32;
        }

        .order label {
            font-size: 0.85rem;
            display: block;
            margin-top: 16px;
            color: #5a5145;
        }

        .order input[type="number"] {
            width: 100%;
            padding: 8px;
            border-radius: var(--radius);
            border: 1px solid #ccc;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .order a {
            color: var(--accent);
            text-decoration: none;
        }

        .order a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    
    <div class="container order my-5">
  <h4 id="produk" class="mb-4">Order Items</h4>
    <div class="row g-3 mb-4">
       <div class="col-12 col-md-6">
         <div class="container summary-box">
        <h2>Summary</h2>
        
        <div class="line">
            <span>Subtotal</span>
            <span id="subtotal">Rp 0</span>
        </div>

        <div class="line">
            <span>Estimated Delivery & Handling</span>
            <span id="delivery">Rp 0</span>
        </div>

        <div class="line">
            <span>Estimated Duties and Taxes</span>
            <span id="taxes">Rp 0</span>
        </div>

        <label for="discountInput">Masukkan Diskon</label>
        <input type="number" id="discountInput" placeholder="Kode diskon" />

        <div class="line">
            <span>Diskon</span>
            <span id="discount">- Rp 0</span>
        </div>

        <div class="line total">
            <span>Total</span>
            <span id="total">Rp 0</span>
        </div>

        <label>
            <input type="checkbox" id="agreeTerms" />
            Saya menyetujui <a href="#">syarat & ketentuan</a>
        </label>

        <button class="btn" onclick="checkout('guest')">Checkout</button>
    </div>
         </div>
    </div>
  </div>

    

    <script>
        async function loadCart() {
            try {
                const response = await fetch(`${BASEURL}/Cart/getCart`, {
                    method: 'GET',
                    credentials: 'include'
                });
                const data = await response.json();
                
                if (!data.items) return;

                const subtotalEl = document.getElementById('subtotal');
                const deliveryEl = document.getElementById('delivery');
                const taxesEl = document.getElementById('taxes');
                const discountEl = document.getElementById('discount');
                const totalEl = document.getElementById('total');
                
                let subtotal = 0;
                data.items.forEach(item => {
                    subtotal += item.price * item.quantity;
                });

                subtotalEl.textContent = `Rp ${subtotal.toLocaleString()}`;
                deliveryEl.textContent = `Rp ${subtotal * 0.1}`;
                taxesEl.textContent = `Rp ${subtotal * 0.05}`;
                
                const discountCode = document.getElementById('discountInput').value;
                const discount = discountCode ? subtotal * 0.15 : 0;
                discountEl.textContent = `- Rp ${discount.toLocaleString()}`;
                
                const total = subtotal + (subtotal*0.1) + (subtotal*0.05) - discount;
                totalEl.textContent = `Rp ${total.toLocaleString()}`;
            } catch (error) {
                console.error('Error loading cart:', error);
            }
        }

        async function checkout(type) {
            const response = await fetch(`${BASEURL}/order/checkout`, {
                method: 'POST',
                credentials: 'include'
            });
            const result = await response.json();
            
            if (result.success) {
                alert(`Order berhasil! Nomor Order: ${result.order_id}`);
                window.location.reload();
            } else {
                alert(result.error);
            }
        }

        // Load cart on page load
        document.addEventListener('DOMContentLoaded', loadCart);
    </script>