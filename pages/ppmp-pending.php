<?php
require_once '../php/auth_check.php';
if (!($canApprovePPMP && $canViewReports && $canManageBudget)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';

$currentFY = $db->getCurrentFiscalYear();
$currentFiscalYearId = $currentFY['fiscal_year_id'] ?? null;
$currentFiscalYearLabel = $currentFY['year'] ?? 'No Fiscal Year';

?>
<style>
  .money {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
  }

  .btn-xs {
    padding: 2px 6px;
    font-size: 0.7rem;
  }
</style>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>PPMP Review</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <div class="d-flex justify-content-between align-items-center w-100">
          <h2 class="mb-0">Pending PPMPs for Review</h2>
          <span class="text-dark mr-3" style="font-size: 0.95rem;">
            Fiscal Year: <strong><?= htmlspecialchars($currentFiscalYearLabel); ?></strong>
          </span>
        </div>
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
                        <th class="text-center">Estimated Budget per Item (PhP)</th>
                      </tr>
                    </thead>
                    <tbody></tbody>

                    <tfoot>
                      <tr>
                        <td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td>
                        <td id="total_budget_modal" class="fw-bold money"></td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <hr>
                <div class="row" id="reviewer_section">
                  <div class="col-md-12">
                    <label for="reviewer_notes" class="fw-bold">Reviewer Notes (Optional):</label>
                    <textarea id="reviewer_notes" class="form-control mb-3" rows="3"></textarea>
                  </div>
                  <div class="col-md-12 d-flex justify-content-between align-items-center">
                    <div>
                      <input type="hidden" id="current_ppmp_id">
                      <span class="fw-bold">Current Status:</span> <span id="current_status_badge" class="badge"></span>
                    </div>
                    <div id="review_actions">
                      <button class="btn btn-warning btn-sm" id="return_ppmp_btn" data-status="Returned">
                        <i class="fa fa-undo"></i> Return PPMP
                      </button>
                      <button class="btn btn-danger btn-sm" id="reject_ppmp_btn" data-status="Rejected">
                        <i class="fa fa-times"></i> Disapprove
                      </button>
                      <button class="btn btn-success btn-sm" id="approve_ppmp_btn" data-status="Approved">
                        <i class="fa fa-check"></i> Approve
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>OFFICE NAME</th>
                <th>OFFICE HEAD</th>
                <th class="text-right">TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
                <th class="text-center">STATUS</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              function formatAccountingPHP($amount)
              {
                $n = (float) $amount;
                $abs = number_format(abs($n), 2);
                if ($n < 0)
                  return "(₱{$abs})";
                return "₱{$abs}";
              }
              $ppmpRecords = $db->getAllPPMPRecordsForProcHead("Pending", $currentFiscalYearId);
              while ($row = $ppmpRecords->fetch_assoc()) {
                $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
                $totalAmount = formatAccountingPHP($row['total_amount']);
                $submissionDate = !empty($row['submitted_at'])
                  ? date('M d, Y', strtotime($row['submitted_at']))
                  : date('M d, Y', strtotime($row['created_at']));
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td><?= htmlspecialchars($fullName); ?></td>
                  <td class="text-end fw-bold money"><?= $totalAmount; ?></td>
                  <td><?= $submissionDate; ?></td>
                  <td class="text-center">
                    <span class="text-light badge bg-<?= htmlspecialchars($row['status_badge']); ?>">
                      <?= htmlspecialchars($row['display_status']); ?>
                    </span>

                    <?php if ($row['revision_request_status'] === 'Requested'): ?>
                      <div class="mt-2">
                        <span class="badge bg-primary">Revision Request Pending</span>
                      </div>

                      <div class="mt-2 d-flex justify-content-center gap-1">
                        <button class="btn btn-success btn-xs approve-revision" data-ppmp-id="<?= (int) $row['ppmp_id']; ?>"
                          data-reason="<?= htmlspecialchars($row['revision_request_reason'] ?? '', ENT_QUOTES); ?>">
                          <i class="fa fa-check"></i> Approve
                        </button>

                        <button class="btn btn-danger btn-xs reject-revision" data-ppmp-id="<?= (int) $row['ppmp_id']; ?>"
                          data-reason="<?= htmlspecialchars($row['revision_request_reason'] ?? '', ENT_QUOTES); ?>">
                          <i class="fa fa-times"></i> Reject
                        </button>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm" title="View PPMP Submission"
                      onclick="window.location.href='ppmp-review.php?ppmp_id=<?= (int) $row['ppmp_id']; ?>'">
                      <i class="fa fa-eye"></i>
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

<?php require_once 'footer.php'; ?>

<script>
  $(document).ready(function () {

    $(document).on('click', '.approve-revision', function () {
      const ppmpId = $(this).data('ppmp-id');
      const reason = $(this).data('reason') || 'No reason provided.';

      Swal.fire({
        title: 'Approve Revision Request?',
        html: `
      <div class="text-left">
        <p><strong>Sector reason:</strong></p>
        <div style="background:#f8f9fa;border:1px solid #ddd;padding:10px;border-radius:6px;max-height:180px;overflow:auto;">
          ${reason}
        </div>
        <p class="mt-3 mb-0">Sector will be allowed to edit this PPMP.</p>
      </div>
    `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        confirmButtonColor: '#28a745'
      }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
          url: '../php/processes.php',
          type: 'POST',
          dataType: 'json',
          data: { action: 'ApprovePPMPRevisionRequest', ppmp_id: ppmpId },
          success: function (response) {
            if (response.success) {
              showSweetAlert('Approved', response.message, 'success', 'ppmp-pending.php');
            } else {
              showSweetAlert('Error', response.message, 'error');
            }
          }
        });
      });
    });

    $(document).on('click', '.reject-revision', function () {
      const ppmpId = $(this).data('ppmp-id');
      const reason = $(this).data('reason') || 'No reason provided.';

      Swal.fire({
        title: 'Reject Revision Request?',
        html: `
      <div class="text-left">
        <p><strong>Sector reason:</strong></p>
        <div style="background:#f8f9fa;border:1px solid #ddd;padding:10px;border-radius:6px;max-height:180px;overflow:auto;">
          ${reason}
        </div>
        <p class="mt-3 mb-0">Sector will not be allowed to edit this PPMP.</p>
      </div>
    `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc3545'
      }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
          url: '../php/processes.php',
          type: 'POST',
          dataType: 'json',
          data: { action: 'RejectPPMPRevisionRequest', ppmp_id: ppmpId },
          success: function (response) {
            if (response.success) {
              showSweetAlert('Rejected', response.message, 'success', 'ppmp-pending.php');
            } else {
              showSweetAlert('Error', response.message, 'error');
            }
          }
        });
      });
    });

  });
</script>