"""
tiki_crawler.py — 3legant Web Shopping
========================================
Crawl dữ liệu sản phẩm nội thất từ Tiki API.
Output: direct_seed.sql — import thẳng vào MySQL

Dữ liệu thu thập:
  - name, slug, sku
  - price, sale_price (nếu có khuyến mãi)
  - rating_average, review_count
  - description (full, đã strip HTML)
  - short_desc (excerpt ngắn gọn)
  - thumbnail (URL ảnh đại diện)
  - gallery images → product_images
  - brand, material, color (nếu có)
  - product_variants mặc định

Schema target:
  products(id, category_id, name, slug, sku, description, short_desc,
           price, sale_price, stock, sold, thumbnail, brand, material,
           color, meta_title, meta_description, is_featured, is_active)
  product_images(product_id, image_url, sort_order)
  product_variants(product_id, color, size, stock, price_diff)

Cách chạy:
  pip install requests   (hoặc dùng stdlib urllib, đã tích hợp sẵn)
  python tiki_crawler.py

Sau đó import direct_seed.sql vào phpMyAdmin / MySQL CLI.

Lưu ý: Chạy truncate.sql TRƯỚC nếu muốn reset sạch dữ liệu sản phẩm.
"""

import urllib.request
import urllib.parse
import json
import re
import ssl
import html
import unicodedata
import time
import sys

sys.stdout.reconfigure(encoding='utf-8')

# ────────────────────────────────────────────────────────────
# CONFIG
# ────────────────────────────────────────────────────────────
SQL_OUTPUT_FILE  = "sql/direct_seed.sql"
LIMIT_PER_QUERY  = 6     # số SP lấy mỗi từ khóa
REQUEST_DELAY    = 0.4   # giây chờ giữa request (tránh bị block)
STOCK_DEFAULT    = 100   # stock mặc định khi import
USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
    "AppleWebKit/537.36 (KHTML, like Gecko) "
    "Chrome/122.0.0.0 Safari/537.36"
)

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode    = ssl.CERT_NONE

# ────────────────────────────────────────────────────────────
# CATEGORY MAP — category_id khớp với database
# ────────────────────────────────────────────────────────────
# ID phải khớp với bảng categories trong DB (xem seed_categories.sql)
CATEGORY_QUERIES = [
    (1, "Living Room",  [
        "sofa phòng khách",
        "kệ tivi phòng khách",
        "bàn trà phòng khách",
        "ghế sofa da",
    ]),
    (2, "Bedroom", [
        "giường ngủ gỗ",
        "đầu giường gỗ",
        "đèn ngủ đầu giường",
    ]),
    (3, "Kitchen", [
        "bàn ăn gia đình",
        "kệ bếp inox",
        "ghế ăn gỗ",
    ]),
    (4, "Decor", [
        "bình hoa trang trí",
        "đèn trang trí phòng khách",
        "tranh treo tường",
        "nến thơm trang trí",
    ]),
    (5, "Dining Room", [
        "bàn ăn 4 ghế",
        "ghế ăn bọc da",
        "tủ rượu gỗ",
    ]),
    (7, "Outdoor", [
        "bàn ghế sân vườn",
        "ghế nhựa ngoài trời",
        "xích đu sân vườn",
    ]),
]

# ────────────────────────────────────────────────────────────
# HELPERS
# ────────────────────────────────────────────────────────────
def slugify(text: str) -> str:
    """Chuyển tên sản phẩm sang slug URL-safe."""
    text = unicodedata.normalize("NFKD", text)
    text = text.encode("ascii", "ignore").decode("ascii")
    text = text.lower().strip()
    text = re.sub(r"[^a-z0-9\-]", "-", text)
    text = re.sub(r"-{2,}", "-", text)
    return text.strip("-")[:80]


def strip_html(raw: str) -> str:
    """Xóa toàn bộ HTML tags, decode entities, chuẩn hóa whitespace."""
    if not raw:
        return ""
    # Xóa <script> và <style>
    raw = re.sub(r"<(script|style)[^>]*>.*?</\1>", " ", raw, flags=re.DOTALL | re.IGNORECASE)
    # Block tags → newline
    raw = re.sub(r"<(br|/?p|/?div|/?li|/?tr|/?th|/?td|/?h\d)[^>]*>", "\n", raw, flags=re.IGNORECASE)
    # Strip các tag còn lại
    raw = re.sub(r"<[^>]+>", " ", raw)
    raw = html.unescape(raw)
    raw  = re.sub(r"[ \t]+", " ", raw)
    raw  = re.sub(r"\n\s*\n", "\n", raw)
    
    # ── CLEAN TIKI BRANDING ──
    raw = re.sub(r"(?i)\btiki trading\b", "Cửa hàng", raw)
    raw = re.sub(r"(?i)\btiki\b", "Cửa hàng", raw)
    raw = raw.replace("***", "")
    
    return raw.strip()


def make_short_desc(full_desc: str, name: str, max_len: int = 490) -> str:
    """Tạo short_desc từ description (lấy 2 dòng đầu, tối đa 490 ký tự)."""
    if not full_desc:
        return name[:490]
    lines = [ln.strip() for ln in full_desc.splitlines() if ln.strip()]
    excerpt = " ".join(lines[:2])
    if len(excerpt) > max_len:
        excerpt = excerpt[: max_len - 3].rsplit(" ", 1)[0] + "..."
    return excerpt or name[:490]


def sql_esc(s: str) -> str:
    """Escape string cho MySQL single-quoted value."""
    if not s:
        return ""
    return s.replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n").replace("\r", "")


def fetch_json(url: str) -> dict | None:
    """Gọi HTTP GET, trả về dict JSON hoặc None nếu lỗi."""
    try:
        req = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
        with urllib.request.urlopen(req, context=ctx, timeout=15) as resp:
            return json.loads(resp.read().decode("utf-8"))
    except Exception as err:
        print(f"  [WARN] fetch error: {err}")
        return None


def get_sale_price(p: dict, list_price: int) -> int | None:
    """
    Tiki trả về price là giá bán thực tế (đã giảm).
    list_price là giá gốc (original_price / list_price).
    → nếu price < list_price thì có sale.
    """
    original = p.get("list_price") or p.get("original_price") or 0
    current  = p.get("price", 0)
    if original and original > current:
        return current   # current là sale price
    return None          # không có sale


def get_list_price(p: dict) -> int:
    """Trả về giá gốc (list_price), fallback về price."""
    return p.get("list_price") or p.get("original_price") or p.get("price", 0)


# ────────────────────────────────────────────────────────────
# CRAWL
# ────────────────────────────────────────────────────────────
sql_products  = []
sql_images    = []
sql_variants  = []
seen_tiki_ids = set()
auto_id       = 1               # sequential ID bắt đầu từ 1

print("=" * 65)
print(f"  3legant Tiki Crawler — output: {SQL_OUTPUT_FILE}")
print("=" * 65)
print(f"{'CAT':<16} {'KEYWORD':<30} {'N':>3}")
print("-" * 55)

for cat_id, cat_name, keywords in CATEGORY_QUERIES:
    for keyword in keywords:
        url = (
            f"https://tiki.vn/api/v2/products"
            f"?limit={LIMIT_PER_QUERY}"
            f"&sort=popular"
            f"&q={urllib.parse.quote(keyword)}"
        )
        data = fetch_json(url)
        if not data:
            continue

        items = data.get("data", [])
        collected = 0

        for p in items:
            tiki_id = p.get("id")
            if not tiki_id or tiki_id in seen_tiki_ids:
                continue
            seen_tiki_ids.add(tiki_id)

            # ── Thông tin cơ bản từ list API ──
            name           = p.get("name", "").strip() or "Unnamed Product"
            thumbnail_url  = p.get("thumbnail_url", "").strip()
            rating_avg     = float(p.get("rating_average") or 0)
            review_count   = int(p.get("review_count") or 0)
            sold           = int(p.get("all_time_quantity_sold") or 0)
            brand_name     = (p.get("brand") or {}).get("name", "") or ""

            list_price  = get_list_price(p)
            sale_price  = get_sale_price(p, list_price)

            # ── Chi tiết sản phẩm (gọi detail API) ──
            time.sleep(REQUEST_DELAY)
            detail = fetch_json(f"https://tiki.vn/api/v2/products/{tiki_id}")

            if detail:
                description_raw = detail.get("description", "") or ""
                description = strip_html(description_raw)

                # short_desc: ưu tiên short_description, fallback tự tạo
                short_raw  = detail.get("short_description", "") or ""
                short_desc = strip_html(short_raw) or make_short_desc(description, name)

                # brand, material, color từ specifications
                material = ""
                color    = ""
                specs    = detail.get("specifications", [])
                for spec_group in specs:
                    for attr in spec_group.get("attributes", []):
                        attr_name  = (attr.get("name") or "").lower()
                        attr_value = (attr.get("value") or "").strip()[:95]
                        if not material and "chất liệu" in attr_name:
                            material = attr_value
                        if not color and ("màu" in attr_name or "color" in attr_name):
                            color = attr_value

                # Gallery images
                gallery = detail.get("images", []) or []
                if not gallery:
                    gallery = detail.get("images", [])
            else:
                description = name
                short_desc  = name[:490]
                material    = ""
                color       = ""
                gallery     = []

            # ── Fallback / Sanitize ──
            if not description: description = name
            if not short_desc:  short_desc  = name[:490]
            if not thumbnail_url and gallery:
                thumbnail_url = gallery[0].get("base_url", "")

            # Try authentic SKU first
            actual_sku = detail.get("sku") or detail.get("current_seller", {}).get("sku") or f"TK-{tiki_id}"

            # ── Build SQL values ──
            slug     = f"{slugify(name)}-{tiki_id}"
            sku      = sql_esc(str(actual_sku)[:75])
            is_feat  = 1 if rating_avg >= 4.5 and review_count >= 50 else 0
            sale_val = f"{sale_price}" if sale_price else "NULL"

            n  = sql_esc(name)
            d  = sql_esc(description[:3000])   # TEXT limit safety
            sd = sql_esc(short_desc[:490])
            b  = sql_esc(brand_name[:95])
            m  = sql_esc(material[:95])
            c  = sql_esc(color[:95])
            th = sql_esc(thumbnail_url[:250])

            sql_products.append(
                f"({auto_id}, {cat_id}, '{n}', '{slug}', '{sku}', "
                f"'{d}', '{sd}', "
                f"{list_price}, {sale_val}, "
                f"{STOCK_DEFAULT}, {sold}, "
                f"'{th}', "
                f"'{b}', '{m}', '{c}', "
                f"'{n}', '{sd}', "
                f"{is_feat}, 1)"
            )

            # product_images — full gallery
            if gallery:
                for order, img in enumerate(gallery[:10], 1):
                    img_url = img.get("base_url", "").strip()
                    if img_url:
                        sql_images.append(
                            f"({auto_id}, '{sql_esc(img_url[:250])}', {order})"
                        )
            elif thumbnail_url:
                sql_images.append(f"({auto_id}, '{sql_esc(thumbnail_url[:250])}', 1)")

            # product_variants — Real Variants from Configurable Products
            variants_list = detail.get("configurable_products", [])
            options_meta = detail.get("configurable_options", [])
            
            if variants_list:
                for v in variants_list[:10]: # Limit to 10 variants max
                    v_sku = sql_esc(str(v.get("sku", ""))[:75]) or f"VAR-{tiki_id}-{v.get('id')}"
                    
                    v_color = ""
                    v_size = ""
                    opt1 = v.get("option1")
                    opt2 = v.get("option2")
                    
                    # Map options properly based on metadata
                    if opt1:
                         if options_meta and options_meta[0].get("name", "").lower() == "kích thước":
                             v_size = opt1
                         else:
                             v_color = opt1
                    if opt2:
                         if options_meta and len(options_meta) > 1 and options_meta[1].get("name", "").lower() == "màu sắc":
                             v_color = opt2
                         else:
                             v_size = opt2
                             
                    v_color_esc = sql_esc(v_color[:45]) if v_color else "NULL"
                    v_size_esc = sql_esc(v_size[:15]) if v_size else "NULL"
                    
                    val_c = f"'{v_color_esc}'" if v_color_esc != "NULL" else "NULL"
                    val_s = f"'{v_size_esc}'" if v_size_esc != "NULL" else "NULL"
                    
                    v_price = v.get("price", list_price)
                    p_diff = round(max(0.00, float(v_price) - float(list_price)), 2)
                    
                    v_img = "NULL"
                    v_images = v.get("images", [])
                    if v_images and isinstance(v_images, list) and v_images[0].get("base_url"):
                         v_img = f"'{sql_esc(v_images[0].get('base_url')[:245])}'"
                    else:
                         # Fallback to main image if a variant has no specific image
                         v_img = f"'{sql_esc(thumbnail_url[:245])}'" if thumbnail_url else "NULL"
                              
                    sql_variants.append(
                        f"({auto_id}, '{v_sku}', {val_c}, {val_s}, {STOCK_DEFAULT}, {p_diff}, {v_img})"
                    )
            else:
                # Fallback simple variant
                variant_color = sql_esc(color[:45]) if color else "Tiêu chuẩn"
                v_sku = f"VAR-{sku}"
                v_img = f"'{sql_esc(thumbnail_url[:245])}'" if thumbnail_url else "NULL"
                sql_variants.append(
                    f"({auto_id}, '{v_sku}', '{variant_color}', NULL, {STOCK_DEFAULT}, 0.00, {v_img})"
                )

            auto_id += 1
            collected += 1

        print(f"{cat_name:<16} {keyword:<30} {collected:>3}")
        time.sleep(REQUEST_DELAY)

# ────────────────────────────────────────────────────────────
# WRITE SQL
# ────────────────────────────────────────────────────────────
total = auto_id - 1
print("=" * 55)
print(f"  Total products crawled: {total}")
print("=" * 55)

if not sql_products:
    print("[ERROR] Không có sản phẩm nào được crawl! Kiểm tra kết nối mạng.")
    exit(1)

with open(SQL_OUTPUT_FILE, "w", encoding="utf-8") as f:
    f.write("-- ============================================================\n")
    f.write("-- direct_seed.sql — Auto-generated by tiki_crawler.py\n")
    f.write("-- 3legant Web Shopping Project\n")
    f.write(f"-- Total products: {total}\n")
    f.write("-- HOW TO USE:\n")
    f.write("--   1. Run tools/truncate.sql first to clear product tables\n")
    f.write("--   2. Run tools/seed_categories.sql to ensure categories exist\n")
    f.write("--   3. Import this file into MySQL via phpMyAdmin or CLI\n")
    f.write("-- ============================================================\n\n")

    f.write("SET NAMES utf8mb4;\n")
    f.write("SET FOREIGN_KEY_CHECKS = 0;\n\n")

    # products
    f.write(
        "INSERT IGNORE INTO `products` (\n"
        "  `id`, `category_id`, `name`, `slug`, `sku`,\n"
        "  `description`, `short_desc`,\n"
        "  `price`, `sale_price`,\n"
        "  `stock`, `sold`,\n"
        "  `thumbnail`,\n"
        "  `brand`, `material`, `color`,\n"
        "  `meta_title`, `meta_description`,\n"
        "  `is_featured`, `is_active`\n"
        ") VALUES\n"
    )
    f.write(",\n".join(sql_products) + ";\n\n")

    # product_images
    f.write(
        "INSERT IGNORE INTO `product_images` "
        "(`product_id`, `image_url`, `sort_order`) VALUES\n"
    )
    f.write(",\n".join(sql_images) + ";\n\n")

    # product_variants
    f.write(
        "INSERT IGNORE INTO `product_variants` "
        "(`product_id`, `sku`, `color`, `size`, `stock`, `price_diff`, `image`) VALUES\n"
    )
    f.write(",\n".join(sql_variants) + ";\n\n")

    f.write("SET FOREIGN_KEY_CHECKS = 1;\n")

print(f"\n[OK] SQL written to: {SQL_OUTPUT_FILE}")
print("     Import steps:")
print("     1. phpMyAdmin → web_shopping → Import → truncate.sql")
print("     2. phpMyAdmin → web_shopping → Import → seed_categories.sql")
print(f"     3. phpMyAdmin → web_shopping → Import → {SQL_OUTPUT_FILE}")
