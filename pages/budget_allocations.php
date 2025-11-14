<?php
  require_once '../php/auth_check.php';

  $isProcurementHead = ($canApprovePPMP && $canViewReports && $canManageBudget);
  $isBudgetOfficer = $canManageBudget && !$canApprovePPMP;

  if (!$isProcurementHead && !$isBudgetOfficer) {
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
        <h3>Budget Allocations</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Budget Allocations List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddLabel">New Budget Allocation</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="office_id">Office Name</label>
                    <select class="form-control" name="office_id" id="office_id" required>
                      <option value="" disabled selected>Select Office Name</option>
                      <?php
                        $offices = $db->getAllOffices();
                        while ($office = $offices->fetch_assoc()) {
                            $fullname = htmlspecialchars($office['office_name']);
                            echo '<option value="' . htmlspecialchars($office['office_id']) . '">' . $office['office_name'] . '</option>';
                        }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="text" class="form-control" name="amount" id="amount" placeholder="Enter amount" inputmode="decimal" pattern="^\d+(\.\d{0,2})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '') .replace(/(\..*)\./g, '$1') .replace(/^0+(\d)/, '$1');" required>
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

        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="UpdateForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">Update Budget Allocation</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="allocation_id" id="edit_allocation_id" required>
                  <div class="form-group">
                    <label for="edit_office_id">Office Name</label>
                    <select class="form-control" name="office_id" id="edit_office_id" required>
                      <option value="" disabled selected>Select Office Name</option>
                      <?php
                        $offices = $db->getAllOffices();
                        while ($office = $offices->fetch_assoc()) {
                            $fullname = htmlspecialchars($office['office_name']);
                            echo '<option value="' . htmlspecialchars($office['office_id']) . '">' . $office['office_name'] . '</option>';
                        }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="edit_amount">Amount</label>
                    <input type="text" class="form-control" name="amount" id="edit_amount" placeholder="Enter amount" inputmode="decimal" pattern="^\d+(\.\d{0,2})?$" oninput="this.value = this.value.replace(/[^0-9.]/g, '') .replace(/(\..*)\./g, '$1') .replace(/^0+(\d)/, '$1');" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select class="form-control" name="status" id="edit_status" required>
                      <option value="Pending">Pending</option>
                      <option value="Approved">Approved</option>
                      <option value="Dispproved">Disapproved</option>
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
          <?php if ($accessTypeName === "Budget Officer"): ?>
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1" title="Create Budget Allocation"><i
              class="fa fa-plus"></i></button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete Budget Allocation"><i class="fa fa-trash"></i></button>
          <?php endif; ?>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-light">
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>OFFICE NAME</th>
                <th class="d-none">OFFICE CODE</th>
                <th>OFFICE HEAD</th>
                <th class="d-none">FISCAL YEAR</th>
                <th>ALLOTMENT BUDGET</th>
                <th>ALLOCATED</th>
                <th>BALANCE</th>
                <th>STATUS</th>
                <th class="d-none">DATE CREATED</th>
                <?php if ($accessTypeName === "Procurement Head"): ?>
                <th class="text-center">ACTIONS</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              $allocations = $db->getAllBudgetAllocations();
                while ($row = $allocations->fetch_assoc()):
                  $spent = $row['allocated_amount'] - $row['remaining_amount'];

                  $statusClass = '';
                  switch (strtolower($row['status'])) {
                    case 'pending': $statusClass = 'bg-warning'; break;
                    case 'approved': $statusClass = 'bg-success'; break;
                    case 'dispproved': $statusClass = 'bg-secondary'; break;
                    default: $statusClass = 'bg-light'; break;
                  }
              ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['allocation_id'] ?>" id="record_<?= $row['allocation_id'] ?>" value="<?= $row['allocation_id']; ?>"></td>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td class="d-none"><?= htmlspecialchars($row['office_code']); ?></td>
                  <td><?= htmlspecialchars($row['head_name']); ?></td>
                  <td class="d-none"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <td class="text-end">₱<?= number_format($row['allocated_amount'], 2); ?></td>
                  <td class="text-end">₱<?= number_format($spent, 2); ?></td>
                  <td class="text-end">₱<?= number_format($row['remaining_amount'], 2); ?></td>
                  <td><span class="badge <?= $statusClass; ?> text-light"><?= ucfirst($row['status']); ?></span></td>
                  <td class="d-none"><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>
                  <?php if ($accessTypeName === "Procurement Head"): ?>
                  <td class="text-center">
                    <button 
                      class="btn btn-success btn-sm edit-allocation" 
                      title="Edit Allocation"
                      data-allocation-id="<?= $row['allocation_id']; ?>"
                      data-office-id="<?= $row['office_id']; ?>"
                      data-fiscal-year-id="<?= $row['fiscal_year_id']; ?>"
                      data-allocated-amount="<?= $row['allocated_amount']; ?>"
                      data-remaining-amount="<?= $row['remaining_amount']; ?>"
                      data-status="<?= $row['status']; ?>"
                    >
                      <i class="fa fa-edit"></i>
                    </button>
                  </td>
                  <?php endif; ?>
                </tr>
              <?php 
                endwhile;
              ?>
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

  $('#office_id').select2({
    dropdownParent: $('#AddNew'),
    width: '100%',
    placeholder: 'Select Office Name'
  });

   $('#edit_office_id').select2({
    dropdownParent: $('#UpdateModal'),
    width: '100%',
    placeholder: 'Select Office Name'
  });

  $(document).ready(function () {

    $('#SaveForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'AddBudgetAllocation');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "budget_allocations.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    $('#datatable').on('click', '.edit-allocation', function () {
      const allocation_id = $(this).data('allocation-id');
      const office_id = $(this).data('office-id');
      const allocated_amount = $(this).data('allocated-amount');
      const status = $(this).data('status');

      $('#edit_allocation_id').val(allocation_id);
      $('#edit_amount').val(allocated_amount);
      $('#edit_status').val(status);

      $('#UpdateModal').modal('show');

      setTimeout(() => {
        $('#edit_office_id').val(office_id).trigger('change.select2'); 
      }, 150);
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateBudgetAllocation');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "budget_allocations.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("budget_allocation", "allocation_id", selectedIDs, "budget_allocations.php");
      });
    });


  });

</script>