<?php
// Tự động seed dữ liệu mẫu với hình ảnh cao cấp
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE products");
$conn->query("TRUNCATE TABLE categories");
$conn->query("TRUNCATE TABLE product_images");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$categories = [
    ['Living Room', 'living-room', 'Phòng khách'],
    ['Bedroom', 'bedroom', 'Phòng ngủ'],
    ['Kitchen', 'kitchen', 'Nhà bếp'],
    ['Decor', 'decor', 'Trang trí']
];

foreach ($categories as $c) {
    $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $c[0], $c[1], $c[2]);
    $stmt->execute();
}

$cLiving = $conn->query("SELECT id FROM categories WHERE slug='living-room'")->fetch_assoc()['id'];
$cBed = $conn->query("SELECT id FROM categories WHERE slug='bedroom'")->fetch_assoc()['id'];
$cKit = $conn->query("SELECT id FROM categories WHERE slug='kitchen'")->fetch_assoc()['id'];
$cDec = $conn->query("SELECT id FROM categories WHERE slug='decor'")->fetch_assoc()['id'];

$products = [
    [
        'category_id' => $cLiving,
        'name' => 'Loveseat Sofa',
        'slug' => 'loveseat-sofa',
        'price' => 2000000,
        'sale_price' => 1800000,
        'stock' => 15,
        'sku' => 'SOFA-01',
        'thumbnail' => 'https://images.unsplash.com/photo-1550254478-ead40cc54513?q=80&w=600&auto=format&fit=crop',
        'short_desc' => 'Sofa phong cách tối giản',
        'is_featured' => 1
    ],
    [
        'category_id' => $cLiving,
        'name' => 'Luxury Wooden Table',
        'slug' => 'wooden-table-lux',
        'price' => 1500000,
        'sale_price' => 0,
        'stock' => 10,
        'sku' => 'TB-02',
        'thumbnail' => 'https://images.unsplash.com/photo-1577140917170-285929fb55b7?q=80&w=600&auto=format&fit=crop',
        'short_desc' => 'Bàn gỗ tự nhiên nguyên khối cao cấp',
        'is_featured' => 0
    ],
    [
        'category_id' => $cBed,
        'name' => 'Table Lamp',
        'slug' => 'table-lamp-01',
        'price' => 450000,
        'sale_price' => 380000,
        'stock' => 50,
        'sku' => 'LMP-03',
        'thumbnail' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?q=80&w=600&auto=format&fit=crop',
        'short_desc' => 'Đèn ngủ ánh sáng vàng',
        'is_featured' => 1
    ],
    [
        'category_id' => $cBed,
        'name' => 'White Queen Bed',
        'slug' => 'queen-bed-w',
        'price' => 5000000,
        'sale_price' => 4800000,
        'stock' => 5,
        'sku' => 'BED-04',
        'thumbnail' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?q=80&w=600&auto=format&fit=crop',
        'short_desc' => 'Giường Queen Size màu trắng',
        'is_featured' => 1
    ],
    [
        'category_id' => $cLiving,
        'name' => 'Armchair Tối Giản',
        'slug' => 'armchair-min',
        'price' => 1200000,
        'sale_price' => 890000,
        'stock' => 8,
        'sku' => 'CHR-05',
        'thumbnail' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?q=80&w=600&auto=format&fit=crop',
        'short_desc' => 'Ghế Armchair bọc nỉ êm ái',
        'is_featured' => 0
    ],
    [
        'category_id' => $cDec,
        'name' => 'Tranh Treo Tường',
        'slug' => 'wall-art-decor',
        'price' => 350000,
        'sale_price' => 0,
        'stock' => 20,
        'sku' => 'ART-06',
        'thumbnail' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?q=80&w=600&auto=format&fit=crop',
        'short_desc' => 'Tranh decor phong cách Châu Âu',
        'is_featured' => 0
    ]
];

foreach ($products as $p) {
    // Insert Product
    $stmt = $conn->prepare("INSERT INTO products (category_id, name, slug, price, sale_price, stock, sku, thumbnail, short_desc, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("issddisssi", $p['category_id'], $p['name'], $p['slug'], $p['price'], $p['sale_price'], $p['stock'], $p['sku'], $p['thumbnail'], $p['short_desc'], $p['is_featured']);
    $stmt->execute();
    $pid = $stmt->insert_id;
    
    // Thêm các ảnh gallery dummy cho đẹp
    $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, 1)");
    $imgStmt->bind_param("is", $pid, $p['thumbnail']);
    $imgStmt->execute();
}
