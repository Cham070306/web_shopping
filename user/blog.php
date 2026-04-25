<?php
require_once "../config/database.php";
require_once "../models/Post.php";

$postModel = new Post($conn);

$search = trim($_GET['search'] ?? '');
$categoryId = trim($_GET['category'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$posts = $postModel->getPublishedPosts($search, $categoryId, $perPage, $offset);
$totalPosts = $postModel->countPublishedPosts($search, $categoryId);
$totalPages = max(1, ceil($totalPosts / $perPage));
$categories = $postModel->getCategories();

function getBlogThumbnail($thumbnail)
{
    $default = "../assets/images/banner.jpg";

    if (empty($thumbnail)) {
        return $default;
    }

    $thumbnail = trim($thumbnail);

    if (strpos($thumbnail, 'http://') === 0 || strpos($thumbnail, 'https://') === 0) {
        return $thumbnail;
    }

    if (strpos($thumbnail, '../') === 0) {
        return $thumbnail;
    }

    if (strpos($thumbnail, '/assets/') === 0) {
        return '..' . $thumbnail;
    }

    if (strpos($thumbnail, 'assets/') === 0) {
        return '../' . $thumbnail;
    }

    // DB của bạn đang lưu kiểu: post-1.jpg, post-2.jpg...
    return "../assets/images/posts/" . ltrim($thumbnail, '/');
}

include "../includes/header.php";
include "../includes/navbar.php";
?>

<style>
.blog-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: Arial, sans-serif;
}

.blog-hero {
    position: relative;
    width: 100%;
    height: 280px;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 28px;
    background: url('../assets/images/banner.jpg') center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
}

.blog-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.45);
}

.blog-hero-content {
    position: relative;
    text-align: center;
    z-index: 2;
}

.blog-hero-content h1 {
    font-size: 54px;
    font-weight: 600;
    color: #111;
    margin-bottom: 12px;
}

.blog-hero-content p {
    font-size: 15px;
    color: #444;
    margin: 0;
}

.blog-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}

.blog-tabs {
    display: flex;
    gap: 24px;
    align-items: center;
    flex-wrap: wrap;
}

.blog-tabs a {
    text-decoration: none;
    font-size: 13px;
    color: #777;
    padding-bottom: 6px;
    border-bottom: 1px solid transparent;
}

.blog-tabs a.active {
    color: #111;
    border-bottom: 1px solid #111;
}

.blog-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.blog-search-form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.blog-search-form input,
.blog-search-form select {
    height: 38px;
    padding: 0 12px;
    border: 1px solid #ddd;
    background: #fff;
    font-size: 13px;
    color: #222;
    outline: none;
}

.blog-search-form input {
    min-width: 200px;
}

.blog-search-form select {
    min-width: 180px;
}

.blog-search-form button {
    height: 38px;
    padding: 0 18px;
    border: none;
    background: #111;
    color: #fff;
    font-size: 13px;
    cursor: pointer;
}

.blog-sort {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px 24px;
}

.blog-card {
    background: #fff;
}

.blog-card-thumb {
    display: block;
    width: 100%;
    aspect-ratio: 1 / 0.78;
    overflow: hidden;
    margin-bottom: 12px;
    background: #f3f3f3;
}

.blog-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.blog-card-title {
    font-size: 15px;
    line-height: 1.45;
    font-weight: 500;
    margin-bottom: 8px;
}

.blog-card-title a {
    text-decoration: none;
    color: #111;
}

.blog-card-meta {
    font-size: 12px;
    color: #8a8a8a;
}

.blog-empty {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-size: 15px;
}

.show-more-wrap {
    display: flex;
    justify-content: center;
    margin-top: 40px;
}

.show-more-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    height: 40px;
    padding: 0 20px;
    border: 1px solid #111;
    border-radius: 24px;
    text-decoration: none;
    color: #111;
    font-size: 13px;
    background: #fff;
    transition: 0.2s ease;
}

.show-more-btn:hover {
    background: #111;
    color: #fff;
}

.newsletter-section {
    margin-top: 70px;
    min-height: 320px;
    background: #f7f7f7;
    border-radius: 4px;
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr;
    overflow: hidden;
}

.newsletter-side {
    background-size: cover;
    background-position: center;
    min-height: 320px;
}

.newsletter-side.left {
    background-image: url('../assets/images/banner.jpg');
}

.newsletter-side.right {
    background-image: url('../assets/images/banner.jpg');
}

.newsletter-center {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 30px;
    text-align: center;
    background: #f8f8f8;
}

.newsletter-content h2 {
    font-size: 36px;
    color: #111;
    margin-bottom: 10px;
    font-weight: 500;
}

.newsletter-content p {
    font-size: 14px;
    color: #666;
    margin-bottom: 22px;
}

.newsletter-form {
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #bbb;
    max-width: 360px;
    margin: 0 auto;
    padding-bottom: 8px;
}

.newsletter-form input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 14px;
    color: #111;
}

.newsletter-form button {
    border: none;
    background: transparent;
    color: #888;
    cursor: pointer;
    font-size: 14px;
}

@media (max-width: 991px) {
    .blog-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .newsletter-section {
        grid-template-columns: 1fr;
    }

    .newsletter-side {
        display: none;
    }

    .newsletter-center {
        min-height: 260px;
    }
}

@media (max-width: 767px) {
    .blog-hero {
        height: 220px;
    }

    .blog-hero-content h1 {
        font-size: 38px;
    }

    .blog-grid {
        grid-template-columns: 1fr;
    }

    .blog-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .blog-search-form {
        width: 100%;
    }

    .blog-search-form input,
    .blog-search-form select,
    .blog-search-form button {
        width: 100%;
    }
}
</style>

<div class="blog-page">
    <div class="blog-hero">
        <div class="blog-hero-content">
            <h1>Our Blog</h1>
            <p>Home ideas and design inspiration.</p>
        </div>
    </div>

    <div class="blog-toolbar">
        <div class="blog-tabs">
            <a href="blog.php" class="<?= $categoryId === '' ? 'active' : '' ?>">All Blog</a>
            <a href="blog.php?category=<?= isset($categories[0]['id']) ? $categories[0]['id'] : '' ?>">
                Articles
            </a>
        </div>

        <div class="blog-actions">
            <div class="blog-sort">Sort by</div>

            <form method="GET" class="blog-search-form">
                <input type="text" name="search" placeholder="Search post..." value="<?= htmlspecialchars($search) ?>">

                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <?php if (empty($posts)): ?>
        <div class="blog-empty">No blog posts found.</div>
    <?php else: ?>
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <?php $thumb = getBlogThumbnail($post['thumbnail'] ?? ''); ?>
                <div class="blog-card">
                    <a href="blog_detail.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card-thumb">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    </a>

                    <div class="blog-card-title">
                        <a href="blog_detail.php?slug=<?= urlencode($post['slug']) ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </div>

                    <div class="blog-card-meta">
                        <?= htmlspecialchars($post['author_name'] ?? 'Admin') ?>
                        <?php if (!empty($post['published_at'])): ?>
                            • <?= date('M d, Y', strtotime($post['published_at'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($page < $totalPages): ?>
            <div class="show-more-wrap">
                <a class="show-more-btn" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($categoryId) ?>">
                    Show more
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

   
</div>
<?php include "../includes/newsletter.php";?>

<?php include "../includes/footer.php"; ?>