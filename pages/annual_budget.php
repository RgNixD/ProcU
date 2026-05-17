<?php
require_once '../php/auth_check.php';

$isProcurementHead = ($canApprovePPMP && $canViewReports && $canManageBudget);
$isBudgetOfficer = $canManageBudget && !$canApprovePPMP;

if (!$isProcurementHead && !$isBudgetOfficer) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';

$current_fy = $db->getCurrentFiscalYear(true);
if ($current_fy) {
  $fy_year = htmlspecialchars($current_fy['year']);
  $start_date_raw = $current_fy['start_date'];
  $end_date_raw = $current_fy['end_date'];
  $fy_id = htmlspecialchars($current_fy['fiscal_year_id']);

  $start_date_formatted = date('M d, Y', strtotime($start_date_raw));
  $end_date_formatted = date('M d, Y', strtotime($end_date_raw));

  $fy_period_display = $start_date_formatted . ' – ' . $end_date_formatted;
}
?>
<style>
  .money {
    text-align: right;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
  }
</style>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Annual Budget</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Annual Budget List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddLabel">New Annual Budget Allocation</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <?php if ($current_fy): ?>
                    <div class="form-group">
                      <div class="form-group">
                        <label for="fy_year">Fiscal Year</label>
                        <input type="text" class="form-control" id="fy_year" value="<?php echo $fy_year; ?>" readonly>
                      </div>
                      <div class="form-group">
                        <label for="fy_period">Fiscal Period</label>
                        <input type="text" class="form-control" id="fy_period" value="<?php echo $fy_period_display; ?>"
                          readonly>
                      </div>
                      <input type="hidden" name="fiscal_year_id" value="<?php echo $fy_id; ?>">
                      <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date_raw); ?>">
                      <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date_raw); ?>">
                    </div>
                    <div class="form-group">
                      <label for="total_budget">Amount</label>
                      <input type="text" class="form-control" name="total_budget_display" id="total_budget"
                        placeholder="Enter amount" inputmode="decimal" required>
                      <input type="hidden" name="total_budget" id="total_budget_value">
                    </div>
                  <?php else: ?>
                    <p class="text-danger text-center">Current fiscal year data not available.</p>
                  <?php endif; ?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary btn-sm" id="submit_button" <?= !$current_fy ? 'disabled' : ''; ?>>
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
                  <h5 class="modal-title" id="exampleModalLabel">Update Annual Budget Allocation</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="annual_budget_id" id="edit_annual_budget_id" required>
                  <?php if ($current_fy): ?>
                    <div class="form-group">
                      <div class="form-group">
                        <label for="fy_year">Fiscal Year</label>
                        <input type="text" class="form-control" id="fy_year" value="<?php echo $fy_year; ?>" readonly>
                      </div>
                      <div class="form-group">
                        <label for="fy_period">Fiscal Period</label>
                        <input type="text" class="form-control" id="fy_period" value="<?php echo $fy_period_display; ?>"
                          readonly>
                      </div>
                      <input type="hidden" name="fiscal_year_id" value="<?php echo $fy_id; ?>">
                      <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date_raw); ?>">
                      <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date_raw); ?>">
                    </div>
                    <div class="form-group">
                      <label for="edit_total_budget">Amount</label>
                      <input type="text" class="form-control" name="total_budget_display" id="edit_total_budget"
                        placeholder="Enter amount" inputmode="decimal" required>
                      <input type="hidden" name="total_budget" id="edit_total_budget_value">
                    </div>
                  <?php else: ?>
                    <p class="text-danger text-center">Current fiscal year data not available.</p>
                  <?php endif; ?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                      class="fa fa-times"></i> Cancel</button>
                  <button type="submit" class="btn btn-success btn-sm" id="edit_submit_button" <?= !$current_fy ? 'disabled' : ''; ?>><i class="fa fa-edit"></i> Update</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="modal fade" id="DetailsModal" tabindex="-1" role="dialog" aria-labelledby="DetailsModalLabel"
          aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="DetailsModalLabel">Annual Budget Allocation Details - <span
                    id="details_fiscal_year"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <p class="font-weight-bold mb-0">Total Allotment Budget:</p>
                    <h4 class="text-primary" id="details_total_budget"></h4>
                  </div>
                  <div class="col-md-6">
                    <p class="font-weight-bold mb-0">Remaining Balance (Based on **Approved**):</p>
                    <h4 class="text-success" id="details_remaining_balance"></h4>
                  </div>
                </div>

                <hr>

                <h6 class="font-weight-bold mt-4 mb-3">Departmental Allocations</h6>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover table-sm">
                    <thead class="bg-light">
                      <tr>
                        <th>OFFICE NAME</th>
                        <th>OFFICE HEAD</th>
                        <th class="text-right">ALLOTMENT BUDGET</th>
                        <th class="text-right">BALANCE</th>
                        <th class="d-none">STATUS</th>
                        <th>CREATED AT</th>
                      </tr>
                    </thead>
                    <tbody id="department_allocations_body">
                      <tr>
                        <td colspan="6" class="text-center">Loading allocations...</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <div class="d-flex justify-content-end mr-2">
            <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"
              title="Create Budget Allocation"><i class="fa fa-plus"></i></button>
            <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete Budget Allocation"><i
                class="fa fa-trash"></i></button>
          </div>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-light">
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>FISCAL YEAR</th>
                <th class="text-right">ALLOTMENT BUDGET</th>
                <th class="text-right">BALANCE</th>
                <th>SUBMITTED BY</th>
                <th>LAST UPDATED BY</th>
                <th>DATE ADDED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              function accounting_php($amount)
              {
                $n = (float) $amount;
                $f = number_format(abs($n), 2);
                return $n < 0 ? "(₱{$f})" : "₱{$f}";
              }
              $annual_budgets = $db->getAnnualBudgets();
              while ($row = $annual_budgets->fetch_assoc()):

                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto"
                      name="record_<?= $row['annual_budget_id'] ?>" id="record_<?= $row['annual_budget_id'] ?>"
                      value="<?= $row['annual_budget_id']; ?>"></td>
                  <td><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <td class="money text-end"><?= accounting_php($row['total_budget_amount']); ?></td>
                  <td class="money text-end"><?= accounting_php($row['remaining_budget_amount']); ?></td>

                  <td><?= htmlspecialchars($row['submitted_by_name'] ?: 'N/A'); ?></td>
                  <td><?= htmlspecialchars($row['updated_by_name'] ?: 'N/A'); ?></td>
                  <td><?= date('M. d, Y', strtotime($row['date_added'])); ?></td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm view-budget" title="View Budget Details"
                      data-id="<?= $row['annual_budget_id']; ?>">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-success btn-sm edit-annual-budget" title="Edit Budget"
                      data-id="<?= $row['annual_budget_id']; ?>" data-amount="<?= $row['total_budget_amount']; ?>">
                      <i class="fa fa-edit"></i>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
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

  function parseAccountingToNumber(str) {
    if (!str) return 0;
    let s = String(str).trim();
    let isNeg = /^\(.*\)$/.test(s);
    s = s.replace(/[()]/g, '');
    s = s.replace(/₱/g, '').replace(/,/g, '').replace(/\s+/g, '');
    if (s.startsWith('-')) {
      isNeg = true;
      s = s.slice(1);
    }
    s = s.replace(/[^\d.]/g, '');
    const parts = s.split('.');
    const intPart = parts[0] || '0';
    const decPart = parts.slice(1).join('').slice(0, 2);
    const numStr = decPart.length ? `${intPart}.${decPart}` : intPart;
    const n = Number(numStr) || 0;
    return isNeg ? -n : n;
  }

  function formatAccountingDisplay(n, force2Decimals = false) {
    const num = Number(n) || 0;
    const abs = Math.abs(num);
    const formatted = abs.toLocaleString('en-PH', {
      minimumFractionDigits: force2Decimals ? 2 : 0,
      maximumFractionDigits: 2
    });
    const withPeso = `₱${formatted}`;
    return num < 0 ? `(${withPeso})` : withPeso;
  }

  function syncBudgetInput(force2Decimals = false) {
    const displayEl = document.getElementById('total_budget');
    const valueEl = document.getElementById('total_budget_value');
    if (!displayEl || !valueEl) return;
    const n = parseAccountingToNumber(displayEl.value);
    displayEl.value = formatAccountingDisplay(n, force2Decimals);
    valueEl.value = (Number(n) || 0).toFixed(2);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const displayEl = document.getElementById('total_budget');
    if (!displayEl) return;

    displayEl.addEventListener('input', () => {
      const n = parseAccountingToNumber(displayEl.value);
      document.getElementById('total_budget_value').value = (Number(n) || 0).toFixed(2);
    });

    displayEl.addEventListener('blur', () => syncBudgetInput(true));

    displayEl.addEventListener('focus', () => displayEl.select());

    if (displayEl.value.trim().length > 0) {
      syncBudgetInput(true);
    } else {
      document.getElementById('total_budget_value').value = "0.00";
    }

    const form = document.getElementById('SaveForm');
    if (form) {
      form.addEventListener('submit', () => syncBudgetInput(true));
    }
  });

  function syncEditBudgetInput(force2Decimals = false) {
    const displayEl = document.getElementById('edit_total_budget');
    const valueEl = document.getElementById('edit_total_budget_value');
    if (!displayEl || !valueEl) return;
    const n = parseAccountingToNumber(displayEl.value);
    displayEl.value = formatAccountingDisplay(n, force2Decimals);
    valueEl.value = (Number(n) || 0).toFixed(2);
  }



  $(document).ready(function () {

    $(document).on('input', '#edit_total_budget', function () {
      const n = parseAccountingToNumber(this.value);
      $('#edit_total_budget_value').val((Number(n) || 0).toFixed(2));
    });

    $(document).on('blur', '#edit_total_budget', function () {
      syncEditBudgetInput(true);
    });

    $(document).on('focus', '#edit_total_budget', function () {
      this.select();
    });

    function syncEditBudgetInput(force2Decimals = false) {
      const displayEl = document.getElementById('edit_total_budget');
      const valueEl = document.getElementById('edit_total_budget_value');
      if (!displayEl || !valueEl) return;

      const n = parseAccountingToNumber(displayEl.value);
      displayEl.value = formatAccountingDisplay(n, force2Decimals);
      valueEl.value = (Number(n) || 0).toFixed(2);
    }

    $('#SaveForm').submit(function (e) {
      e.preventDefault();

      syncBudgetInput(true);

      var formData = new FormData(this);
      formData.append('action', 'AddAnnualBudget');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "annual_budget.php");
          } else {
            showSweetAlert("Error", response.message, "error");
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
        }
      });
    });


    $('#datatable').on('click', '.edit-annual-budget', function () {
      const annual_budget_id = $(this).data('id');
      const total_budget_amount = $(this).data('amount');
      const $editAmountInput = $('#edit_total_budget');
      $('#edit_annual_budget_id').val(annual_budget_id);

      $editAmountInput.val(formatAccountingDisplay(total_budget_amount, true));
      $('#edit_total_budget_value').val(Number(total_budget_amount || 0).toFixed(2));


      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();

      syncEditBudgetInput(true);

      var formData = new FormData(this);
      formData.append('action', 'UpdateAnnualBudget');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "annual_budget.php");
          } else {
            showSweetAlert("Error", response.message, "error");
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
        }
      });
    });

    $('#datatable').on('click', '.view-budget', function () {
      const annual_budget_id = $(this).data('id');
      const fiscal_year_text = $(this).closest('tr').find('td:eq(1)').text();

      $('#details_fiscal_year').text(fiscal_year_text);
      $('#details_total_budget').html('<i class="fa fa-spinner fa-spin"></i> Loading...');
      $('#details_remaining_balance').html('<i class="fa fa-spinner fa-spin"></i> Loading...');
      $('#department_allocations_body').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Fetching details...</td></tr>'); // Updated colspan to 6

      $('#DetailsModal').modal('show');

      $.ajax({
        url: '../php/processes.php',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'get_annual_budget_allocations',
          annual_budget_id: annual_budget_id
        },
        success: function (response) {
          if (response.error) {
            $('#details_total_budget').text('N/A');
            $('#details_remaining_balance').text('N/A');
            $('#department_allocations_body').html('<tr><td colspan="6" class="text-danger text-center">' + response.error + '</td></tr>');
            return;
          }

          $('#details_total_budget').text(formatAccountingDisplay(response.total_budget_amount, true));
          $('#details_remaining_balance').text(formatAccountingDisplay(response.remaining_budget_amount, true));

          const allocations = response.allocations;
          let tableRows = '';

          if (allocations.length === 0) {
            tableRows = '<tr><td colspan="6" class="text-center text-muted">No budget allocations found for this fiscal year.</td></tr>';
          } else {
            allocations.forEach(function (alloc) {
              let statusClass = '';
              switch (alloc.status.toLowerCase()) {
                case 'approved': statusClass = 'bg-success'; break;
                case 'submitted': statusClass = 'bg-info'; break;
                case 'draft': statusClass = 'bg-secondary'; break;
                case 'pending': statusClass = 'bg-warning'; break;
                case 'rejected': statusClass = 'bg-danger'; break;
                default: statusClass = 'bg-light text-dark'; break;
              }

              const allocated_amount_formatted = formatAccountingDisplay(alloc.allocated_amount, true);
              const office_remaining_formatted = formatAccountingDisplay(alloc.office_remaining_amount, true);

              const office_head_name = alloc.office_head_name || 'N/A';

              const date_allocated = new Date(alloc.date_allocated).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

              tableRows += `
                        <tr>
                            <td>${alloc.office_name}</td>
                            <td>${office_head_name}</td> <!-- New Column -->
                            <td class="text-right">${allocated_amount_formatted}</td>
                            <td class="text-right text-primary">${office_remaining_formatted}</td> <!-- New Column -->
                            <td class="d-none"><span class="badge ${statusClass} text-light">${alloc.status.charAt(0).toUpperCase() + alloc.status.slice(1)}</span></td>
                            <td>${date_allocated}</td>
                        </tr>
                    `;
            });
          }

          $('#department_allocations_body').html(tableRows);

        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", error);
          $('#details_total_budget').text('Error');
          $('#details_remaining_balance').text('Error');
          $('#department_allocations_body').html('<tr><td colspan="6" class="text-danger text-center">Failed to load data. Check console for details.</td></tr>');
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
        deleteMultipleRecords("annual_budget", "annual_budget_id", selectedIDs, "annual_budget.php");
      });
    });

  });

</script>