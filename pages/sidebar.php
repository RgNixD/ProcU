<?php
  require_once '../php/classes.php';
  $db = new db_class();

  if (!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
    header("Location: ../index.php");
    exit();
  }

  $userId = $_SESSION['user_id'];
  $sessionToken = $_SESSION['session_token'];

  $sessionData = $db->validateSession($userId, $sessionToken);
  if (!$sessionData) {
    header("Location: ../php/logout.php");
    exit();
  }

  $user = $db->getUserById($userId);
  if (!$user || $user['is_active'] == 0) {
    header("Location: ../php/logout.php");
    exit();
  }

  $permissions = $db->getUserPermissions($userId);
  if (!$permissions) {
    header("Location: ../php/logout.php");
    exit();
  }

  $fullName = htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name']));

  // Determine role name based on permissions
  $badgeClass = ($permissions['can_manage_budget'] || $permissions['can_approve_ppmp']) ? "badge-primary" : "badge-secondary";

  $canViewReports = $permissions['can_view_reports'] == 1;
  $canCreatePPMP  = $permissions['can_create_ppmp'] == 1;
  $canApprovePPMP = $permissions['can_approve_ppmp'] == 1;
  $canManageBudget= $permissions['can_manage_budget'] == 1;

  $accessTypeName = "System User";

  if ($canApprovePPMP && $canManageBudget) {
      if ($canCreatePPMP) {
          $accessTypeName = "Administrator (Full Access)";
      } else {
          $accessTypeName = "Senior Management";
      }
  } elseif ($canApprovePPMP && $canViewReports) {
      $accessTypeName = "Procurement Head";
  } elseif ($canManageBudget) {
      $accessTypeName = "Budget Officer";
  } elseif ($canCreatePPMP) {
      $accessTypeName = "College Dean";
  }
  

  require_once 'header.php';
?>
<div class="col-md-3 left_col menu_fixed"><!-- FOR FIXED SIDEBAR -->
  <div class="left_col scroll-view">
    <div class="navbar nav_title">
      <a href="index.php" class="site_title">
        <img src="../<?= $systemLogo ?>" alt="..." class="img-circle mt-3" width="50"> 
        <span class="text-dark"><?= $accessTypeName ?> <br><p class="text-muted"><?= $user['email'] ?></p> </span> 
      </a>
    </div>
    
    <div class="clearfix"></div>

    <div class="profile clearfix d-none">
      <div class="profile_pic">
        <img src="../assets/img/user-profiles/<?= $user['profile'] ?>" alt="..." class="img-circle profile_img">
      </div>
      <div class="profile_info">
        <span>Welcome,</span>
        <h2><?= $fullName ?></h2>
        <span class="d-none"><?= $accessTypeName ?></span>
      </div>
    </div>
    
    <!-- sidebar menu -->
    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu mt-4">
      <div class="menu_section">
        
        <!-- User Management -->
        <h3 class="menu-title d-none" style="margin-left: -8px;">User Management</h3>
        <ul class="nav side-menu">

          <li class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
            <a href="index.php" class="text-dark"><i class="fa fa-tachometer"></i> DASHBOARD</a>
          </li>

          <?php if ($canApprovePPMP && $canViewReports): ?>
            <li class="<?= ($current_page == 'users.php') ? 'active' : '' ?>">
              <a href="users.php" class="text-dark"><i class="fa fa-users"></i> USERS</a>
            </li>

            <li class="<?= ($current_page == 'offices.php') ? 'active' : '' ?>">
              <a href="offices.php" class="text-dark"><i class="fa fa-building"></i> OFFICE</a>
            </li>


            <li class="<?= ($current_page == 'categories.php') ? 'active' : '' ?>">
              <a href="categories.php" class="text-dark"><i class="fa fa-tags"></i> CATEGORIES</a>
            </li>

            <li class="<?= ($current_page == 'fiscal_years.php') ? 'active' : '' ?>">
              <a href="fiscal_years.php" class="text-dark"><i class="fa fa-calendar"></i> FISCAL YEARS</a>
            </li>

            <li class="<?= ($current_page == 'admin.php') ? 'active' : '' ?>">
              <a href="#" class="text-dark"><i class="fa fa-file"></i> REVIEW SUBMISSIONS</a>
            </li>

            <li class="<?= ($current_page == 'approvals.php') ? 'active' : '' ?>">
              <a href="#" class="text-dark"><i class="fa fa-check"></i> CONSOLIDATE PPMP</a>
            </li>

            <li class="<?= ($current_page == 'reports.php') ? 'active' : '' ?>">
              <a href="#" class="text-dark"><i class="fa fa-file"></i> REVISE APP</a>
            </li>
          <?php endif; ?>

          

          <?php if ($canCreatePPMP): ?>
            <li class="<?= ($current_page == 'ppmp.php') ? 'active' : '' ?>">
              <a href="ppmp.php" class="text-dark"><i class="fa fa-folder"></i> MY PPMP</a>
            </li>
            <li class="d-none <?= ($current_page == 'create_ppmp.php') ? 'active' : '' ?>">
              <a href="create_ppmp.php" class="text-dark"><i class="fa fa-plus"></i> CREATE PPMP</a>
            </li>
            <li class="<?= ($current_page == 'notifications.php') ? 'active' : '' ?>">
              <a href="notifications.php" class="text-dark"><i class="fa fa-bell"></i> NOTIFICATIONS</a>
            </li>
          <?php endif; ?>

          <?php if ($canManageBudget): ?>
            <li class="<?= ($current_page == 'budget_allocations.php') ? 'active' : '' ?>">
              <a href="budget_allocations.php" class="text-dark"><i class="fa fa-calculator"></i> BUDGET ALLOCATIONS</a>
            </li>
            <li class="<?= ($current_page == 'adjustment_logs.php') ? 'active' : '' ?>">
              <a href="adjustment_logs.php" class="text-dark"><i class="fa fa-history"></i> ADJUSTMENT LOGS</a>
            </li>
          <?php endif; ?>
          
          <?php if (!$canCreatePPMP): ?>
            <li class="<?= ($current_page == 'reports.php') ? 'active' : '' ?>">
              <a href="reports.php" class="text-dark"><i class="fa fa-file"></i> REPORTS</a>
            </li>
          <?php endif; ?>
           
        </ul>
      </div>
      </ul>
    </div>
    <!-- /sidebar menu -->

  </div>
</div>

<!-- top navigation -->
<div class="top_nav">
  <div class="nav_menu text-light">
    <div class="nav toggle">
      <a id="menu_toggle"><i class="fa fa-bars"></i></a>
    </div>
    <nav class="nav navbar-nav">

      <ul class="navbar-right">
        <li class="nav-item dropdown open">
          <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
            <img src="../assets/img/user-profiles/<?= !empty($user['profile']) ? $user['profile'] : 'avatar.png' ?>" alt=""> 
            <span class="text-light">Welcome, <?= $user['first_name'] ?>!</span>   
          </a>
          <div class="dropdown-menu dropdown-usermenu pull-right mt-4" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="profile.php"><i class="fa fa-user"></i> Profile</a>
            <a class="dropdown-item" href="#" id="signOutButton"><i class="fa fa-sign-out pull-right"></i> Log Out</a>
          </div>
        </li>
      </ul>

    </nav>
  </div>
</div>
<!-- /top navigation -->