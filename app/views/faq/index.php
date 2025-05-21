<!-- faq -->
<div class="container py-1 faq-container">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
          
          <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4 feedback-card">
                    <div class="card-body p-4">
                        <h3 class="card-title feedback-title">Send Us Your Feedback</h3>
                        <form action="https://formspree.io/f/xgvkreeb" method="POST">
                            <div class="mb-3">
                                <label for="nameInput" class="form-label feedback-label">Your Name</label>
                                <input type="text" name="name" class="form-control feedback-input" id="nameInput" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailInput" class="form-label feedback-label">Your Email</label>
                                <input type="email" name="email" class="form-control feedback-input" id="emailInput" required>
                            </div>
                            <div class="mb-3">
                                <label for="messageInput" class="form-label feedback-label">Message</label>
                                <textarea name="message" class="form-control feedback-textarea" id="messageInput" rows="4" required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary feedback-btn">Send Feedback</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
          
            <div class="btn-group" role="group" aria-label="Language Toggle">
                <button class="btn btn-outline-primary lang-btn me-2" onclick="toggleLanguage('en')">
                    <i class="bi bi-globe"></i> English
                </button>
                <button class="btn btn-outline-success lang-btn" onclick="toggleLanguage('id')">
                    <i class="bi bi-globe-americas"></i> Indonesia
                </button>
            </div>
        </div>

        <div class="text-center mb-5">
            <h2 id="faqTitle" class="fw-bold faq-title">Frequently Asked Questions</h2>
             <p id="faqDescription" class="text-muted faq-description">Find quick answers to common questions below.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion shadow rounded" id="faqAccordion">

                    <!-- FAQ 1 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqOneHeading">
                            <button class="accordion-button fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="true">
                                <span id="faqOneTitle">What is this website for?</span>
                            </button>
                        </h2>
                        <div id="faqOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqOneAnswer">This website is an online store where users can browse and purchase products conveniently.</span>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqTwoHeading">
                            <button class="accordion-button collapsed fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false">
                                <span id="faqTwoTitle">How do I add items to my cart?</span>
                            </button>
                        </h2>
                        <div id="faqTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqTwoAnswer">Simply click the “Add to Cart” button under any product, and it will appear in your shopping cart.</span>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqThreeHeading">
                            <button class="accordion-button collapsed fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false">
                                <span id="faqThreeTitle">How can I contact customer support?</span>
                            </button>
                        </h2>
                        <div id="faqThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqThreeAnswer">Use the feedback form below or email us directly at support@example.com.</span>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqFourHeading">
                            <button class="accordion-button collapsed fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false">
                                <span id="faqFourTitle">How can I track my order?</span>
                            </button>
                        </h2>
                        <div id="faqFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqFourAnswer">After placing an order, you'll receive a tracking number via email. You can use it to track your order status on the carrier's website.</span>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqFiveHeading">
                            <button class="accordion-button collapsed fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqFive" aria-expanded="false">
                                <span id="faqFiveTitle">Do you offer international shipping?</span>
                            </button>
                        </h2>
                        <div id="faqFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqFiveAnswer">Yes, we offer international shipping to selected countries. Please check our shipping policy for more details.</span>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqSixHeading">
                            <button class="accordion-button collapsed fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqSix" aria-expanded="false">
                                <span id="faqSixTitle">Can I cancel my order?</span>
                            </button>
                        </h2>
                        <div id="faqSix" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqSixAnswer">Orders can only be canceled if they haven't been processed yet. Please contact us as soon as possible if you'd like to cancel an order.</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 7 -->
                    <div class="accordion-item faq-item">
                        <h2 class="accordion-header" id="faqSevenHeading">
                            <button class="accordion-button collapsed fw-semibold faq-header-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqSeven" aria-expanded="false">
                                <span id="faqSevenTitle">How to register?</span>
                            </button>
                        </h2>
                        <div id="faqSeven" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body faq-body">
                                <span id="faqSevenAnswer">Click the Three Lines Button on the Top Left, if you already have an account, enter your email and password, if you already have one, click login, if you don't have an account, click "Don't have an account? Register"</span>

                </div>
            </div>
        </div>


        
    </div>
</div>
        </div>
        </div>
        </div>

<script>
    function toggleLanguage(lang) {
        if (lang === 'en') {
            document.getElementById('faqTitle').innerText = "Frequently Asked Questions";
            document.getElementById('faqDescription').innerText = "Find quick answers to common questions below.";
            document.getElementById('faqOneTitle').innerText = "What is this website for?";
            document.getElementById('faqTwoTitle').innerText = "How do I add items to my cart?";
            document.getElementById('faqThreeTitle').innerText = "How can I contact customer support?";
            document.getElementById('faqFourTitle').innerText = "How can I track my order?";
            document.getElementById('faqFiveTitle').innerText = "Do you offer international shipping?";
            document.getElementById('faqSixTitle').innerText = "Can I cancel my order?";
            document.getElementById('faqOneAnswer').innerText = "This website is an online store where users can browse and purchase products conveniently.";
            document.getElementById('faqTwoAnswer').innerText = "Simply click the “Add to Cart” button under any product, and it will appear in your shopping cart.";
            document.getElementById('faqThreeAnswer').innerText = "Use the feedback form below or email us directly at support@example.com.";
            document.getElementById('faqFourAnswer').innerText = "After placing an order, you'll receive a tracking number via email. You can use it to track your order status on the carrier's website.";
            document.getElementById('faqFiveAnswer').innerText = "Yes, we offer international shipping to selected countries. Please check our shipping policy for more details.";
            document.getElementById('faqSixAnswer').innerText = "Orders can only be canceled if they haven't been processed yet. Please contact us as soon as possible if you'd like to cancel an order.";
        } else if (lang === 'id') {
            document.getElementById('faqTitle').innerText = "Pertanyaan yang Sering Diajukan";
            document.getElementById('faqDescription').innerText = "Temukan jawaban cepat untuk pertanyaan umum di bawah ini.";
            document.getElementById('faqOneTitle').innerText = "Apa tujuan website ini?";
            document.getElementById('faqTwoTitle').innerText = "Bagaimana cara menambahkan barang ke keranjang?";
            document.getElementById('faqThreeTitle').innerText = "Bagaimana cara menghubungi dukungan pelanggan?";
            document.getElementById('faqFourTitle').innerText = "Bagaimana cara melacak pesanan saya?";
            document.getElementById('faqFiveTitle').innerText = "Apakah Anda menawarkan pengiriman internasional?";
            document.getElementById('faqSixTitle').innerText = "Bisakah saya membatalkan pesanan saya?";
            document.getElementById('faqSevenTitle').innerText = "Bagaiamana Cara Untuk Mendaftar?";
            document.getElementById('faqOneAnswer').innerText = "Website ini adalah toko online di mana pengguna dapat menelusuri dan membeli produk dengan mudah.";
            document.getElementById('faqTwoAnswer').innerText = "Cukup klik tombol “Tambah ke Keranjang” di bawah produk, dan produk tersebut akan muncul di keranjang belanja Anda.";
            document.getElementById('faqThreeAnswer').innerText = "Gunakan formulir umpan balik di bawah atau email kami langsung di support@example.com.";
            document.getElementById('faqFourAnswer').innerText = "Setelah melakukan pemesanan, Anda akan menerima nomor pelacakan melalui email. Anda dapat menggunakannya untuk melacak status pesanan di situs web pengiriman.";
            document.getElementById('faqFiveAnswer').innerText = "Ya, kami menawarkan pengiriman internasional ke negara-negara tertentu. Silakan periksa kebijakan pengiriman kami untuk detail lebih lanjut.";
            document.getElementById('faqSixAnswer').innerText = "Pesanan hanya dapat dibatalkan jika belum diproses. Harap hubungi kami secepatnya jika Anda ingin membatalkan pesanan.";
            document.getElementById
            ('faqSevenAnswer'
            ).innerText = "Klik Tombol Tiga Garis di Kiri Atas, jika sudah mempunyai akun masukkan email dan password, jika sudah punya klik login, jika belum punya akun klik Tidak punya akun";
        }
    }
</script>