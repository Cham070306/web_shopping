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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Management</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f8fb;
            color: #222;
        }

        .page {
            max-width: 1250px;
            margin: 0 auto;
            padding: 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .topbar-left h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .topbar-left p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .add-btn {
            display: inline-block;
            text-decoration: none;
            background: #111;
            color: #fff;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .filter-box {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        .filter-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select,
        .filter-form button {
            height: 42px;
            padding: 0 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }

        .filter-form input {
            min-width: 260px;
        }

        .filter-form select {
            min-width: 180px;
        }

        .filter-form button {
            background: #111;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .table-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        th {
            background: #fafafa;
            font-size: 13px;
            color: #666;
            font-weight: 600;
        }

        td {
            font-size: 14px;
        }

        .thumb {
            width: 90px;
            height: 68px;
            object-fit: cover;
            border-radius: 10px;
            background: #eee;
            display: block;
        }

        .post-title {
            font-weight: 600;
            color: #111;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .post-slug {
            font-size: 12px;
            color: #888;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
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
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-edit,
        .btn-delete {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-edit {
            background: #edf2ff;
            color: #2f5bea;
        }

        .btn-delete {
            background: #ffeaea;
            color: #d93025;
        }

        .empty-row {
            text-align: center;
            color: #666;
            padding: 32px 16px;
        }

        @media (max-width: 900px) {
            .admin-shell {
            display: flex;
            min-height: 100vh;
            background: #f6f8fb;
         }

        .admin-main {
            flex: 1;
            margin-left: 260px;
            width: calc(100% - 260px);
        }

        .admin-sidebar {
            width: 260px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
            .page {
                padding: 16px;
            }

            .filter-form input,
            .filter-form select,
            .filter-form button {
                width: 100%;
            }

            .table-card {
                overflow-x: auto;
            }

            table {
                min-width: 900px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Posts Management</h1>
                <p>Manage blog posts, published articles, and drafts.</p>
            </div>
            <a href="create.php" class="add-btn">+ Add New Post</a>
        </div>

        <div class="filter-box">
            <form method="GET" class="filter-form">
                <input
                    type="text"
                    name="search"
                    placeholder="Search by title or slug..."
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
            <table>
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Post</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Published Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <?php
                                $thumb = !empty($post['thumbnail'])
                                    ? "../../assets/images/posts/" . $post['thumbnail']
                                    : "../../assets/images/banner.jpg";
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($thumb) ?>" alt="Thumbnail" class="thumb">
                                </td>

                                <td>
                                    <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                    <div class="post-slug"><?= htmlspecialchars($post['slug']) ?></div>
                                </td>

                                <td><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></td>

                                <td>
                                    <span class="status-badge <?= !empty($post['is_published']) ? 'status-published' : 'status-draft' ?>">
                                        <?= !empty($post['is_published']) ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>

                                <td><?= (int)($post['views'] ?? 0) ?></td>

                                <td>
                                    <?= !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : '-' ?>
                                </td>

                                <td>
                                    <div class="action-wrap">
                                        <a href="edit.php?id=<?= (int)$post['id'] ?>" class="btn-edit">Edit</a>

                                        <form action="../../controllers/PostController.php" method="POST" onsubmit="return confirm('Delete this post?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-row">No posts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php include '../layouts/admin_footer.php'; ?>