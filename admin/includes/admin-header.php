<?php
require_once __DIR__ . '/../../includes/Session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/config.php';

Session::start();

// Require admin
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($pageTitle) ? $pageTitle . ' - Admin - ' . SITE_NAME : 'Admin - ' . SITE_NAME; ?>
    </title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
</head>

<body>
    <!-- Flash Messages -->
    <?php if (Session::hasFlash('success')): ?>
        <div style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="alert alert-success" style="box-shadow: var(--shadow-xl);">
                ✓
                <?php echo Session::getFlash('success'); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
        <div style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="alert alert-error" style="box-shadow: var(--shadow-xl);">
                ✗
                <?php echo Session::getFlash('error'); ?>
            </div>
        </div>
    <?php endif; ?>