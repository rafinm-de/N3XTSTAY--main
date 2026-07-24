<?php
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../config/config.php';

Session::start();
Session::destroy();

header('Location: ' . SITE_URL . '/index.php');
exit;
?>