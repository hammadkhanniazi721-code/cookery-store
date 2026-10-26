<?php
require_once '../db.php';
requireAdminLogin();

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Admin' ?> - Cookery Store</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-wrapper">

    <!-- ============ SIDEBAR ============ -->
    <?php include 'sidebar.php'; ?>

    <!-- ============ TOPBAR ============ -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-logo">
                <i class="fas fa-utensils"></i>
                <span>Cookery Store</span>
            </div>
        </div>

        <div class="topbar-right">
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search...">
            </div>

            <!-- <div class="topbar-icon" title="Notifications"> -->
                <!-- <i class="fas fa-bell"></i>
                <span class="notif-badge">3</span>
            </div> -->

            <div class="topbar-user">
                <div class="user-avatar">
                    <?= strtoupper(substr($admin_name, 0, 1)) ?>
                </div>
                <div class="user-info">
                    <strong><?= htmlspecialchars($admin_name) ?></strong>
                    <span><?= ucfirst(str_replace('_', ' ', $admin_role)) ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- ============ MAIN CONTENT ============ -->
    <main class="admin-content">