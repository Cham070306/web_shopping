<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Auto-detect the base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$projectFolder = str_replace('\\', '/', dirname(dirname($scriptName)));
if ($projectFolder === '/') $projectFolder = '';
define('BASE_URL', $protocol . '://' . $host . $projectFolder . '/');
?>