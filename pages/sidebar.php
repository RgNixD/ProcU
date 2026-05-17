<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

$canViewReports = $permissions['can_view_reports'] == 1;
$canCreatePPMP = $permissions['can_create_ppmp'] == 1;
$canApprovePPMP = $permissions['can_approve_ppmp'] == 1;
$canManageBudget = $permissions['can_manage_budget'] == 1;

$accessTypeName = "System User";
$remainingBudget = 0;
$latestPPMPStatus = null;

$unreadNotifications = [];
$unreadCount = 0;

if ($canApprovePPMP && $canViewReports && $canManageBudget) {
  $accessTypeName = "BAC Sec Head";
} elseif ($canManageBudget) {
  $accessTypeName = "Budget Officer";
} elseif ($canCreatePPMP) {

  $officeId = $db->getOfficeIdByHead($userId);
  $accessTypeName = $db->getOfficeNamesByIds($officeId) ?: 'Sector';
  $remainingBudget = $db->getRemainingBudgetByUser($userId);

  $unreadNotifications = $db->getUnreadNotificationsByOffice($userId);
  $unreadCount = count($unreadNotifications);
}

$currentFY = $db->getCurrentFiscalYear(true);
$fiscalYearText = $currentFY ? $currentFY['year'] : 'N/A';

require_once 'header.php';
?>

<div class="col-md-3 left_col menu_fixed"><!-- FOR FIXED SIDEBAR -->
  <div class="left_col scroll-view">
    <div class="navbar nav_title">
      <a href="index.php" class="site_title">
        <img src="../<?= $systemLogo ?>" alt="..." class="img-circle mt-3" width="50">
        <span class="text-dark"><?= $accessTypeName ?> <br>
          <p class="text-muted"><?= $user['email'] ?></p>
        </span>
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

        <h3 class="menu-title d-none" style="margin-left: -8px;">User Management</h3>
        <ul class="nav side-menu">

          <li class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
            <a href="index.php" class="text-dark"><i class="fa fa-tachometer"></i> <span
                class="menu-text">DASHBOARD</span></a>
          </li>

          <!-- BAC Sec Head -->
          <?php if ($canApprovePPMP && $canViewReports && $canManageBudget): ?>

            <li class="<?= ($current_page == 'users.php') ? 'active' : '' ?>">
              <a href="users.php" class="text-dark"><i class="fa fa-users"></i> USERS</a>
            </li>

            <li class="<?= ($current_page == 'offices.php') ? 'active' : '' ?>">
              <a href="offices.php" class="text-dark"><i class="fa fa-building"></i> OFFICE</a>
            </li>

            <?php
            $category_pages = ['categories.php', 'sub-categories.php', 'item-name.php', 'bid-category.php', 'strategy.php', 'mode-of-procurement.php'];
            $budget_pages = ['annual_budget.php', 'budget_allocations.php'];

            $ppmp_pages = ['ppmp-pending.php', 'ppmp-approved.php', 'ppmp-consolidated.php', 'ppmp-review.php'];
            $is_ppmp_open = in_array($current_page, $ppmp_pages);
            ?>

            <!-- Categories -->
            <li class="<?= in_array($current_page, $category_pages) ? 'active' : '' ?>">
              <a class="text-dark"> <i class="fa fa-tags"></i> PROC. SETUP<span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu submenu-flyout">
                <li class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">
                  <a href="categories.php" class="text-dark">MAIN CATEGORIES</a>
                </li>
                <li class="<?= $current_page == 'sub-categories.php' ? 'active' : '' ?>">
                  <a href="sub-categories.php" class="text-dark">SUB-CATEGORIES</a>
                </li>
                <li class="<?= $current_page == 'item-name.php' ? 'active' : '' ?>">
                  <a href="item-name.php" class="text-dark">ITEM NAME</a>
                </li>
                <li class="<?= $current_page == 'bid-category.php' ? 'active' : '' ?>">
                  <a href="bid-category.php" class="text-dark">BIDDING CATEGORY</a>
                </li>
                <li class="<?= $current_page == 'strategy.php' ? 'active' : '' ?>">
                  <a href="strategy.php" class="text-dark">PROC. STRATEGY</a>
                </li>
                <li class="<?= $current_page == 'mode-of-procurement.php' ? 'active' : '' ?>">
                  <a href="mode-of-procurement.php" class="text-dark">MODE OF PROCUREMENT</a>
                </li>
              </ul>
            </li>

            <li class="<?= ($current_page == 'fiscal_years.php') ? 'active' : '' ?>">
              <a href="fiscal_years.php" class="text-dark"><i class="fa fa-calendar"></i> FISCAL YEARS</a>
            </li>

            <!-- Budget -->
            <li class="<?= in_array($current_page, $budget_pages) ? 'active' : '' ?>">
              <a class="text-dark"><i class="fa fa-calculator"></i> BUDGET<span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu submenu-flyout">
                <li class="<?= $current_page == 'annual_budget.php' ? 'active' : '' ?>">
                  <a href="annual_budget.php" class="text-dark">ANNUAL BUDGET</a>
                </li>
                <li class="<?= $current_page == 'budget_allocations.php' ? 'active' : '' ?>">
                  <a href="budget_allocations.php" class="text-dark">BUDGET ALLOCATIONS</a>
                </li>
              </ul>
            </li>
            <!-- PPMP -->
            <li class="<?= $is_ppmp_open ? 'active' : '' ?>">
              <a class="text-dark">
                <i class="fa fa-check"></i> PPMP
                <span class="fa fa-chevron-down"></span>
              </a>

              <ul class="nav child_menu submenu-flyout"
                style="<?= $is_ppmp_open ? 'display: block;' : 'display: none;' ?>">
                <li class="<?= $current_page == 'ppmp-pending.php' ? 'current-page active' : '' ?>">
                  <a href="ppmp-pending.php" class="text-dark">PENDING PPMP</a>
                </li>

                <li class="<?= $current_page == 'ppmp-approved.php' ? 'current-page active' : '' ?>">
                  <a href="ppmp-approved.php" class="text-dark">READY FOR CONSOLIDATION</a>
                </li>

                <li class="<?= $current_page == 'ppmp-consolidated.php' ? 'current-page active' : '' ?>">
                  <a href="ppmp-consolidated.php" class="text-dark">CONSOLIDATED PPMP</a>
                </li>
              </ul>
            </li>

            <li class="<?= ($current_page == 'app.php') ? 'active' : '' ?>">
              <a href="app.php" class="text-dark"><i class="fa fa-check"></i> APP</a>
            </li>

          <?php endif; ?>
          <!-- End BAC Sec Head -->

          <!-- SECTOR -->
          <?php if ($canCreatePPMP): ?>
            <li class="<?= ($current_page == 'ppmp.php') ? 'active' : '' ?>">
              <a href="ppmp.php" class="text-dark"><i class="fa fa-folder"></i> PPMP</a>
            </li>
            <li class="d-none <?= ($current_page == 'adjustment_logs.php') ? 'active' : '' ?>">
              <a href="#" class="text-dark"><i class="fa fa-history"></i> ADJUSTMENT LOGS</a>
            </li>
          <?php endif; ?>
          <!-- END SECTOR -->

          <!-- FINANCE OFFICER -->
          <?php if ($canManageBudget): ?>
            <?php if (!$canApprovePPMP && !$canViewReports): ?>

              <li
                class="<?= ($current_page == 'annual_budget.php' || $current_page == 'budget_allocations.php') ? 'active' : '' ?>">
                <a class="text-dark">
                  <i class="fa fa-calculator"></i> BUDGET <span class="fa fa-chevron-down"></span>
                </a>
                <ul class="nav child_menu submenu-flyout">
                  <li>
                    <a href="annual_budget.php" class="text-dark"> ANNUAL BUDGET</a>
                  </li>
                  <li>
                    <a href="budget_allocations.php" class="text-dark"> BUDGET ALLOCATIONS</a>
                  </li>
                </ul>
              </li>
            <?php endif; ?>
          <?php endif; ?>
          <!-- END FINANCE OFFICER -->

          <?php if (!$canCreatePPMP): ?>
            <li class="<?= ($current_page == 'reports.php') ? 'active' : '' ?>">
              <a href="reports.php" class="text-dark"><i class="fa fa-file"></i> REPORTS</a>
            </li>
          <?php endif; ?>

          <!-- BAC Sec Head -->
          <?php if ($canApprovePPMP && $canViewReports && $canManageBudget): ?>
            <li class="<?= ($current_page == 'activity_logs.php') ? 'active' : '' ?>">
              <a href="activity_logs.php" class="text-dark"><i class="fa fa-users"></i> ACTIVITY LOGS</a>
            </li>
          <?php endif; ?>
          <!-- End BAC Sec Head -->

        </ul>
      </div>
      </ul>
    </div>
  </div>
</div>

<div class="top_nav">
  <div class="nav_menu text-light">
    <div class="nav toggle">
      <a id="menu_toggle"><i class="fa fa-bars"></i></a>
    </div>
    <nav class="nav navbar-nav">

      <ul class="navbar-right">
        <li class="nav-item dropdown open" style="padding-left: 15px;">
          <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown"
            data-toggle="dropdown" aria-expanded="false">
            <img src="../assets/img/user-profiles/<?= !empty($user['profile']) ? $user['profile'] : 'avatar.png' ?>"
              alt="">
            <span class="text-light">Welcome, <?= $user['first_name'] ?>!</span>
          </a>
          <div class="dropdown-menu dropdown-usermenu pull-right mt-4" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="profile.php"><i class="fa fa-user"></i> Profile</a>
            <a class="dropdown-item" href="#" id="signOutButton"><i class="fa fa-sign-out pull-right"></i> Log Out</a>
          </div>
        </li>

        <?php if ($canCreatePPMP): ?>
          <li role="presentation" class="nav-item dropdown open">
            <a href="javascript:;" class="dropdown-toggle info-number" id="navbarDropdown1" data-toggle="dropdown"
              aria-expanded="false">
              <i class="fa fa-envelope-o text-light"></i>
              <span class="badge bg-green" id="notifBadge"><?= $unreadCount ?></span>
            </a>
            <ul class="dropdown-menu list-unstyled msg_list" role="menu" aria-labelledby="navbarDropdown1"
              style="max-height:300px; overflow-y:auto;">
              <?php if (!empty($unreadNotifications)): ?>
                <?php foreach ($unreadNotifications as $notif): ?>
                  <li class="nav-item">
                    <a href="javascript:;" class="dropdown-item view-notification" data-id="<?= $notif['notification_id'] ?>">
                      <span class="message"><?= htmlspecialchars($notif['message']) ?></span>
                      <br>
                      <small class="text-muted"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></small>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li class="nav-item text-center">
                  <span class="dropdown-item text-muted">No new notifications</span>
                </li>
              <?php endif; ?>
              <li class="nav-item text-center">
                <div class="text-center">
                  <a class="dropdown-item" href="notifications.php"><strong>See All Notifications</strong> <i
                      class="fa fa-angle-right"></i></a>
                </div>
              </li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

    </nav>
  </div>
</div>

<div class="modal fade" id="NotificationModal" tabindex="-1" aria-labelledby="NotificationModalLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="NotificationModalLabel">Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
        </button>
      </div>
      <div class="modal-body" id="notifMessage">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i>
          Close</button>
      </div>
    </div>
  </div>
</div>