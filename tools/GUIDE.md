# Tools & Database Guide — 3legant Web Shopping

---

## 🗄️ Các file SQL cần import

### 1. Setup lần đầu (bắt buộc)

| File | Vị trí | Mục đích |
|------|--------|---------|
| `database.sql` | `/database.sql` (root project) | Schema đầy đủ + toàn bộ seed data — **import file này trước tiên** |

**Cách import:**
```
phpMyAdmin → Import → Chọn database.sql → Go
```
Hoặc dùng CLI:
```bash
mysql -u root web_shopping < database.sql
```

> ✅ File này bao gồm: tạo DB, tất cả bảng, dữ liệu mẫu (users, products, orders, coupons...) và tài khoản admin.

---

### 2. Tài khoản có sẵn sau khi import

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@3legant.com` | `Admin@3legant` |
| User test | `user@shop.com` | `User@123` |

---

### 3. SQL hỗ trợ crawler (trong `tools/sql/`)

| File | Mục đích |
|------|---------|
| `truncate.sql` | Xóa sạch bảng products, product_images, product_variants trước khi import batch mới |
| `seed_categories.sql` | Tạo lại 7 categories chuẩn nếu bị mất |
| `direct_seed.sql` | *(Auto-generated)* Output của crawler — import sản phẩm mới từ Tiki |

---

## 🕷️ Tiki Crawler

### Yêu cầu
```bash
Python 3.8+   # không cần cài thêm thư viện
```

### Chạy crawler
```bash
cd tools
python tiki_crawler.py
```
→ Tự tạo file `tools/sql/direct_seed.sql`

### Import sản phẩm mới
```
1. phpMyAdmin → web_shopping → Import → tools/sql/truncate.sql
2. phpMyAdmin → web_shopping → Import → tools/sql/direct_seed.sql
```

---

## 📦 Dữ liệu crawl được

Mỗi sản phẩm thu thập:

| Field | Mô tả |
|-------|-------|
| `name` | Tên sản phẩm |
| `slug` | URL slug (từ tên + Tiki ID) |
| `sku` | Mã dạng `TK-{tiki_id}` |
| `price` | Giá gốc |
| `sale_price` | Giá khuyến mãi (nếu có) |
| `description` | Mô tả đầy đủ (đã strip HTML) |
| `thumbnail` | URL ảnh đại diện |
| `brand` | Thương hiệu |
| `sold` | Số đã bán |
| Gallery | Tối đa 10 ảnh/sản phẩm → `product_images` |
| Variants | Tự động → `product_variants` |

> `is_featured = 1` nếu rating ≥ 4.5★ và ≥ 50 reviews

---

## ⚙️ Tuỳ chỉnh crawler

Mở `tiki_crawler.py`, chỉnh phần **CONFIG**:

```python
LIMIT_PER_QUERY = 6      # Số sản phẩm lấy mỗi từ khóa
REQUEST_DELAY   = 0.4    # Giây delay giữa request
STOCK_DEFAULT   = 100    # Stock mặc định
```

Thêm/bớt từ khóa trong **CATEGORY_QUERIES** để crawl danh mục khác.
