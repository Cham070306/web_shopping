<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tạm thời có thể comment đoạn này nếu bạn chưa fix auth admin xong
//if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
   // header("Location: ../../user/index.php");
//exit();
//}

require_once "../../config/database.php";
require_once "../../models/Post.php";

$postModel = new Post($conn);
$categories = $postModel->getCategories();
$currentPage = 'posts';
$pageTitle = 'Create Post';
$breadcrumb = 'System / Posts / Create';
$base_path = '../';
?>
<?php include '../layouts/admin_header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
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
            display: inline-block;
            text-decoration: none;
            color: #111;
            font-size: 14px;
            font-weight: 600;
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
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            outline: none;
            font-family: Arial, sans-serif;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group .editor {
            min-height: 280px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-secondary,
        .btn-primary {
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-secondary {
            background: #eef1f6;
            color: #333;
        }

        .btn-primary {
            background: #111;
            color: #fff;
        }

        .help-text {
            font-size: 12px;
            color: #777;
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
                <h1>Create New Post</h1>
                <p>Add a new blog article for the website.</p>
            </div>
            <a href="index.php" class="back-link">← Back to Posts</a>
        </div>

        <form action="../../controllers/PostController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">

            <div class="form-wrap">
                <div class="card">
                    <h3>Post Content</h3>

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" required>
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" placeholder="Leave blank to auto-generate">
                        <div class="help-text">Example: how-to-make-a-busy-bathroom-a-place-to-relax</div>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="4" placeholder="Short summary for blog list page"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea name="content" id="content" class="editor" placeholder="Write your article content here..."></textarea>
                    </div>
                </div>

                <div class="card">
                    <h3>Post Settings</h3>

                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" id="category_id">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail">Thumbnail</label>
                        <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/*">
                        <input type="hidden" name="thumbnail" value="">
                        <div class="help-text">Use image file name stored in assets/images/posts/</div>
                    </div>

                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title">
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Publish</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_published" id="is_published" value="1">
                            <label for="is_published" style="margin:0; font-weight:500;">Publish this post now</label>
                        </div>
                    </div>

                    <div class="action-bar">
                        <a href="index.php" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save Post</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php include '../layouts/admin_footer.php'; ?>