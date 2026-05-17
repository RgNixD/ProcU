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
        <h3>Modes of Procurement</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Modes of Procurement List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- Add Modal -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddProcModelLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddProcModelLabel">New Procurement Mode</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="proc_mode_name">Procurement Mode Name</label>
                    <input type="text" class="form-control" name="proc_mode_name" id="proc_mode_name"
                      placeholder="Enter name" required>
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
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="UpdateProcModeLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="UpdateForm" autocomplete="off">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="UpdateProcModeLabel">Update Procurement Mode</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="proc_mode_id" id="edit_proc_mode_id" required>
                  <div class="form-group">
                    <label for="edit_proc_mode_name">Procurement Mode Name</label>
                    <input type="text" class="form-control" name="proc_mode_name" id="edit_proc_mode_name"
                      placeholder="Enter name" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                      class="fa fa-times"></i> Cancel</button>
                  <button type="submit" class="btn btn-success btn-sm" id="edit_submit_button"><i
                      class="fa fa-edit"></i> Update</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Table -->
        <div class="card-box bg-white table-responsive pb-2">
          <div class="d-flex justify-content-end mr-2">
            <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"
              title="Create procurement mode"><i class="fa fa-plus"></i></button>
            <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete procurement mode"><i
                class="fa fa-trash"></i></button>
          </div>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>MODE OF PROCUREMENT</th>
                <th>DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $procModes = $db->getProcMode();
              while ($row = $procModes->fetch_assoc()) {
                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto"
                      name="record_<?= $row['proc_mode_id'] ?>" id="record_<?= $row['proc_mode_id'] ?>"
                      value="<?= $row['proc_mode_id']; ?>"></td>
                  <td><?= htmlspecialchars($row['proc_mode_name']); ?></td>
                  <td><?= date("M. d, Y", strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button class="btn btn-success btn-sm edit-item" title="Edit Item Name"
                      data-proc-mode-id="<?= $row['proc_mode_id']; ?>"
                      data-proc-mode-name="<?= htmlspecialchars($row['proc_mode_name']); ?>">
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
      formData.append('action', 'AddProcMode');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "mode-of-procurement.php");
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
      const proc_mode_ID = $(this).data('proc-mode-id');
      const proc_mode_name = $(this).data('proc-mode-name');

      $('#edit_proc_mode_id').val(proc_mode_ID);
      $('#edit_proc_mode_name').val(proc_mode_name);

      $('#UpdateModal').modal('show');
    });

    // Update
    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateProcMode');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "mode-of-procurement.php");
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
        deleteMultipleRecords("procurement_modes", "proc_mode_id", selectedIDs, "mode-of-procurement.php");
      });
    });

  });
</script>