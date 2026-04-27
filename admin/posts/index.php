<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../user/index.php");
    exit();
}

require_once "../../config/database.php";
require_once "../../models/Post.php";

$postModel = new Post($conn);

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

$posts = $postModel->getAllPosts($search, $status);
$currentPage = 'posts';
$pageTitle = 'Posts Management';
$breadcrumb = 'System / Posts';
$base_path = '../';
?>

<?php include '../layouts/admin_header.php'; ?>

<style>
    * {
        box-sizing: border-box;
    }

    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f6f8fb;
        color: #222;
    }

    .page {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        overflow: hidden;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .page-title h1 {
        margin: 0;
        font-size: 24px;
        color: #111;
    }

    .page-title p {
        margin: 4px 0 0;
        color: #666;
        font-size: 13px;
    }

    .add-btn {
        text-decoration: none;
        background: #111;
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
    }

    .filter-box {
        width: 100%;
        background: #fff;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .filter-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        width: 100%;
    }

    .filter-form input,
    .filter-form select,
    .filter-form button {
        height: 38px;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 0 12px;
        font-size: 13px;
        min-width: 0;
    }

    .filter-form input {
        flex: 1 1 220px;
    }

    .filter-form select {
        flex: 0 1 150px;
    }

    .filter-form button {
        flex: 0 0 auto;
        background: #111;
        color: #fff;
        cursor: pointer;
        border-color: #111;
        font-weight: 600;
    }

    .table-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        width: 100%;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .posts-table {
        width: 100%;
        border-collapse: collapse;
    }

    .posts-table th,
    .posts-table td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
        font-size: 13px;
    }

    .posts-table th {
        background: #fafafa;
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        white-space: nowrap;
    }

    .thumb {
        width: 60px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        display: block;
        border: 1px solid #eee;
    }

    .post-title-wrap {
        max-width: 260px;
        min-width: 0;
    }

    .post-title {
        font-weight: 600;
        color: #111;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    .post-slug {
        font-size: 11px;
        color: #888;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        margin-top: 2px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-published {
        background: #e9f9ef;
        color: #1f7a3f;
    }

    .status-draft {
        background: #fff4e5;
        color: #a76400;
    }

    .action-wrap {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-edit-icon,
    .btn-delete-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border: none;
        cursor: pointer;
        flex-shrink: 0;
    }

    .btn-edit-icon {
        background: #edf2ff;
        color: #2f5bea;
    }

    .btn-delete-icon {
        background: #fff1f2;
        color: #e11d48;
    }

    .empty-posts {
        text-align: center;
        padding: 42px 16px;
        color: #777;
    }

    @media (max-width: 992px) {
        .page {
            padding: 14px;
        }

        .posts-table th,
        .posts-table td {
            padding: 10px 8px;
            font-size: 12px;
        }

        .thumb {
            width: 52px;
            height: 36px;
        }

        .post-title {
            font-size: 13px;
        }

        .btn-edit-icon,
        .btn-delete-icon {
            width: 28px;
            height: 28px;
        }
    }

    @media (max-width: 768px) {
        .topbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .add-btn {
            width: 100%;
            text-align: center;
        }

        .page-title h1 {
            font-size: 22px;
        }

        .filter-form input,
        .filter-form select,
        .filter-form button {
            width: 100%;
            flex: 1 1 100%;
        }

        .posts-table {
            transform: scale(0.95);
            transform-origin: top left;
            width: 105%;
        }

        .thumb {
            width: 46px;
            height: 32px;
        }

        .post-title {
            font-size: 12px;
        }

        .post-slug {
            font-size: 10px;
        }

        .status-badge {
            font-size: 10px;
            padding: 3px 6px;
        }
    }

    @media (max-width: 576px) {
        .page {
            padding: 10px;
        }

        .page-title h1 {
            font-size: 18px;
        }

        .page-title p {
            font-size: 12px;
        }

        .filter-box {
            padding: 12px;
        }

        .posts-table {
            transform: scale(0.88);
            transform-origin: top left;
            width: 114%;
        }

        .posts-table th,
        .posts-table td {
            padding: 8px 6px;
            font-size: 11px;
        }

        .thumb {
            width: 40px;
            height: 30px;
        }

        .btn-edit-icon,
        .btn-delete-icon {
            width: 24px;
            height: 24px;
        }
    }

    @media (max-width: 420px) {
        .page {
            padding: 8px;
        }

        .posts-table {
            transform: scale(0.80);
            transform-origin: top left;
            width: 125%;
        }
    }
</style>

<div class="page">
    <div class="topbar">
        <div class="page-title">
            <h1>Posts Management</h1>
            <p>Manage blog posts and drafts.</p>
        </div>

        <a href="create.php" class="add-btn">+ New Post</a>
    </div>

    <div class="filter-box">
        <form method="GET" class="filter-form">
            <input 
                type="text" 
                name="search" 
                placeholder="Search..." 
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="status">
                <option value="">All Status</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Published</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Draft</option>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table class="posts-table">
                <thead>
                    <tr>
                        <th>Thumb</th>
                        <th>Post Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <?php
                                    $thumbnail = !empty($post['thumbnail']) ? $post['thumbnail'] : 'default.jpg';
                                    ?>
                                    <img 
                                        src="../../assets/images/posts/<?= htmlspecialchars($thumbnail) ?>" 
                                        class="thumb" 
                                        alt="Post thumbnail"
                                    >
                                </td>

                                <td title="<?= htmlspecialchars($post['title'] ?? '') ?>">
                                    <div class="post-title-wrap">
                                        <div class="post-title">
                                            <?= htmlspecialchars($post['title'] ?? 'Untitled') ?>
                                        </div>

                                        <div class="post-slug">
                                            <?= htmlspecialchars($post['slug'] ?? '') ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($post['category_name'] ?? 'General') ?>
                                </td>

                                <td>
                                    <span class="status-badge <?= !empty($post['is_published']) ? 'status-published' : 'status-draft' ?>">
                                        <?= !empty($post['is_published']) ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>

                                <td>
                                    <?= number_format((int)($post['views'] ?? 0)) ?>
                                </td>

                                <td>
                                    <?php
                                    $dateValue = $post['published_at'] ?? '';
                                    echo !empty($dateValue) ? date('d/m/y', strtotime($dateValue)) : '—';
                                    ?>
                                </td>

                                <td>
                                    <div class="action-wrap">
                                        <a href="edit.php?id=<?= (int)$post['id'] ?>" class="btn-edit-icon" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                            </svg>
                                        </a>

                                        <form action="../../controllers/PostController.php" method="POST" onsubmit="return confirm('Delete?');" style="margin:0">
                                            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                            <input type="hidden" name="action" value="delete">

                                            <button type="submit" class="btn-delete-icon" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-posts">
                                No posts found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../layouts/admin_footer.php'; ?>