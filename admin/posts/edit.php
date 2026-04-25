<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tạm comment nếu auth admin chưa ổn
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: ../../user/index.php");
//     exit();
// }

require_once "../../config/database.php";
require_once "../../models/Post.php";

$postModel = new Post($conn);

$id = (int)($_GET['id'] ?? 0);
$post = $postModel->getById($id);

if (!$post) {
    echo "Post not found.";
    exit;
}

$categories = $postModel->getCategories();
$currentPage = 'posts';
$pageTitle = 'Edit Post';
$breadcrumb = 'System / Posts / Edit';
$base_path = '../';
?>
<?php include '../layouts/admin_header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <style>
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .page-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .back-link {
            color: #111;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .form-wrap {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            outline: none;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        .editor {
            min-height: 320px;
        }

        .preview-img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            border-radius: 10px;
            background: #eee;
            margin-top: 10px;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-row input {
            width: auto;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-primary,
        .btn-secondary {
            padding: 12px 18px;
            border-radius: 10px;
            border: none;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: #111;
            color: #fff;
        }

        .btn-secondary {
            background: #eef1f6;
            color: #333;
        }

        .help {
            color: #777;
            font-size: 12px;
            margin-top: 6px;
        }

        @media (max-width: 992px) {
            .form-wrap {
                grid-template-columns: 1fr;
            }

            .page {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="page-header">
        <div>
            <h1>Edit Post</h1>
            <p>Update blog article information.</p>
        </div>
        <a href="index.php" class="back-link">← Back to Posts</a>
    </div>

    <form action="../../controllers/PostController.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">

        <div class="form-wrap">
            <div class="card">
                <h3>Post Content</h3>

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" value="<?= htmlspecialchars($post['slug']) ?>">
                </div>

                <div class="form-group">
                    <label>Excerpt</label>
                    <textarea name="excerpt" rows="4"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" class="editor"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    <div class="help">You can paste HTML content here.</div>
                </div>
            </div>

            <div class="card">
                <h3>Post Settings</h3>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['id'] ?>" <?= $post['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail_file" accept="image/*">
                    <input type="hidden" name="thumbnail" value="<?= htmlspecialchars($post['thumbnail'] ?? '') ?>">
                    <div class="help">Leave empty if you want to keep the current thumbnail.</div>
                    <div class="help">Image file should be in assets/images/posts/</div>

                    <?php if (!empty($post['thumbnail'])): ?>
                        <img class="preview-img" src="../../assets/images/posts/<?= htmlspecialchars($post['thumbnail']) ?>" alt="">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" value="<?= htmlspecialchars($post['meta_title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" rows="4"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Publish</label>
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_published" value="1" id="is_published" <?= !empty($post['is_published']) ? 'checked' : '' ?>>
                        <label for="is_published" style="margin:0;font-weight:500;">Published</label>
                    </div>
                </div>

                <div class="actions">
                    <a href="index.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Update Post</button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php include '../layouts/admin_footer.php'; ?>