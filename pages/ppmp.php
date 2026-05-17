<?php
require_once '../php/auth_check.php';
if (!($canCreatePPMP)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';
function accounting_php($amount)
{
  $n = (float) $amount;
  $formatted = "₱" . number_format(abs($n), 2);

  return $n < 0 ? "({$formatted})" : $formatted;
}
?>
<style>
  th {
    vertical-align: middle;
    font-size: 0.75rem;
    padding: 5px;
  }

  td {
    height: 40px;
    vertical-align: top;
    font-size: 0.75rem;
  }

  .main-header {
    font-size: 0.85rem;
    text-transform: uppercase;
  }

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
        <h3>PPMP</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Final PPMP List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- VIEW MODAL -->
        <div class="modal fade" id="ViewPPMPModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">PPMP Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row mb-3" id="ppmpHeaderDetails">
                  <div class="col-md-4"><b>PPMP Code:</b> <span id="ppmp_code_modal">-</span></div>
                  <div class="col-md-4"><b>Office:</b> <span id="office_name_modal">-</span></div>
                  <div class="col-md-4"><b>Fiscal Year:</b> <span id="fiscal_year_modal">-</span></div>
                </div>
                <hr>
                <h6 class="text-white p-2 mb-0 mt-4" style="background-color: #a83232;">CURRENT ITEMS</h6>
                <div class="table-responsive">
                  <table class="table table-bordered" id="ppmpItemsTable">
                    <thead>
                      <tr>
                        <th colspan="5" class="text-center main-header bg-light align-middle">PROCUREMENT PROJECT
                          DETAILS
                        </th>
                        <th colspan="3" class="text-center main-header bg-light align-middle">PROJECTED TIMELINE
                          (MM/YYYY)
                        </th>
                        <th colspan="2" class="text-center main-header bg-light align-middle">FUNDING DETAILS</th>
                        <th rowspan="2" class="text-center align-middle">ATTACHED SUPPORTING DOCUMENTS</th>
                        <th rowspan="2" class="text-center align-middle">REMARKS</th>
                      </tr>
                      <tr>
                        <th class="text-center">General Description and Objective of the Project to be Procured</th>
                        <th class="text-center">Type of the Project to be Procured</th>
                        <th class="text-center">Quantity and Size of the Project to be Procured</th>
                        <th class="text-center">Recommended Mode of Procurement</th>
                        <th class="text-center">Pre-Procurement Conference</th>
                        <th class="text-center">Start of Procurement Activity</th>
                        <th class="text-center">End of Procurement Activity</th>
                        <th class="text-center">Expected Delivery / Implementation Period</th>
                        <th class="text-center">Source of Funds</th>
                        <th class="text-center">Estimated Budget per Item</th>
                      </tr>
                    </thead>
                    <tbody></tbody>

                    <tfoot>
                      <tr>
                        <td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td>
                        <td id="total_budget_modal" class="money fw-bold"></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="requestRevisionModal" tabindex="-1" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog">
            <form id="requestRevisionForm">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title">Request Revision</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="ppmp_id" id="rev_ppmp_id">
                  <div class="form-group">
                    <label>Reason for revision</label>
                    <textarea class="form-control" name="reason" required></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                      class="fa fa-times"></i> Cancel</button>
                  <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Submit
                    Request</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <div class="d-flex justify-content-end mb-3 mr-2">
            <button id="create-ppmp-btn" class="btn btn-sm btn-primary" title="Create PPMP">
              <i class="fa fa-plus"></i>
            </button>
          </div>

          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th class="text-center">FISCAL YEAR</th>
                <th>PPMP CODE</th>

                <?php if (!$canCreatePPMP): ?>
                  <th>OFFICE</th>
                <?php endif; ?>

                <?php if ($canApprovePPMP && $canViewReports): ?>
                  <th>SUBMITTED BY</th>
                <?php endif; ?>

                <th class="text-right">TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
                <th class="text-center">STATUS</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>

            <tbody>
              <?php
              $ppmpRecords = $db->getAllPPMPRecordsBySector($userId, false);

              while ($row = $ppmpRecords->fetch_assoc()):
                $fullName = ucwords(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

                $displayStatus = $row['display_status'] ?? $row['status'];
                $statusBadge = $row['status_badge'] ?? 'secondary';

                $canRequestRevision = (int) ($row['can_request_revision'] ?? 0) === 1;
                $canEdit = (int) ($row['can_edit'] ?? 0) === 1;

                $submittedDate = !empty($row['submitted_at'])
                  ? date("F d, Y", strtotime($row['submitted_at']))
                  : date("F d, Y", strtotime($row['created_at']));
                ?>
                <tr>
                  <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>

                  <td><?= htmlspecialchars($row['ppmp_code']); ?></td>

                  <?php if (!$canCreatePPMP): ?>
                    <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <?php endif; ?>

                  <?php if ($canApprovePPMP && $canViewReports): ?>
                    <td><?= htmlspecialchars($fullName); ?></td>
                  <?php endif; ?>

                  <td class="text-right"><?= accounting_php($row['total_amount']); ?></td>

                  <td><?= htmlspecialchars($submittedDate); ?></td>

                  <td class="text-center">
                    <span class="text-light badge bg-<?= htmlspecialchars($statusBadge); ?>">
                      <?= htmlspecialchars($displayStatus); ?>
                    </span>
                    <br>
                    <?php if (($row['status'] ?? '') === 'Rejected' && !empty($row['reject_reason'])): ?>
                      <button type="button" class="btn btn-sm btn-outline-danger mt-1 view-reject-reason"
                        data-reason="<?= htmlspecialchars($row['reject_reason'], ENT_QUOTES); ?>">
                        View Reason
                      </button>
                    <?php endif; ?>
                  </td>

                  <td class="text-center">
                    <button class="btn btn-primary btn-sm view-ppmp" data-ppmp-id="<?= (int) $row['ppmp_id']; ?>"
                      data-ppmp-version-id="<?= (int) $row['ppmp_version_id']; ?>" title="View Details">
                      <i class="fa fa-eye"></i>
                    </button>

                    <?php if ($canRequestRevision): ?>
                      <button class="btn btn-warning btn-sm request-revision-btn"
                        data-ppmp-id="<?= (int) $row['ppmp_id']; ?>"
                        data-ppmp-version-id="<?= (int) $row['ppmp_version_id']; ?>" title="Request Revision">
                        <i class="fa fa-undo"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($canEdit): ?>
                      <button class="btn btn-success btn-sm" title="Edit PPMP"
                        onclick="window.location.href='ppmp-update.php?ppmp_version_id=<?= (int) $row['ppmp_version_id']; ?>'">
                        <i class="fa fa-edit"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Draft PPMP List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="d-flex justify-content-end mb-3 mr-2">
          <button id="delete-selected" class="btn btn-sm btn-danger" title="Delete PPMP"> <i class="fa fa-trash"></i>
          </button>
        </div>
        <div class="card-box bg-white table-responsive pb-2">
          <table id="datatable2" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th class="text-center">FISCAL YEAR</th>
                <th>PPMP CODE</th>

                <?php if (!$canCreatePPMP): ?>
                  <th>OFFICE</th>
                <?php endif; ?>

                <?php if ($canApprovePPMP && $canViewReports): ?>
                  <th>SUBMITTED BY</th>
                <?php endif; ?>

                <th class="text-right">TOTAL AMOUNT</th>
                <th>DATE CREATED</th>
                <th class="text-center">STATUS</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>

            <tbody>
              <?php
              $draftRecords = $db->getAllPPMPRecordsBySector($userId, true);

              while ($row = $draftRecords->fetch_assoc()):
                $fullName = ucwords(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

                $displayStatus = $row['display_status'] ?? 'Draft';
                $statusBadge = $row['status_badge'] ?? 'secondary';
                ?>
                <tr>
                  <td>
                    <input type="checkbox" class="select-record d-block m-auto"
                      name="record_<?= (int) $row['ppmp_id']; ?>" id="record_<?= (int) $row['ppmp_id']; ?>"
                      value="<?= (int) $row['ppmp_id']; ?>">
                  </td>

                  <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>

                  <td><?= htmlspecialchars($row['ppmp_code']); ?></td>

                  <?php if (!$canCreatePPMP): ?>
                    <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <?php endif; ?>

                  <?php if ($canApprovePPMP && $canViewReports): ?>
                    <td><?= htmlspecialchars($fullName); ?></td>
                  <?php endif; ?>

                  <td class="text-right"><?= accounting_php($row['total_amount']); ?></td>

                  <td><?= date("F d, Y", strtotime($row['created_at'])); ?></td>

                  <td class="text-center">
                    <span class="badge bg-<?= htmlspecialchars($statusBadge); ?>">
                      <?= htmlspecialchars($displayStatus); ?>
                    </span>
                  </td>

                  <td class="text-center">
                    <button class="btn btn-primary btn-sm view-ppmp" data-ppmp-id="<?= (int) $row['ppmp_id']; ?>"
                      data-ppmp-version-id="<?= (int) $row['ppmp_version_id']; ?>" title="View Details">
                      <i class="fa fa-eye"></i>
                    </button>

                    <button class="btn btn-success btn-sm" title="Edit PPMP"
                      onclick="window.location.href='ppmp-update.php?ppmp_version_id=<?= (int) $row['ppmp_version_id']; ?>'">
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

<?php
$budgetCheck = $db->checkOfficeBudgetStatus($userId);
$budgetCheckJSON = json_encode($budgetCheck);
require_once 'footer.php';
?>

<script>
  $(document).ready(function () {

    $(document).on('click', '.view-reject-reason', function () {
      const reason = $(this).data('reason') || 'No rejection reason provided.';

      Swal.fire({
        title: 'Rejection Reason',
        text: reason,
        icon: 'info',
        confirmButtonText: 'Close'
      });
    });

    $(document).on('click', '.request-revision-btn', function () {
      let ppmpId = $(this).data('ppmp-id');
      $('#rev_ppmp_id').val(ppmpId);
      $('#requestRevisionModal').modal('show');
    });

    $('#requestRevisionForm').submit(function (e) {
      e.preventDefault();
      $.ajax({
        url: '../php/processes.php',
        type: 'POST',
        data: $(this).serialize() + '&action=RequestPPMPRevision',
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            $('#requestRevisionModal').modal('hide');
            showSweetAlert("Success!", res.message, "success", "ppmp.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", res.message, "error");
          }
        }
      });

    });

    function formatPesoAccounting(n) {
      const num = Number(n) || 0;
      const abs = Math.abs(num);

      const formatted = abs.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      const withPeso = `₱${formatted}`;
      return num < 0 ? `(${withPeso})` : withPeso;
    }

    const budgetCheckResult = <?= $budgetCheckJSON; ?>;
    $('#create-ppmp-btn').on('click', function () {
      if (budgetCheckResult.status === 'success') {
        window.location.href = 'ppmp-create.php';
        return;
      }

      if (
        budgetCheckResult.status === 'draft_exists' ||
        budgetCheckResult.status === 'editable_exists'
      ) {
        window.location.href = 'ppmp-update.php?ppmp_version_id=' + budgetCheckResult.ppmp_version_id;
        return;
      }

      Swal.fire({
        icon: 'warning',
        title: 'Warning',
        text: budgetCheckResult.message,
        confirmButtonText: 'OK',
        customClass: {
          confirmButton: 'btn btn-danger'
        }
      });
    });

    // VIEW PPMP DETAILS
    $('#datatable, #datatable2').on('click', '.view-ppmp', function () {
      loadPPMPDetails($(this).data("ppmp-id"));
    });
    $(document).on('click', '.view-all-attachments', function () {
      const files = String($(this).data('files') || '')
        .split('|')
        .map(file => file.trim())
        .filter(Boolean);

      const html = files.map(file => {
        const ext = file.split('.').pop().toLowerCase();

        let icon = 'fa-file';
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) icon = 'fa-image';
        else if (ext === 'pdf') icon = 'fa-file-pdf';
        else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word';
        else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel';

        return `
      <a href="../assets/ppmp_attachments/${file}"
        target="_blank"
        class="btn btn-light border d-flex align-items-center justify-content-between mb-2 p-2 w-100 text-left">
        <span>
          <i class="fa ${icon} me-2"></i>
          ${file}
        </span>
        <i class="fa fa-external-link"></i>
      </a>
    `;
      }).join('');

      Swal.fire({
        title: 'Attached Documents',
        html: `
      <div style="max-height:400px; overflow-y:auto; text-align:left;">
        ${html}
      </div>
    `,
        width: 650,
        confirmButtonText: 'Close'
      });
    });

    function renderAttachmentButtons(fileAttachment) {
      if (!fileAttachment) {
        return '<span class="text-muted">None</span>';
      }

      const files = fileAttachment
        .split(",")
        .map(file => file.trim())
        .filter(Boolean);

      const count = files.length;

      return `
    <button type="button"
      class="btn btn-sm btn-primary view-all-attachments"
      data-files="${files.join('|')}">
      <i class="fa fa-paperclip"></i> View All (${count})
    </button>
  `;
    }

    function loadPPMPDetails(ppmpId) {
      $.ajax({
        url: "../php/processes.php",
        type: "POST",
        data: { action: "getPPMPItemsById", ppmp_id: ppmpId },
        dataType: "json",
        success: function (response) {

          if (!response.success) {
            showSweetAlert("Error", response.message || "Unable to load PPMP items.", "error");
            return;
          }

          if (response.header) {
            $('#ppmp_code_modal').text(response.header.ppmp_code || '-');
            $('#office_name_modal').text(response.header.office_name || '-');
            $('#fiscal_year_modal').text(response.header.fiscal_year || '-');
          }

          const tbody = $("#ppmpItemsTable tbody");
          tbody.empty();

          if (response.items.length === 0) {
            tbody.append(`<tr><td colspan="12" class="text-center">No items found.</td></tr>`);
          } else {
            const tbody = $("#ppmpItemsTable tbody");
            tbody.empty();

            let grandTotal = 0;

            response.items.forEach(item => {
              const estimatedBudget = parseFloat(item.estimated_budget || 0);
              const totalCost = parseFloat(item.total_cost || 0);
              grandTotal += totalCost;

              const quantityAndSpecs = `${item.quantity || '-'}<br>${item.specifications || '-'}`;

              tbody.append(`
                <tr>
                  <td>${item.item_description || '-'}</td>
                  <td>${item.category_name || '-'}</td>
                  <td>${quantityAndSpecs}</td>
                  <td>${item.mode_of_procurement || '-'}</td>
                  <td>${item.pre_procurement_conference || '-'}</td>
                  <td>${item.procurement_start_date || '-'}</td>
                  <td>${item.bidding_date || '-'}</td>
                  <td>${item.contract_signing_date || '-'}</td>
                  <td>${item.source_of_funds || '-'}</td>
                  <td class="text-end money">${formatPesoAccounting(totalCost || estimatedBudget)}</td>
                  <td>${renderAttachmentButtons(item.file_attachment)}</td>
                  <td>${item.remarks || '-'}</td>
                </tr>
              `);
            });

            $("#total_budget_modal").text(formatPesoAccounting(grandTotal));

          }

          $("#ViewPPMPModal").modal("show");
        },
        error: function (xhr, status, error) {
          showSweetAlert("Server Error", error, "error");
        }
      });
    }
    // END VIEW PPMP DETAILS

    // Multiple Deletion
    $('#select-all').on('click', function () {
      var isChecked = $(this).is(':checked');
      $('.select-record:not(:disabled)').prop('checked', isChecked);
    });

    $('#datatable2').on('change', '.select-record', function () {
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
        deleteMultipleRecords("ppmp", "ppmp_id", selectedIDs, "ppmp.php");
      });
    });

  });

</script>