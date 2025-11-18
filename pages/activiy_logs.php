<?php
  require_once '../php/auth_check.php';
  if (!($canApprovePPMP && $canViewReports && $canManageBudget)) {
    header("Location: 404.php");
    exit();
  }
  require_once 'sidebar.php';
?>

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Activity Logs</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Activity Logs List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="card-box bg-white table-responsive pb-2">
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>USER</th>
                <th>USER TYPE</th>
                <th>ACTION</th>
                <th>TABLE AFFECTED</th>
                <th class="d-none">RECORD ID</th>
                <th>DATE & TIME</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $activity_logs = $db->getActivityLogs();
              while ($row = $activity_logs->fetch_assoc()) {
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['user_name'] ?: 'System/Unknown User'); ?></td>
                  <td><?= htmlspecialchars($row['access_type']); ?></td>
                  <td><?= htmlspecialchars($row['action']); ?></td>
                  <td><?= htmlspecialchars($row['table_name']); ?></td>
                  <td class="d-none"><?= htmlspecialchars($row['record_id'] ?: 'N/A'); ?></td>
                  <td><?= date("M. d, Y h:i A", strtotime($row['created_at'])); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>