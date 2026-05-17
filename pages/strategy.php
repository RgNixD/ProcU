<?php
require_once '../php/auth_check.php';
if (!($canApprovePPMP && $canViewReports && $canManageBudget)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';
?>

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Procurement Strategies</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Procurement Strategies List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- Add Modal -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddProcStrategyLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddProcStrategyLabel">New Procurement Strategy</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="proc_strat_name">Procurement Strategy Name</label>
                    <input type="text" class="form-control" name="proc_strat_name" id="proc_strat_name" placeholder="Enter procurement strategy name" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary btn-sm" id="submit_button">
                    <i class="fa fa-save"></i> Submit
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Update Modal -->
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="UpdateProcStrategyLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="UpdateForm" autocomplete="off">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="UpdateProcStrategyLabel">Update Procurement Strategy</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="proc_strat_ID" id="edit_proc_strat_ID" required>
                  <div class="form-group">
                    <label for="edit_proc_strat_name">Procurement Strategy Name</label>
                    <input type="text" class="form-control" name="proc_strat_name" id="edit_proc_strat_name" placeholder="Enter procurement strategy name" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                  <button type="submit" class="btn btn-success btn-sm" id="edit_submit_button"><i class="fa fa-edit"></i> Update</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div class="card-box bg-white table-responsive pb-2">
          <div class="d-flex justify-content-end mr-2">
            <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"
              title="Create procurement strategy"><i class="fa fa-plus"></i></button>
            <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete procurement strategy"><i class="fa fa-trash"></i></button>
          </div>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>PROCUREMENT STRATEGY</th>
                <th>DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $procStrats = $db->getProcStrategy();
              while ($row = $procStrats->fetch_assoc()) {
                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['proc_strat_ID'] ?>" id="record_<?= $row['proc_strat_ID'] ?>" value="<?= $row['proc_strat_ID']; ?>"></td>
                  <td><?= htmlspecialchars($row['proc_strat_name']); ?></td>
                  <td><?= date("M. d, Y", strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button class="btn btn-success btn-sm edit-item" title="Edit Item Name"
                      data-proc-strat-id="<?= $row['proc_strat_ID']; ?>"
                      data-proc-strat-name="<?= htmlspecialchars($row['proc_strat_name']); ?>">
                      <i class="fa fa-edit"></i> 
                    </button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- /page content -->

<?php require_once 'footer.php'; ?>
<script>
  $(document).ready(function () {

    // Add new
    $('#SaveForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'AddProcStrategyForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "strategy.php");
          } else {
            showSweetAlert("Error", response.message, "error");
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    // Edit modal
    $('#datatable').on('click', '.edit-item', function () {
      const proc_strat_ID = $(this).data('proc-strat-id');
      const proc_strat_name = $(this).data('proc-strat-name');

      $('#edit_proc_strat_ID').val(proc_strat_ID);
      $('#edit_proc_strat_name').val(proc_strat_name);

      $('#UpdateModal').modal('show');
    });

    // Update
    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateProcStrategyForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "strategy.php");
          } else {
            showSweetAlert("Error", response.message, "error");
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    // MULTIPLE DELETION
    $('#select-all').on('click', function () {
      var isChecked = $(this).is(':checked');
      $('.select-record').prop('checked', isChecked);
    });

    $('#datatable').on('change', '.select-record', function () {
      if (!$(this).is(':checked')) {
        $('#select-all').prop('checked', false);
      } else {
        var allChecked = $('.select-record').length === $('.select-record:checked').length;
        $('#select-all').prop('checked', allChecked);
      }
    });

    $('#delete-selected').on('click', function () {
      var selectedIDs = [];
      $('.select-record:checked').each(function () {
        selectedIDs.push($(this).val());
      });

      if (selectedIDs.length === 0) {
        Swal.fire("No selection", "Please select at least one record to delete.", "info");
        return;
      }

      confirmMultipleDeletion(selectedIDs.length, function () {
        deleteMultipleRecords("procurement_strategy", "proc_strat_ID", selectedIDs, "strategy.php");
      });
    });

  });
</script>
