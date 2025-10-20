<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'classes.php';
    $db = new db_class();

    if (!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
        header("Location: ../index.php");
        exit();
    }

    $userId = $_SESSION['user_id'];
    $sessionToken = $_SESSION['session_token'];

    $sessionData = $db->validateSession($userId, $sessionToken);
    if (!$sessionData) {
        header("Location: logout.php");
        exit();
    }

    $user = $db->getUserById($userId);
    if (!$user || $user['is_active'] == 0) {
        header("Location: logout.php");
        exit();
    }

    $permissions = $db->getUserPermissions($userId);
    if (!$permissions) {
        header("Location: logout.php");
        exit();
    }

    $canViewReports = $permissions['can_view_reports'] == 1;
    $canCreatePPMP  = $permissions['can_create_ppmp'] == 1;
    $canApprovePPMP = $permissions['can_approve_ppmp'] == 1;
    $canManageBudget= $permissions['can_manage_budget'] == 1;

    $accessTypeName = "System User";
    $remainingBudget = 0;
    if ($canApprovePPMP && $canViewReports && $canManageBudget) {
        $accessTypeName = "Procurement Head";
    } elseif ($canManageBudget) {
        $accessTypeName = "Budget Officer";
    } elseif ($canCreatePPMP) {
        $accessTypeName = "Sectors";
        $remainingBudget = $db->getRemainingBudgetByUser($userId);
    }
