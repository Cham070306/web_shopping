<footer class="footer">
    <div class="container">

        <div class="footer-top">

            <div class="footer-left">
                <h3>3legant.</h3>

                <div class="logo-line"></div>

                <span class="gray">Gift & Decoration Store</span>
            </div>

            <div class="footer-menu">
                <a href="index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="shop.php">Product</a>
                <a href="blog.php">Blog</a>
                <a href="contact.php">Contact Us</a>
            </div>

        </div>

        <hr class="footer-line mobile-hide">

        <div class="footer-bottom">

            <div class="footer-text">
                Copyright © 2026 3legant. All rights reserved
            </div>

            <div class="footer-links">
                <a href="#" class="footer-link">Privacy Policy</a>
                <a href="#" class="footer-link">Terms of Use</a>
            </div>

            <div class="footer-icons">
                <i class="fa-brands fa-instagram"></i>
                <i class="fa-brands fa-facebook"></i>
                <i class="fa-brands fa-youtube"></i>
            </div>

        </div>

    </div>
</footer>

<script>
// Tự động chặn các form Add to Cart cũ (submit POST bị redirect) 
// và chuyển đổi thành AJAX ngầm chặn lỗi trả về chuỗi JSON.
document.addEventListener('DOMContentLoaded', function() {
    const cartForms = document.querySelectorAll('form[action*="CartController.php"]');
    cartForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Collect Form data sang dạng Object (JSON)
            const fd = new FormData(this);
            const payload = {};
            fd.forEach((value, key) => payload[key] = value);
            
            const formActionUrl = this.getAttribute('action') || '';
            
            // Xử lý action
            if (!payload.action) {
                const urlParams = new URLSearchParams(formActionUrl.split('?')[1] || '');
                payload.action = urlParams.get('action') || 'add';
            }

            try {
                // Submit bằng fetch ẩn
                const destination = formActionUrl.includes('?') ? formActionUrl : formActionUrl + '?action=' + payload.action;
                const res = await fetch(destination, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.require_login) {
                    showGlobalToast(data.message || 'Vui lòng đăng nhập để thêm vào giỏ.', 'error');
                    setTimeout(() => window.location.href = 'login.php', 1500);
                    return;
                }
                
                if (data.success) {
                    const badges = document.querySelectorAll('#cart-count');
                    badges.forEach(b => b.textContent = data.cart_count);
                    showGlobalToast(data.message || 'Thêm vào giỏ hàng thành công!', 'success');
                } else {
                    showGlobalToast(data.message || 'Không thể thêm vào giỏ.', 'error');
                }
            } catch (err) {
                console.error(err);
                showGlobalToast('Lỗi kết nối Server.', 'error');
            }
        });
    });
});

function showGlobalToast(msg, type = 'success') {
    let toast = document.getElementById('global-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'global-toast';
        document.body.appendChild(toast);
        
        // Inject styles once
        const style = document.createElement('style');
        style.innerHTML = `
            #global-toast {
                position: fixed; z-index: 9999; left: 50%; bottom: 40px; transform: translateX(-50%) translateY(100px);
                background: #141718; color: #fff; padding: 14px 24px; border-radius: 8px;
                font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;
                display: flex; align-items: center; gap: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                opacity: 0; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                visibility: hidden;
            }
            #global-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; visibility: visible; }
            #global-toast.error { background: #FF5630; }
        `;
        document.head.appendChild(style);
    }
    
    toast.className = 'show ' + (type === 'error' ? 'error' : '');
    toast.innerHTML = (type === 'success' ? '<i class="fa-solid fa-check-circle" style="color:#38CB89; font-size: 18px;"></i>' : '<i class="fa-solid fa-xmark-circle" style="font-size: 18px;"></i>') + msg;
    
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}
</script>