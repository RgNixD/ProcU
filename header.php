<?php
  require_once 'php/classes.php';
  $db = new db_class();
  if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
      $userId = $_SESSION['user_id'];
      $sessionToken = $_SESSION['session_token'];

      $sessionData = $db->validateSession($userId, $sessionToken);
      if ($sessionData) {
          $permissions = $_SESSION['permissions'] ?? $db->getUserPermissions($userId);

          $redirectURL = determineRedirectURL($permissions);
          header("Location: $redirectURL");
          exit(); 
      } else {
          header("Location: php/logout.php");
          exit();
      }
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?= $systemLogo ?>" type="image/ico" />
  <title><?= htmlspecialchars($pageName) ?></title>

  <!-- Bootstrap -->
  <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
  <!-- Sweetalerts-->
  <link rel="stylesheet" href="assets/css/sweetalert2.min.css">
  <!-- Custom Theme Style -->
  <link href="build/css/custom.min.css" rel="stylesheet">
  <style>
    html, body { 
      overflow-x: hidden; 
    }

    body {
      background:
        linear-gradient(to bottom,
          rgba(128, 0, 0, 0.2) 0%,
          rgba(128, 0, 0, 0.25) 30%,
          rgba(128, 0, 0, 0.35) 60%,
          rgba(128, 0, 0, 0.70) 100%),
        url('assets/img/login-bg.png') center center fixed;
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }

    .top_nav {
      margin-left: 0 !important;
      width: 100% !important;
      background-color: #a83232;
      border-bottom: 1px solid #ddd;
    }

    .top_nav .navbar { 
      padding-left: 12px; 
      padding-right: 12px; 
    }

    .top_nav .navbar-nav .nav-link,
    .top_nav .navbar-brand {
      color: #fff !important;
      font-weight: 500;
    }

    .navbar-toggler {
      border-color: rgba(255,255,255,0.3);
    }
    .navbar-toggler-icon {
      width: 24px;
      height: 18px;
      display: inline-block;
      background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: center;
    }

    .top_nav .nav_menu {
      height: 60px;
    }

    .top_nav img {
      height: 50px;
      width: 50px;
      border-radius: 50%;
      margin-right: 10px;

      background-color: #fff;
      border: 2px solid #fff;
      padding: 1px;
      object-fit: cover;
    }

    .content .login-container {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .content .login-title {
      font-size: 30px;
      font-weight: bold;
      letter-spacing: 2px;
      margin-bottom: 25px;
      color: #fff;
      text-align: center;
    }

    .content .login-card {
      margin: 20px auto;
      background: rgba(30, 30, 30, 0.6);
      max-width: 450px;
      width: 100%;
      color: #fff;
      border: none;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(6px);
    }

    .container .main_container .content {
      margin-left: 0 !important;
      margin-top: 50px !important;
      padding: 20px;
      width: 100% !important;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: auto !important;
    }

    .toggle-password {
      position: absolute;
      top: 38px;
      right: 12px;
      cursor: pointer;
      font-size: 18px;
      color: #6c757d;
    }

    .toggle-password:hover {
      color: #007bff;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0; 
    }

    .brand-text {
      font-family: "Anton", sans-serif;
      font-weight: 400;
      font-style: normal;
      text-transform: uppercase;
      font-size: 30px;
      color: #fff;
      display: inline-block;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: calc(100vw - 150px);
    }
    
    button.btn, .get-started a.btn, a.btn {
      background-color: #a83232;
      border: 1px solid lightgrey;
      color: #fff;
    }

    .swal2-confirm-custom, .swal2-cancel-custom {
        border: none !important; 
        box-shadow: none !important;
        outline: none !important;
    }

    .swal2-confirm-custom {
        border: 1px solid #fff !important; 
        background-color: #a83232 !important; 
        color: #fff !important;
    }

    .swal2-cancel-custom {
        border: 1px solid #ccc !important;
        background-color: #555 !important; 
        color: #fff !important;
    }

    /* Home */
    .row.text-center {
      display: flex;
      flex-wrap: wrap;
    }

    .row.text-center > [class*="col-"] {
      display: flex;
    }

    .row.text-center .card {
      flex: 1 1 auto;
    }
    /* End Home */
    
    /* Footer */
    footer {
      background-color: #a83232;
      color: #fff;
      border-top: 1px solid #ccc;
    }
    /* EndFooter */

    @media (max-width: 767.98px) {
      .brand-text {
        font-size: 25px;
        max-width: calc(100vw - 110px);
      }
      .logo-img { height: 36px; width: 36px; }
    }

    @media (min-width: 992px) {
      .navbar-collapse { display: flex !important; flex-basis: auto; }
    }
  </style>
</head>

<body class="nav-md footer_fixed">
  <div class="container">
    <div class="main_container">
      <div class="top_nav">
        <nav class="navbar navbar-expand-lg navbar-dark px-3">
          <a class="navbar-brand" href="index.php">
            <img src="<?= $systemLogo ?>" alt="Logo" class="logo-img">
            <span class="brand-text"><?= $clientName ?></span>
          </a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ml-auto d-none">
              <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="#">About</a></li>
              <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
            </ul>
          </div>
        </nav>
      </div>

      <div class="content" role="main">