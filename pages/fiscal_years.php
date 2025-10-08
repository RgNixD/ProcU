<?php require_once 'sidebar.php'; ?>

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Fiscal Years</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Fiscal Years List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">New Fiscal Year</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="year">Year</label>
                    <input type="number" class="form-control" name="year" id="year" placeholder="Enter fiscal year (e.g. 2025)" min="1900" max="2099" oninput="if(this.value.length > 4) this.value = this.value.slice(0,4);" required>
                  </div>
                  <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary btn-sm" id="submit_button">
                    <i class="fa fa-save"></i> Save
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>


        <!-- UPDATE MODAL -->
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="UpdateForm" autocomplete="off" enctype="multipart/form-data">

              <input type="hidden" class="form-control" name="user_type" id="edit_user_type" value="Administrator"
                required>

              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">Update Fiscal Year</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="fiscal_year_id" id="edit_fiscal_year_id" required>
                  <div class="form-group">
                    <label for="edit_year">Year</label>
                    <input type="number" class="form-control" name="year" id="edit_year" placeholder="Enter fiscal year (e.g. 2025)" min="1900" max="2099" oninput="if(this.value.length > 4) this.value = this.value.slice(0,4);" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_start_date">Start Date</label>
                    <input type="date" class="form-control" name="start_date" id="edit_start_date" min="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_end_date">End Date</label>
                    <input type="date" class="form-control" name="end_date" id="edit_end_date" min="<?= date('Y-m-d') ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_is_current">Is Current?</label>
                    <select class="form-control" name="is_current" id="edit_is_current" required>
                      <option value="0">No</option>
                      <option value="1">Yes</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select class="form-control" name="status" id="edit_status" required>
                      <option value="1">Active</option>
                      <option value="0">Inactive</option>
                    </select>
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

        <div class="card-box bg-white table-responsive pb-2">
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"><i
              class="fa fa-plus"></i> Create FY</button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1"><i class="fa fa-trash"></i>
            Delete</button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr class="text-center">
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>YEAR</th>
                <th>START DATE</th>
                <th>END DATE</th>
                <th>IS ACTIVE</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $fiscal_years = $db->getAllFiscalYears();
              while ($row = $fiscal_years->fetch_assoc()) {
                $status_badge = ($row['status'] == 1)
                  ? '<span class="badge bg-success p-1 rounded text-light">Active</span>'
                  : '<span class="badge bg-secondary p-1 rounded text-light">Inactive</span>';

                $current_badge = ($row['is_current'] == 1)
                  ? '<span class="badge bg-success p-1 rounded text-light">Current</span>'
                  : '<span class="badge bg-secondary p-1 rounded text-light">No</span>';
              ?>
                <tr class="text-center">
                  <td><input type="checkbox" class="select-record d-block m-auto" value="<?= $row['fiscal_year_id'] ?>"></td>
                  <td><?= htmlspecialchars($row['year']); ?></td>
                  <td><?= date('M. d, Y', strtotime($row['start_date'])); ?></td>
                  <td><?= date('M. d, Y', strtotime($row['end_date'])); ?></td>
                  <td><?= $current_badge; ?></td>
                  <td><?= $status_badge; ?></td>
                  <td>
                    <button 
                      class="btn btn-success btn-sm edit-fiscalyear" title="Edit Fiscal Year"
                      data-fiscal-year-id="<?= $row['fiscal_year_id']; ?>"
                      data-year="<?= htmlspecialchars($row['year']); ?>"
                      data-start-date="<?= htmlspecialchars($row['start_date']); ?>"
                      data-end-date="<?= htmlspecialchars($row['end_date']); ?>"
                      data-is-current="<?= htmlspecialchars($row['is_current']); ?>"
                      data-status="<?= htmlspecialchars($row['status']); ?>">
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

    $('#SaveForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'AddFiscalYearForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "fiscal_years.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    
    // Populate update modal with candidate data
    $('#datatable').on('click', '.edit-fiscalyear', function () {
      const fiscal_year_id = $(this).data('fiscal-year-id');
      const year = $(this).data('year');
      const start_date = $(this).data('start-date');
      const end_date = $(this).data('end-date');
      const is_current = $(this).data('is-current');
      const status = $(this).data('status');

      $('#edit_fiscal_year_id').val(fiscal_year_id);
      $('#edit_year').val(year);
      $('#edit_start_date').val(start_date);
      $('#edit_end_date').val(end_date);
      $('#edit_is_current').val(is_current);
      $('#edit_status').val(status);

      // Show modal
      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateFiscalYearForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "fiscal_years.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
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
      // If any checkbox is unchecked, uncheck "select-all"
      if (!$(this).is(':checked')) {
        $('#select-all').prop('checked', false);
      } else {
        // If all checkboxes are checked, check the "select-all"
        var allChecked = $('.select-record').length === $('.select-record:checked').length;
        $('#select-all').prop('checked', allChecked);
      }
    });

    // Delete selected button
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
        deleteMultipleRecords("users", "user_id", selectedIDs, "users.php");
      });
    });


  });

</script>