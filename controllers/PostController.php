<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";
require_once "../models/Post.php";

$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    die("Access denied");
}

$postModel = new Post($conn);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function makeSlug($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'post-' . time();
}

function uploadPostThumbnail($fieldName = 'thumbnail_file')
{
    if (empty($_FILES[$fieldName]['name'])) {
        return '';
    }

    $uploadDir = __DIR__ . '/../assets/images/posts/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $originalName = $_FILES[$fieldName]['name'];
    $tmpName = $_FILES[$fieldName]['tmp_name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return '';
    }

    $newName = 'post-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
    $targetPath = $uploadDir . $newName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        return $newName;
    }

    return '';
}

if ($action === 'create') {
    $slug = trim($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');

    if ($slug === '') {
        $slug = makeSlug($title);
    }

    if ($postModel->slugExists($slug)) {
        $slug .= '-' . time();
    }

    $uploadedThumbnail = uploadPostThumbnail();
    $thumbnail = $uploadedThumbnail !== ''
        ? $uploadedThumbnail
        : trim($_POST['thumbnail'] ?? '');

    $data = [
        'category_id' => $_POST['category_id'] ?? null,
        'author_id' => $_SESSION['user']['id'],
        'title' => $title,
        'slug' => $slug,
        'thumbnail' => $thumbnail,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'is_published' => isset($_POST['is_published']) ? 1 : 0
    ];

    if ($postModel->create($data)) {
        $_SESSION['success'] = 'Post created successfully.';
    } else {
        $_SESSION['error'] = 'Failed to create post.';
    }

    header("Location: ../admin/posts/index.php");
    exit;
}

if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $current = $postModel->getById($id);

    if (!$current) {
        $_SESSION['error'] = 'Post not found.';
        header("Location: ../admin/posts/index.php");
        exit;
    }

    $slug = trim($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');

    if ($slug === '') {
        $slug = makeSlug($title);
    }

    if ($postModel->slugExists($slug, $id)) {
        $slug .= '-' . time();
    }

    $uploadedThumbnail = uploadPostThumbnail();
    $thumbnail = $uploadedThumbnail !== ''
        ? $uploadedThumbnail
        : trim($_POST['thumbnail'] ?? $current['thumbnail']);

    $data = [
        'category_id' => $_POST['category_id'] ?? null,
        'title' => $title,
        'slug' => $slug,
        'thumbnail' => $thumbnail,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'published_at' => $current['published_at']
    ];

    if ($postModel->update($id, $data)) {
        $_SESSION['success'] = 'Post updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update post.';
    }

    header("Location: ../admin/posts/index.php");
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    if ($postModel->delete($id)) {
        $_SESSION['success'] = 'Post deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete post.';
    }

    header("Location: ../admin/posts/index.php");
    exit;
}

header("Location: ../admin/posts/index.php");
exit;