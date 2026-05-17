<?php
require_once '../php/auth_check.php';

$isProcurementHead = ($canApprovePPMP && $canViewReports && $canManageBudget);
$isBudgetOfficer = $canManageBudget && !$canApprovePPMP;

if (!$isProcurementHead && !$isBudgetOfficer) {
  header("Location: 404.php");
  exit();
}

require_once 'sidebar.php';

$currentFY = $db->getCurrentFiscalYear();
$currentFiscalYearId = $currentFY['fiscal_year_id'] ?? '';
$fiscalYears = $db->getFiscalYears();

function accounting_php($amount)
{
  $n = (float) $amount;
  $f = number_format(abs($n), 2);
  return $n < 0 ? "(₱{$f})" : "₱{$f}";
}

$totalAnnualBudget = 0;
$totalAllocatedBudget = 0;
$totalAvailableBalance = 0;

if (!empty($currentFiscalYearId)) {
  $summary = $db->getBudgetSummaryByFiscalYear($currentFiscalYearId);

  if ($summary) {
    $totalAnnualBudget = (float) $summary['annual_budget'];
    $totalAllocatedBudget = (float) $summary['total_allocated'];
    $totalAvailableBalance = (float) $summary['available_balance'];
  }
}
?>
<style>
  .money {
    text-align: right;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
  }

  .table-summary-container {
    width: calc(100% - 30px);
    margin: 0 auto 15px auto;
  }

  .budget-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }

  .budget-card {
    background: #fff;
    border: 1px solid #e3e8ef;
    border-radius: 8px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: .2s ease;
  }

  .budget-card:hover {
    border-color: #cfd8e3;
    box-shadow: 0 3px 10px rgba(0, 0, 0, .04);
  }

  .budget-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    flex-shrink: 0;
  }

  .budget-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #7b8794;
    letter-spacing: .7px;
  }

  .budget-value {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-top: 2px;
    line-height: 1.1;
  }

  @media (max-width: 768px) {
    .budget-summary {
      grid-template-columns: 1fr;
    }

    .table-summary-container {
      width: 100%;
    }
  }
</style>
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
                        echo '<option value="' . htmlspecialchars($office['office_id']) . '">' . htmlspecialchars($office['office_name']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="text" class="form-control" name="amount_display" id="amount" placeholder="Enter amount"
                      inputmode="decimal" required>
                    <input type="hidden" name="amount" id="amount_value">
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
                        echo '<option value="' . htmlspecialchars($office['office_id']) . '">' . htmlspecialchars($office['office_name']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="edit_amount">Amount</label>
                    <input type="text" class="form-control" name="amount_display" id="edit_amount"
                      placeholder="Enter amount" inputmode="decimal" required>
                    <input type="hidden" name="amount" id="edit_amount_value">
                  </div>
                  <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select class="form-control" name="status" id="edit_status" required>
                      <option value="Pending">Pending</option>
                      <option value="Approved">Approved</option>
                      <option value="Disapproved">Disapproved</option>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-success btn-sm" id="edit_submit_button">
                    <i class="fa fa-edit"></i> Update
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <div class="d-flex justify-content-end align-items-center flex-wrap mb-2">
            <div class="d-flex align-items-center mr-3 mb-2">
              <label for="fiscalYearFilter" class="mb-0 mr-2 font-weight-bold">Fiscal Year</label>
              <select id="fiscalYearFilter" class="form-control" style="width: 160px;">
                <?php if ($fiscalYears && $fiscalYears->num_rows > 0): ?>
                  <?php while ($fy = $fiscalYears->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($fy['year']); ?>"
                      data-fiscal-year-id="<?= htmlspecialchars($fy['fiscal_year_id']); ?>"
                      <?= ($fy['fiscal_year_id'] == $currentFiscalYearId) ? 'selected' : ''; ?>>
                      <?= htmlspecialchars($fy['year']); ?>
                    </option>
                  <?php endwhile; ?>
                <?php endif; ?>
              </select>
            </div>

            <?php if ($accessTypeName === "Budget Officer"): ?>
              <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-2 mr-2"
                title="Create Budget Allocation">
                <i class="fa fa-plus"></i>
              </button>
              <button id="delete-selected" class="btn btn-sm btn-danger mb-2" title="Delete Budget Allocation">
                <i class="fa fa-trash"></i>
              </button>
            <?php endif; ?>
          </div>

          <div class="table-summary-container">
            <div class="budget-summary mb-3">

              <div class="budget-card">
                <div class="budget-icon bg-dark">
                  <i class="fa fa-bank"></i>
                </div>
                <div>
                  <div class="budget-label">Annual Budget</div>
                  <div class="budget-value" id="annualBudgetValue">
                    <?= accounting_php($totalAnnualBudget); ?>
                  </div>
                </div>
              </div>

              <div class="budget-card">
                <div class="budget-icon bg-primary">
                  <i class="fa fa-wallet"></i>
                </div>
                <div>
                  <div class="budget-label">Total Allocated</div>
                  <div class="budget-value" id="totalAllocatedBudget">
                    <?= accounting_php($totalAllocatedBudget); ?>
                  </div>
                </div>
              </div>

              <div class="budget-card">
                <div class="budget-icon bg-success">
                  <i class="fa fa-coins"></i>
                </div>
                <div>
                  <div class="budget-label">Available Balance</div>
                  <div class="budget-value" id="availableBalance">
                    <?= accounting_php($totalAvailableBalance); ?>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-light">
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>OFFICE NAME</th>
                <th class="d-none">OFFICE CODE</th>
                <th>OFFICE HEAD</th>
                <th class="d-none">FISCAL YEAR</th>
                <th class="text-right">ALLOTMENT BUDGET</th>
                <th class="text-right">ALLOCATED</th>
                <th class="text-right">BALANCE</th>
                <th>STATUS</th>
                <th class="d-none">DATE CREATED</th>
                <?php if ($accessTypeName !== "Sectors"): ?>
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
                  case 'pending':
                    $statusClass = 'bg-warning';
                    break;
                  case 'approved':
                    $statusClass = 'bg-success';
                    break;
                  case 'disapproved':
                    $statusClass = 'bg-secondary';
                    break;
                  default:
                    $statusClass = 'bg-light';
                    break;
                }

                $isApproved = ($row['status'] == 'Approved');
                $disabledAttribute = $isApproved ? 'disabled' : '';
                ?>
                <tr>
                  <td>
                    <input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['allocation_id'] ?>"
                      id="record_<?= $row['allocation_id'] ?>" value="<?= $row['allocation_id']; ?>" <?= $disabledAttribute ?>>
                  </td>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td class="d-none"><?= htmlspecialchars($row['office_code']); ?></td>
                  <td><?= htmlspecialchars($row['head_name']); ?></td>
                  <td class="d-none"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <td class="money"><?= accounting_php($row['allocated_amount']); ?></td>
                  <td class="money"><?= accounting_php($spent); ?></td>
                  <td class="money"><?= accounting_php($row['remaining_amount']); ?></td>
                  <td><span class="badge <?= $statusClass; ?> text-light"><?= ucfirst($row['status']); ?></span></td>
                  <td class="d-none"><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>

                  <?php if ($accessTypeName !== "Sectors"): ?>
                    <td class="text-center">
                      <button class="btn btn-success btn-sm edit-allocation" title="Edit Allocation"
                        data-allocation-id="<?= $row['allocation_id']; ?>" data-office-id="<?= $row['office_id']; ?>"
                        data-fiscal-year-id="<?= $row['fiscal_year_id']; ?>"
                        data-allocated-amount="<?= $row['allocated_amount']; ?>"
                        data-remaining-amount="<?= $row['remaining_amount']; ?>"
                        data-status="<?= htmlspecialchars($row['status']); ?>">
                        <i class="fa fa-edit"></i>
                      </button>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

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

  function syncInput(displaySelector, hiddenSelector, force2Decimals = false) {
    const displayEl = document.querySelector(displaySelector);
    const hiddenEl = document.querySelector(hiddenSelector);
    if (!displayEl || !hiddenEl) return;

    const n = parseAccountingToNumber(displayEl.value);
    displayEl.value = formatAccountingDisplay(n, force2Decimals);
    hiddenEl.value = (Number(n) || 0).toFixed(2);
  }

  $(document).ready(function () {
    let table = $('#datatable').DataTable();

    function updateBudgetSummary() {
      const fiscalYearId = $('#fiscalYearFilter option:selected').data('fiscal-year-id');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: {
          action: 'GetBudgetSummaryByFiscalYear',
          fiscal_year_id: fiscalYearId
        },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            $('#annualBudgetValue').text(formatAccountingDisplay(response.annual_budget, true));
            $('#totalAllocatedBudget').text(formatAccountingDisplay(response.total_allocated, true));
            $('#availableBalance').text(formatAccountingDisplay(response.available_balance, true));
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
        }
      });
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      if (settings.nTable.id !== 'datatable') {
        return true;
      }

      const selectedYear = $('#fiscalYearFilter').val();
      const fiscalYear = data[4] || '';

      if (!selectedYear || fiscalYear === selectedYear) {
        return true;
      }

      return false;
    });

    table.draw();
    updateBudgetSummary();

    $('#fiscalYearFilter').on('change', function () {
      table.draw();
      updateBudgetSummary();
    });



    $(document).on('input', '#amount', function () {
      const n = parseAccountingToNumber(this.value);
      $('#amount_value').val((Number(n) || 0).toFixed(2));
    });

    $(document).on('blur', '#amount', function () {
      syncInput('#amount', '#amount_value', true);
    });

    $(document).on('focus', '#amount', function () {
      this.select();
    });

    $(document).on('input', '#edit_amount', function () {
      const n = parseAccountingToNumber(this.value);
      $('#edit_amount_value').val((Number(n) || 0).toFixed(2));
    });

    $(document).on('blur', '#edit_amount', function () {
      syncInput('#edit_amount', '#edit_amount_value', true);
    });

    $(document).on('focus', '#edit_amount', function () {
      this.select();
    });

    const form = document.querySelector('form');
    const budgetInput = document.getElementById('amount');

    if (form && budgetInput) {
      $(form).on('submit', function () {
        let cleanValue = budgetInput.value.replace(/,/g, '');
        budgetInput.value = cleanValue;
      });
    }

    $('#SaveForm').submit(function (e) {
      e.preventDefault();

      syncInput('#amount', '#amount_value', true);

      var formData = new FormData(this);
      formData.append('action', 'AddBudgetAllocation');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "budget_allocations.php");
          } else {
            Swal.fire({
              title: "Error",
              html: response.message,
              icon: "error"
            });
          }
        },
        error: function (xhr) {
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
      $('#edit_status').val(status);
      $('#edit_amount').val(formatAccountingDisplay(allocated_amount, true));
      $('#edit_amount_value').val(Number(allocated_amount || 0).toFixed(2));

      $('#UpdateModal').modal('show');

      setTimeout(() => {
        $('#edit_office_id').val(office_id).trigger('change.select2');
      }, 150);
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();

      syncInput('#edit_amount', '#edit_amount_value', true);

      var formData = new FormData(this);
      formData.append('action', 'UpdateBudgetAllocation');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "budget_allocations.php");
          } else {
            Swal.fire({
              title: "Error",
              html: response.message,
              icon: "error"
            });
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
        }
      });
    });

    $('#select-all').on('click', function () {
      var isChecked = $(this).is(':checked');
      $('.select-record:not(:disabled)').prop('checked', isChecked);
    });

    $('#datatable').on('change', '.select-record', function () {
      var totalEnabled = $('.select-record:not(:disabled)').length;
      var checkedEnabled = $('.select-record:checked:not(:disabled)').length;
      var allChecked = (totalEnabled > 0 && totalEnabled === checkedEnabled);

      $('#select-all').prop('checked', allChecked);
    });

    $('#delete-selected').on('click', function () {
      var selectedIDs = [];
      $('.select-record:checked:not(:disabled)').each(function () {
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