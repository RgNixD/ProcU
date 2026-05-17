<?php
require_once '../php/auth_check.php';
if (!($canCreatePPMP)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';

// Fetch all notifications
$notifications = $db->getAllNotificationsByOffice($userId);
?>

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Notifications</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Notifications List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="card-box bg-white table-responsive pb-2">
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>MESSAGE</th>
                <th>CREATED AT</th>
                <th class="d-none">STATUS</th>
              </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notif): ?>
                  <tr class="<?= $notif['is_read'] == 0 ? 'table-warning' : '' ?>">
                    <td><?= htmlspecialchars($notif['message']) ?></td>
                    <td><?= date("M d, Y H:i", strtotime($notif['created_at'])) ?></td>
                    <td class="d-none"><?= $notif['is_read'] == 0 ? 'Unread' : 'Read' ?></td>
                  </tr>
                <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
