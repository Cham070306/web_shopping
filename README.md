# 🛒 3legant — Web Shopping

E-commerce website built with **PHP + MySQL**, inspired by the **3legant furniture store UI**, supporting both customer shopping experience and admin management system.

---

## Author

**Hoàng Thị Ngọc Trâm**  
University of Transport Ho Chi Minh City (UTH)  
Major: Information Technology / Software Project

---

# Tech Stack

- PHP (Vanilla PHP, MVC-style structure)
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap
- Chart.js
- PHPMailer (OTP reset password)
- XAMPP (Apache + MySQL)
- Git + GitHub

---

# Features

---

## 🛍️ Storefront (Customer)

| Feature | Description |
|----------|-------------|
| Home Page | Hero slider, new arrivals, featured products, promotion banner, newsletter |
| Shop Page | Product grid/list view, category filter, price filter, sorting, pagination |
| Product Detail | Image gallery, product variants, countdown timer, quantity selector, add to cart |
| Wishlist | Save and manage favorite products |
| Reviews | Star rating and customer reviews |
| Shopping Cart | Add/remove/update product quantity |
| Checkout | Address selection and order placement |
| Order History | View previous orders |
| Order Complete | Confirmation after successful checkout |
| Account | Update profile information |
| Address Book | Add/edit/delete shipping addresses |
| Authentication | Register, Login, Logout |
| Forgot Password | Reset password via OTP email |
| Contact | Contact form submission |
| Blog | Blog list and blog detail pages |
| Newsletter | Email subscription system |

---

# 🔧 Admin Panel

> Access: `http://localhost/web_shopping/admin/login.php`

| Feature | Description |
|----------|-------------|
| Dashboard | Revenue statistics, order count, customer count, product count |
| Revenue Chart | 7 days / 1 month / 3 months analytics |
| Top Products | Best-selling product report |
| Products | Create, update, delete products |
| Categories | Manage product categories |
| Product Images | Upload and manage images |
| Product Variants | Manage color/size variants |
| Orders | View and update order status |
| Customers | View customer list and details |
| Coupons | Manage discount codes |
| Blog Posts | Manage blogs and post categories |
| Banners | Homepage banner management |
| Reports | Revenue and sales reports |
| Settings | Website settings configuration |

---

# Database Modules

---

## User Module
- users
- user_addresses
- password_reset_otps
- customer_notes
- logs

---

## Product Module
- categories
- products
- product_images
- product_variants
- inventory_logs
- wishlist
- reviews
- cart

---

## Order Module
- orders
- order_items
- order_status_logs
- payments
- coupons

---

## Content/System Module
- posts
- post_categories
- contacts
- banners
- notification_subscribers
- settings

---

# Getting Started

### 1. Clone repository

```bash
git clone https://github.com/yourusername/web_shopping.git
```

---

### 2. Move project to XAMPP

Place project inside:

```bash
xampp/htdocs/
```

---

### 3. Start XAMPP

Run:

- Apache
- MySQL

---

### 4. Create database

Open phpMyAdmin and create:

```bash
web_shopping
```

---

### 5. Import database

Import:

```bash
database.sql
```

---

### 6. Seed sample data

Import:

```bash
tools/sql/direct_seed.sql
```

---

### 7. Run project

Customer site:

```bash
http://localhost/web_shopping/user/index.php
```

Admin site:

```bash
http://localhost/web_shopping/admin/login.php
```

---

# Project Structure

| Folder | Purpose |
|----------|----------|
| `user/` | Customer shopping pages |
| `admin/` | Admin dashboard |
| `controllers/` | Business logic |
| `models/` | Database models |
| `config/` | Database configuration |
| `includes/` | Shared components |
| `assets/` | CSS, JS, images |
| `tools/sql/` | SQL seed files |

---

# Admin Accounts

> Admin Login: `http://localhost/web_shopping/admin/login.php`

⚠️ Only emails with domain **@3legant.com** and role = admin can access admin dashboard.

| Name | Email | Password |
|--------|---------|------------|
| 3legant Admin | admin@3legant.com | Admin@3legant |
| Super Admin | superadmin@3legant.com | Admin@123 |
| Store Manager | manager@3legant.com | Manager@456 |

---

# Test Customer Accounts

> Customer Login: `http://localhost/web_shopping/user/login.php`

| Name | Email | Password |
|--------|---------|------------|
| User Test | user@shop.com | User@123 |
| Nguyen Thi Anh | anh.nguyen@shop.com | User@123 |
| Tran Van Binh | binh.tran@shop.com | User@123 |
| Le Hoang Nam | nam.le@shop.com | User@123 |
| Pham Thu Ha | ha.pham@shop.com | User@123 |

---

# Git Workflow

Project follows team development workflow:

- `main` → Production
- `develop` → Development
- `test` → Testing
- `feature/*` → Feature branches

Example branches:

- feature/user-layout-home
- feature/auth-account-contact
- feature/shop-product-wishlist
- feature/cart-checkout-orders
- feature/blog-admin-report

---

# UML & System Design

This project includes:

- UML Class Diagram
- Database ERD
- Admin Workflow
- User Workflow
- Dashboard Reports

---

# Future Improvements

- Online payment integration
- AI product recommendations
- Multi-language support
- Live chat support
- Cloud deployment

---

# License

This project is developed for educational purposes.
