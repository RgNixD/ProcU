<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../<?= $systemLogo ?>" type="image/ico" />
    <title><?= htmlspecialchars($pageName) ?></title>
    <!-- Bootstrap -->
    <link href="cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <!-- FullCalendar -->
    <link href="../vendors/fullcalendar/dist/fullcalendar.min.css" rel="stylesheet">
    <link href="../vendors/fullcalendar/dist/fullcalendar.print.css" rel="stylesheet" media="print">
    <!-- Select2 -->
    <link href="../vendors/select2/dist/css/select2.min.css" rel="stylesheet">
    <!-- Switchery -->
    <link href="../vendors/switchery/dist/switchery.min.css" rel="stylesheet">
    <!-- starrr -->
    <link href="../vendors/starrr/dist/starrr.css" rel="stylesheet">
    <!-- PNotify -->
    <link href="../vendors/pnotify/dist/pnotify.css" rel="stylesheet">
    <link href="../vendors/pnotify/dist/pnotify.buttons.css" rel="stylesheet">
    <link href="../vendors/pnotify/dist/pnotify.nonblock.css" rel="stylesheet">
    <!-- Datatables -->
    <link href="../vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.css" rel="stylesheet">
    <!-- <link href="../build/css/custom.min.css" rel="stylesheet"> -->

    <style>
      .left_col {
        height:100vh; 
        overflow-y:auto;
      }
      .left_col.scroll-view {
        background-color: #e6e6e6;
        min-height: 100vh;
      }
      .navbar.nav_title {
        background-color: #e6e6e6;
      }
      a.site_title p.text-muted {
        margin-left:56px; 
        margin-top: -47px; 
        font-size: 13px; 
        font-weight: 400;
      }
      ul.navbar-right li a img {
        background-color: #fff;
        border: 2px solid #fff;
      }
      .nav_menu {
        background: linear-gradient(to right,
          #b54d4d 0%,   
          #c35757 40%, 
          #a83232 100% 
        ) !important;
      }
      .swal2-confirm-custom, .swal2-cancel-custom {
          border: none !important; 
          box-shadow: none !important;
          outline: none !important;
      }
      .swal2-confirm-custom {
          border: 1px solid #fff !important; 
          background-color: #800000 !important; 
          color: #fff !important;
      }
      .swal2-cancel-custom {
          border: 1px solid #ccc !important;
          background-color: #555 !important; 
          color: #fff !important;
      }
      .user-profile.dropdown-toggle::after {
        color: #fff !important;
      }
      /* Profile page */
      #imageConfirmButtons button {
        margin: 0 5px;
      }
      select.readonly-select {
        pointer-events: none;
        background-color: #e9ecef;
      }
      @media (min-width: 768px) {
        .border-md-right {
          border-right: 1px solid #dee2e6;
        }
      }
      @media (max-width: 576px) {
        #myTab {
          margin-top: 1rem;
          margin-bottom: 1.5rem;
        }
        #myTab li {
          margin-bottom: 0.5rem;
        }
      }
      /* End Profile page */
      button, .btn {
        background-color: #a83232;
        border: 1px solid #a83232;  
        color: #fff; 
        transition: all 0.3s ease;
      }

      button:hover, .btn:hover {
        background-color: #fff;       
        border: 1px solid #a83232;  
        color: #a83232;             
      }

      button:active,
      .btn:focus,
      .btn:active {
        outline: none !important; 
        background-color: #a83232 !important; 
        border-color: #a83232 !important; 
        color: #fff !important;              
        box-shadow: none !important; 
        border: 1px solid #a83232;       
      }

    </style>
  </head>

  <body class="nav-md footer_fixed">
    <div class="container body" style="background-color: #F7F7F7 !important;">
      <div class="main_container">
        