<?php
    // Load environment variables and configurations
    require_once __DIR__ . '/functions.php';
    // To prevent direct access to this file
    preventDirectAccess();

    // Dynamically set Page Title
    $pageName = generatePageTitle($pageTitle ?? '');

    ini_set('display_errors', 0); // 0 in production
    ini_set('log_errors', 1);
    error_reporting(E_ALL);

    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    ini_set('error_log', "$logDir/php_errors.log");

    date_default_timezone_set('Asia/Manila');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // (Optional) Enforce HTTPS
    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        $_SERVER['SERVER_PORT'] == 443
    ) {
        // Already HTTPS
    } else {
        if ($_SERVER['SERVER_NAME'] !== 'localhost') {
            $httpsURL = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $httpsURL", true, 301);
            exit;
        }
    }

    // Get current page name
    $current_page = basename($_SERVER['PHP_SELF']);

    // Set Client Name
    $clientName = "TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES";
    // Set System Name
    $systemName = "PROCUREMENT PLANNING AND CONSOLIDATION SYSTEM";
    // Logo path
    $systemLogo = "assets/img/logo/system-logo.png";

    