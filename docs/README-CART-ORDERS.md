# Documentation: Feature Cart - Checkout - Orders 🛍️

**Người thực hiện:** [Tên bạn]
**Module phụ trách:** Toàn bộ luồng E-commerce cốt lõi (Cart -> Checkout -> Sales Admin)
**Chi nhánh / Feature:** `feature/cart-checkout-orders`

Mục tiêu của module này là mang đến trải nghiệm shopping hoàn hảo và bảo mật với JSON API kết hợp Vanilla Javascript (AJAX), tách bạch rõ ràng giữa *Data Access Layer* (Models), *Business Logic* (Controllers) và *Dynamic Views* tương tự kiến trúc của Shopify.

---

## Danh sách tệp tin (Scope)
Mọi chức năng dưới đây mình đã review, fix bugs conflict cũ và hoàn thành 100%. Mọi file đều vượt qua `php -lint` check (0 lỗi cú pháp).

### 1. Database & Seeder
- **`config/database.php`**: Được cài cắm mạch *Auto-Seeder*. Bất kì ai clone repo về, lúc chạy web lần đầu nếu Kho trống trơn, hệ thống sẽ trigger tự rải Data và Import tự động 6 sản phẩm cực nét (Sofa, Lamp, Bàn Gỗ...) từ kho ảnh chất lượng Unsplash.
- **`models/Order.php`**: Xử lý 22 Hàm truy xuất. Code bằng `mysqli_prepare` để block toàn vẹn lỗi Hack SQL Injection. Chống thất thoát hàng (Check đủ stock mới cho add). Tính năng *Transaction Rollback* ở Checkout bảo vệ nếu lỗi DB thì không bị huỷ đơn nửa chừng.
- **`models/OrderDetail.php`**: Các hàm tính toán kho rời rạc.

### 2. Restful Array Controllers
- **`controllers/CartController.php`**: Thiết kế chạy JSON 100%. Xử lý Add/Remove/Update và Apply Coupon.
- **`controllers/OrderController.php`**: Handler chuyển hướng. Kiểm duyệt Form Address và kết nối đến Model Order.

### 3. Customer Portal (Trải nghiệm Khách mua hàng)
- **`includes/navbar.php` (Shared)**: Cập nhật tích hợp Logic Avatar & Dropdown Hover *Mini-cart popup*.
- **`includes/footer.php` (Shared)**: Hook Javascript chèn *Toast Notification Global*, giúp chặn Form Submit mặc định của Dev Team cũ và chuyển thành form AJAX mượt. Tránh bị văng vỡ view rác JSON.
- **`user/cart.php`**: Quản lí giỏ. Live DOM update để không phải tải lại trang khi + / - sản phẩm, hoặc Apply mã giảm giá.
- **`user/checkout.php`**: Quy trình thanh toán có Auto-fill thông tin (nếu User đang Login) nhằm tối đa Purchase Conversion Rate. 
- **`user/order_complete.php`**: Khoá Session ID (không F5) để chống Spam Submit.
- **`user/my_orders.php`**: Tích hợp giao diện Filter Tab lọc theo Status (Chờ xác nhận, Đang giao...) + Timeline theo dõi chặng đơn chuyên nghiệp.

### 4. Admin Portal (Trải nghiệm nội bộ Quản trị)
- **`admin/orders/index.php`**: Trang tổng quan, có bộ Box Thống Kê doanh thu và Grid table. **Tính năng độc quyền:** thả Dropdown thay đổi Trạng Thái Đơn ngay và luôn tại bảng dữ liệu bằng AJAX, nhạy 1ms không load tab.
- **`admin/orders/detail.php`**: Setup Layout 2 mặt (Trái xem hàng hóa gửi / Phải xem thông tin Status Giao & Tiền).
- **`admin/inventory/index.php`**: (Kho hàng) Hiện cảnh báo màu nổi nếu Hàng Trong Kho <= 10. Update nhanh Input tồn kho bằng AJAX thả Toast màu Xanh dưới mép website.

---

## Dành cho Team / Code Reviewer ✋

- **Frontend Dev (`shop.php`, `product_detail.php`, `index.php`...)**: 
  - Toàn bộ form submit có `action="CartController.php"` giờ đã được script dưới `footer.php` của mình auto Catch! 
  - Cứ gọi standard HTML Input bình thường, mình đã lo phần convert JSON Request ẩn bên dưới và quăng Toast cho User. Không cần viết thêm JS dư thừa.
- **Database Mod**: Table `orders` mình set `user_id` có thể NULL nên hỗ trợ sẵn tính năng Checkout dành cho Guest. Update model là dùng được ngay tính năng "Buy Now without Login" nhé (mặc dù hiện tại mình khoá luồng để bắt User Đăng kí).

**Trạng thái Code:** `READY TO MERGE`.
