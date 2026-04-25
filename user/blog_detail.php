<?php
require_once "../config/database.php";
require_once "../models/Post.php";

$postModel = new Post($conn);

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header("Location: blog.php");
    exit;
}

$post = $postModel->getPublishedPostBySlug($slug);
if (!$post) {
    header("Location: blog.php");
    exit;
}

$postModel->increaseViews($post['id']);
if ($slug === '7-ways-to-decor-your-home-like-a-professional') {
    $post['title'] = 'How to make a busy bathroom a place to relax';
    $post['author_name'] = 'Henrik Annemark';
    $post['published_at'] = '2023-10-16 10:00:00';
    $post['content'] = '
    <div class="blog-image-full">
        <img src="/web_shopping/assets/images/posts/post-10.png" alt="Post image 10">
    </div>

    <p style="font-size:14px;line-height:1.75;color:#353945;margin-bottom:14px;">
    Your bathroom serves a string of busy functions on a daily basis. See how you can make all of them work, and still have room for comfort and relaxation.
    </p>

    <h2 style="font-size:16px;font-weight:700;margin:14px 0 8px;">A cleaning hub with built-in ventilation</h2>

    <p style="font-size:14px;line-height:1.75;color:#353945;margin-bottom:16px;">
    Use a rod and a shower curtain to create a complement to your cleaning cupboard. Unsightly equipment is stored out of sight yet accessibly close – while the air flow helps dry any dampness.
    </p>

    <div class="blog-two-images">
        <img src="/web_shopping/assets/images/posts/post-11.png" alt="Post image 11">
        <img src="/web_shopping/assets/images/posts/post-12.png" alt="Post image 12">
    </div>

    <h2 style="font-size:16px;font-weight:700;margin:14px 0 8px;">Storage with a calming effect</h2>

    <p style="font-size:14px;line-height:1.75;color:#353945;margin-bottom:16px;">
    Having a lot to store doesn’t mean it all has to go in a cupboard. Many bathroom items are better kept out in the open – either to be close at hand or are nice to look at.
    </p>

    <h2 style="font-size:16px;font-weight:700;margin:14px 0 8px;">Kit your clutter for easy access</h2>

    <p style="font-size:14px;line-height:1.75;color:#353945;margin-bottom:18px;">
    Even if you have a cabinet ready to swallow the clutter, it’s worth resisting a little. Let containers hold kits for different activities – home spa, make-up, personal hygiene.
    </p>

    <div class="blog-last-grid">
        <div class="blog-last-image">
            <img src="/web_shopping/assets/images/posts/post-13.png" alt="Post image 13">
        </div>

        <div class="blog-side-block">
            <h3 style="font-size:16px;font-weight:700;margin:0 0 8px;">An ecosystem of towels</h3>
            <p style="font-size:14px;line-height:1.75;margin:0 0 16px;">
                Racks or hooks that allow air to circulate around each towel prolong their freshness.
            </p>

            <h3 style="font-size:16px;font-weight:700;margin:0 0 8px;">Make your mop disappear</h3>
            <p style="font-size:14px;line-height:1.75;margin:0;">
                Having your cleaning tools organized makes them easier to both use and return to.
            </p>
        </div>
    </div>';
}
$relatedPosts = [];
if (!empty($post['category_id'])) {
    $relatedPosts = $postModel->getRelatedPosts((int)$post['category_id'], (int)$post['id'], 3);
}

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

    return "../assets/images/posts/" . ltrim($thumbnail, '/');
}

include "../includes/header.php";
include "../includes/navbar.php";
?>

<style>
.blog-detail-page {
    max-width: 1280px;
    margin: 0 auto;
    padding: 28px 20px 70px;
    font-family: Arial, sans-serif;
}

.breadcrumb {
    font-size: 13px;
    color: #777;
    margin-bottom: 24px;
}

.breadcrumb a {
    color: #777;
    text-decoration: none;
}

.blog-detail-header {
    max-width: 820px;
    margin: 0 auto 24px;
    text-align: left;
}

.blog-detail-category {
    display: inline-block;
    font-size: 12px;
    color: #111;
    margin-bottom: 14px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    font-weight: 600;
}

.blog-detail-title {
    font-size: 60px;
    line-height: 1.02;
    color: #111;
    margin-bottom: 18px;
    font-weight: 600;
    max-width: 760px;
}

.blog-detail-meta {
    font-size: 14px;
    color: #6C7275;
    display: flex;
    gap: 22px;
    flex-wrap: wrap;
}

.blog-detail-image-wrap {
    margin: 28px auto 28px;
    max-width: 820px;
}

.blog-detail-thumb {
    width: 100%;
    object-fit: cover;
    display: block;
}

.blog-detail-content {
    max-width: 820px;
    margin: 0 auto;
    color: #333;
    font-size: 16px;
    line-height: 1.9;
}

.blog-detail-content img {
    max-width: 100%;
    height: auto;
    display: block;
}

.blog-detail-content p {
    margin-bottom: 16px;
}

.blog-detail-content h1,
.blog-detail-content h2,
.blog-detail-content h3,
.blog-detail-content h4 {
    color: #111;
}

.custom-blog-detail {
    max-width: 820px;
    margin: 0 auto;
}

.blog-intro {
    font-size: 15px;
    line-height: 1.85;
    color: #353945;
    margin-bottom: 18px;
}

.section-title {
    font-size: 40px;
    line-height: 1.15;
    color: #111;
    font-weight: 600;
    margin: 18px 0 10px;
}

.section-text {
    font-size: 15px;
    line-height: 1.85;
    color: #353945;
    margin-bottom: 24px;
}

.blog-two-images {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin: 24px 0 24px;
}

.blog-two-images img {
    width: 100%;
    aspect-ratio: 548 / 729;   /* 👈 chuẩn theo figma */
    object-fit: cover;
    display: block;
}

.blog-last-grid {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    gap: 18px;
    align-items: start;
    margin-top: 10px;
}

.blog-last-image img {
    width: 100%;
    display: block;
}

.blog-side-block h3 {
    font-size: 20px;
    line-height: 1.25;
    color: #111;
    font-weight: 600;
    margin: 0 0 8px;
}

.blog-side-block p {
    font-size: 14px;
    line-height: 1.8;
    color: #353945;
    margin-bottom: 20px;
}

.related-section {
    max-width: 1240px;
    margin: 70px auto 0;
    padding: 0 20px;
}

.related-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.related-title {
    font-size: 18px;
    font-weight: 600;
    color: #111;
    margin: 0;
}

.related-more {
    font-size: 13px;
    color: #111;
    text-decoration: none;
    border-bottom: 1px solid #111;
    padding-bottom: 2px;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.related-card-thumb {
    display: block;
    width: 100%;
    aspect-ratio: 1 / 0.72;
    overflow: hidden;
    background: #f3f3f3;
    margin-bottom: 12px;
}

.related-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.related-card-name {
    font-size: 16px;
    line-height: 1.35;
    margin-bottom: 8px;
}

.related-card-name a {
    text-decoration: none;
    color: #111;
    font-weight: 500;
}

.related-card-meta {
    font-size: 12px;
    color: #8a8a8a;
}

.blog-newsletter-wrap {
    margin-top: 60px;
}

.newsletter-section {
    width: 100%;
    min-height: 320px;
    background: #F3F5F7;
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
    background-image: url('/web_shopping/assets/images/banner.jpg');
}

.newsletter-side.right {
    background-image: url('/web_shopping/assets/images/banner.jpg');
}

.newsletter-center {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 30px;
    text-align: center;
}

.newsletter-content h2 {
    font-size: 42px;
    color: #111;
    margin-bottom: 10px;
    font-weight: 600;
}

.newsletter-content p {
    font-size: 14px;
    color: #6C7275;
    margin-bottom: 24px;
}

.newsletter-form {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #6C7275;
    max-width: 380px;
    margin: 0 auto;
    padding-bottom: 8px;
}

.newsletter-icon {
    font-size: 13px;
    color: #6C7275;
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
    color: #6C7275;
    cursor: pointer;
    font-size: 14px;
}

@media (max-width: 991px) {
    .blog-detail-title {
        font-size: 42px;
    }

    .section-title {
        font-size: 30px;
    }

    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .newsletter-section {
        grid-template-columns: 1fr;
    }

    .newsletter-side {
        display: none;
    }

    .newsletter-center {
        min-height: 250px;
    }
}

@media (max-width: 767px) {
    .blog-detail-title {
        font-size: 32px;
    }

    .section-title {
        font-size: 24px;
    }

    .blog-two-images,
    .blog-last-grid,
    .related-grid {
        grid-template-columns: 1fr;
    }

    .related-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .newsletter-content h2 {
        font-size: 32px;
    }
}
.blog-image-full {
    max-width: 1120px;
    margin: 28px auto 24px;
}

.blog-image-full img {
    width: 100%;          /* 👈 QUAN TRỌNG */
    aspect-ratio: 1119.99 / 646.92;       /* giống layout mẫu */
    object-fit: cover;
    display: block;
}
</style>

<div class="blog-detail-page">
    <div class="breadcrumb">
        <a href="index.php">Home</a> /
        <a href="blog.php">Blog</a> /
        <span><?= htmlspecialchars($post['title']) ?></span>
    </div>

    <div class="blog-detail-header">
        <div class="blog-detail-category">
            ARTICLE
        </div>

        <h1 class="blog-detail-title">
            <?= htmlspecialchars($post['title']) ?>
        </h1>

        <div class="blog-detail-meta">
            <span><?= htmlspecialchars($post['author_name'] ?? 'Admin') ?></span>
            <?php if (!empty($post['published_at'])): ?>
                <span><?= date('F d, Y', strtotime($post['published_at'])) ?></span>
            <?php endif; ?>
        </div>
    </div>

   
   <div class="blog-detail-content custom-article-layout">
        <?= $post['content'] ?>
            </div>
       

        
    </div>
</div>

<?php if (!empty($relatedPosts)): ?>
    <div class="related-section">
        <div class="related-header">
            <h2 class="related-title">You might also like</h2>
            <a href="blog.php" class="related-more">More Articles →</a>
        </div>

        <div class="related-grid">
            <?php foreach ($relatedPosts as $item): ?>
                <div class="related-card">
                    <a href="blog_detail.php?slug=<?= urlencode($item['slug']) ?>" class="related-card-thumb">
                        <img src="<?= htmlspecialchars(getBlogThumbnail($item['thumbnail'] ?? '')) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    </a>

                    <div class="related-card-name">
                        <a href="blog_detail.php?slug=<?= urlencode($item['slug']) ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
                    </div>

                    <div class="related-card-meta">
                        <?= !empty($item['published_at']) ? date('F d, Y', strtotime($item['published_at'])) : '' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="blog-newsletter-wrap">
    <div class="newsletter-section">
        <div class="newsletter-side left"></div>

        <div class="newsletter-center">
            <div class="newsletter-content">
                <h2>Join Our Newsletter</h2>
                <p>Sign up for deals, new products and promotions</p>
                <form class="newsletter-form">
                    <span class="newsletter-icon">✉</span>
                    <input type="email" placeholder="Email address">
                    <button type="button">Signup</button>
                </form>
            </div>
        </div>

        <div class="newsletter-side right"></div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>