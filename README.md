# 3legant — Web Shopping

E-commerce storefront built with PHP + MySQL.

## Tech Stack

- PHP (vanilla, MVC-style)
- MySQL
- HTML / CSS / JavaScript
- XAMPP (Apache + MySQL)
- Git + GitHub

## Features

### 🛍️ Storefront (Customer)

| Feature | Description |
|---|---|
| Home Page | Hero slider, new arrivals, featured products, promo section, newsletter |
| Shop Page | Product grid/list view, sidebar filter by category & price, sort by price/name, pagination |
| Product Detail | Image gallery with thumbnails, countdown timer (sale products), color/variant selector, quantity input, add to cart, wishlist toggle, reviews & ratings |
| Shopping Cart | Add / remove / update quantity, real-time subtotal |
| Checkout | Address selection, place order |
| Order History | View all past orders (`my_orders.php`) |
| Order Complete | Confirmation page after successful purchase |
| Wishlist | Save & manage favourite products |
| Account | View & update profile info, change password |
| Address Book | Add / edit / delete shipping & billing addresses |
| Authentication | Register, Login, Logout, Forgot Password, Reset via OTP code |
| Contact | Contact form with message submission |
| Blog | Blog listing & blog detail pages |

### 🔧 Admin Panel

> Access: `http://localhost/web_shopping/admin/login.php`

| Feature | Description |
|---|---|
| Dashboard | Overview stats (revenue, orders, customers, products) |
| Products | List, create, edit, delete products with image upload |
| Categories | List, create, edit, delete categories |
| Orders | View & manage all orders, update order status |
| Customers | View all registered customers |

## Getting Started

1. Clone the repo into your `htdocs` folder
2. Start **Apache** and **MySQL** in XAMPP
3. Open **phpMyAdmin** and create a database named `web_shopping`
4. Import `database.sql` to create all tables and initial admin/user accounts
5. Import `tools/sql/direct_seed.sql` to seed categories & products
6. Visit `http://localhost/web_shopping/user/index.php`

## Project Structure

| Folder | Purpose |
|---|---|
| `user/` | Storefront pages (shop, cart, checkout, account) |
| `admin/` | Admin dashboard (products, orders, customers) |
| `controllers/` | Business logic & form handlers |
| `models/` | Database query classes |
| `config/` | DB connection & app config |
| `includes/` | Shared partials (header, footer, navbar, newsletter) |
| `assets/` | CSS, JS, images |
| `tools/sql/` | Seed SQL scripts |

## Admin Accounts

> Admin login: `http://localhost/web_shopping/admin/login.php`
>
> ⚠️ Only emails `@3legant.com` with `role = admin` can access the admin panel.

| Name | Email | Password |
|---|---|---|
| 3legant Admin | admin@3legant.com | Admin@3legant |
| Super Admin | superadmin@3legant.com | Admin@123 |
| Store Manager | manager@3legant.com | Manager@456 |

## Test Customer Accounts

> Login: `http://localhost/web_shopping/user/login.php`

| Name | Email | Password |
|---|---|---|
| User Test | user@shop.com | User@123 |
| Nguyen Thi Anh | anh.nguyen@shop.com | User@123 |
| Tran Van Binh | binh.tran@shop.com | User@123 |
| Le Hoang Nam | nam.le@shop.com | User@123 |
| Pham Thu Ha | ha.pham@shop.com | User@123 |
