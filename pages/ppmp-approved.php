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
</style>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Approved PPMP</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <div class="d-flex justify-content-between align-items-center w-100">
          <h2 class="mb-0">PPMPs Ready for Consolidation</h2>
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
                        <th class="text-center">Pre-Procurement Conference (Yes/No)</th>
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
          <div class="d-flex justify-content-end align-items-center flex-wrap mb-3 mr-2">
            <button type="button" id="btnConsolidate" class="btn btn-sm btn-primary mb-2"
              title="Consolidate Approved PPMPs for Current Fiscal Year"><i class="fa fa-clone"></i> Consolidate
            </button>
          </div>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>OFFICE NAME</th>
                <th>OFFICE HEAD</th>
                <th class="text-right">TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              function formatAccountingPHP($amount)
              {
                $n = (float) $amount;
                $abs = number_format(abs($n), 2);
                return ($n < 0) ? "(₱{$abs})" : "₱{$abs}";
              }
              $ppmpRecords = $db->getAllPPMPRecordsForProcHead("Approved", $currentFiscalYearId);
              while ($row = $ppmpRecords->fetch_assoc()) {
                $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
                $totalAmount = formatAccountingPHP($row['total_amount']);
                $submissionDate = date('M d, Y', strtotime($row['created_at']));
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td><?= htmlspecialchars($fullName); ?></td>
                  <td class="text-end fw-bold money"><?= $totalAmount; ?></td>
                  <td><?= $submissionDate; ?></td>
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm view-ppmp" data-ppmp-id="<?= $row['ppmp_id']; ?>"
                      data-ppmp-status="<?= htmlspecialchars($row['status']); ?>" title="View Details">
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
<!-- /page content -->

<?php require_once 'footer.php'; ?>
<script>
  $(document).ready(function () {

    function formatAccounting(amount) {
      const n = Number(amount);
      if (isNaN(n)) return amount;

      const abs = Math.abs(n).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      return n < 0 ? `(₱${abs})` : `₱${abs}`;
    }

    function autoMoney(value) {
      if (value === null || value === undefined) return value;
      const raw = String(value).trim();
      if (raw === "") return raw;

      const cleaned = raw.replace(/,/g, "");
      if (!/^-?\d+(\.\d+)?$/.test(cleaned)) return value;

      const n = Number(cleaned);
      const hasDecimal = cleaned.includes(".");
      if (hasDecimal || Math.abs(n) >= 1000) return formatAccounting(n);

      return value;
    }

    function runConsolidationAjax() {
      let formData = new FormData();
      formData.append('action', 'ConsolidatePPMP');

      $.ajax({
        url: "../php/processes.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (response) {
          if (response && response.success) {
            showSweetAlert("Success!", response.message, "success", "ppmp-approved.php");
          } else {
            showSweetAlert("Error", (response && response.message) ? response.message : "Consolidation failed.", "error");
          }
        },
        error: function (xhr, status, error) {
          showSweetAlert("Server Error", error, "error");
        }
      });
    }

    $('#btnConsolidate').on('click', function () {

      $.ajax({
        url: "../php/processes.php",
        type: "POST",
        dataType: "json",
        data: { action: "CheckMissingApprovedPPMP" },
        success: function (response) {

          if (!response || response.success === false) {

            if (response && response.type === "no_ppmp") {
              Swal.fire({
                title: "Cannot Consolidate",
                text: response.message || "There are no approved PPMPs available for consolidation.",
                icon: "error",
                confirmButtonText: "OK"
              });
              return;
            }

            if (response && response.type === "unused_budget") {
              Swal.fire({
                title: "Cannot Consolidate",
                html: `
                  <div style="text-align:left; font-size:14px;">
                    The annual budget still has a large unutilized balance.<br><br>
                    <b>Remaining:</b> ₱${response.unused}<br>
                    <b>Allowed maximum:</b> ₱${response.threshold}<br><br>
                    Please adjust allocations or update PPMP approvals before consolidating.
                  </div>
                `,
                icon: "warning",
                iconColor: "#e67e22",
                width: "550px",
                showConfirmButton: true,
                confirmButtonText: "OK",
                allowOutsideClick: false
              });
              return;
            }

            showSweetAlert("Error", (response && response.message) ? response.message : "Cannot proceed.", "error");
            return;
          }

          const warnings = response.warning || {};
          const hasMissingPPMP = warnings.warning_type === "missing_ppmp";
          const missingOffices = warnings.missing_offices || [];
          const hasUnusedBudget = !!warnings.unused_budget;
          const unused = hasUnusedBudget ? warnings.unused_budget.unused : null;
          const threshold = hasUnusedBudget ? warnings.unused_budget.threshold : null;

          if (hasMissingPPMP || hasUnusedBudget) {

            const missingHtml = hasMissingPPMP
              ? `
                <br>
                <b>Offices without approved PPMP:</b>
                <ul style="padding-left:20px; line-height:1.5;">
                  ${missingOffices.map(o => `<li>${o}</li>`).join("")}
                </ul>
              `
              : "";

            const budgetHtml = hasUnusedBudget
              ? `
                <div style="margin-top:8px;">
                  <b>Large Unutilized Annual Budget:</b><br>
                  <b>Remaining:</b> ₱${unused}<br>
                  <b>Allowed maximum:</b> ₱${threshold}<br>
                </div>
              `
              : "";

            Swal.fire({
              title: "Proceed with Consolidation?",
              html: `
                <div style="text-align:left; font-size:14px;">
                  You may continue, but only <b>Approved PPMPs</b> will be consolidated.
                  ${budgetHtml}
                  ${missingHtml}
                </div>
              `,
              icon: "warning",
              width: "650px",
              showCancelButton: true,
              confirmButtonText: "Proceed to Consolidate",
              cancelButtonText: "Cancel",
              allowOutsideClick: false
            }).then((res) => {
              if (!res.isConfirmed) return;
              runConsolidationAjax();
            });

            return;
          }

          Swal.fire({
            title: "Consolidate Approved PPMPs?",
            text: "This will group and save all approved items for the current fiscal year.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, consolidate"
          }).then((result) => {
            if (!result.isConfirmed) return;
            runConsolidationAjax();
          });
        },
        error: function (xhr, status, error) {
          showSweetAlert("Server Error", error, "error");
        }
      });

    });

    $('#datatable').on('click', '.view-ppmp', function () {

      const status = $(this).data('ppmp-status');
      const ppmpId = $(this).data('ppmp-id');

      $("#current_ppmp_id").val(ppmpId);

      if (status === "Pending") {
        $("#reviewer_section").show();
      } else {
        $("#reviewer_section").hide();
      }

      $("#current_status_badge")
        .text(status)
        .removeClass()
        .addClass("badge " +
          (status === "Pending" ? "bg-warning text-dark" :
            status === "Approved" ? "bg-success" :
              status === "Rejected" ? "bg-danger" :
                status === "Returned" ? "bg-info" : "bg-secondary")
        );

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

          if (!response.items || response.items.length === 0) {
            tbody.append(`<tr><td colspan="12" class="text-center">No items found.</td></tr>`);
          } else {
            let grandTotal = 0;

            response.items.forEach(item => {

              const unitCost = parseFloat(item.unit_cost || item.estimated_budget || 0);
              const totalCost = parseFloat(item.total_cost || 0);
              grandTotal += totalCost;

              const quantityAndSpecs = `${autoMoney(item.quantity)}<br>${item.specifications || '-'}`;

              tbody.append(`
                <tr>
                  <td>${item.item_description}</td>
                  <td>${item.category_name}</td>
                  <td>${quantityAndSpecs}</td>
                  <td>${item.mode_of_procurement || '-'}</td>
                  <td>${item.pre_procurement_conference || '-'}</td>

                  <td>${item.procurement_start_date || '-'}</td>
                  <td>${item.bidding_date || '-'}</td>
                  <td>${item.contract_signing_date || '-'}</td>

                  <td>${item.source_of_funds || '-'}</td>
                  <td class="money">${formatAccounting(unitCost)}</td>

                  <td>
                    ${item.file_attachment
                  ? item.file_attachment
                    .split(",")
                    .map(file => {
                      file = file.trim();
                      const ext = file.split('.').pop().toLowerCase();

                      let label = "Attached";
                      if (["jpg", "jpeg", "png", "gif"].includes(ext)) label = "View Image";
                      else if (ext === "pdf") label = "View PDF";
                      else if (["doc", "docx"].includes(ext)) label = "Download Word";
                      else if (["xls", "xlsx"].includes(ext)) label = "Download Excel";

                      return `
                            <a href="../assets/ppmp_attachments/${file}"
                              class="btn btn-sm btn-primary mb-1"
                              target="_blank">${label}</a>
                          `;
                    })
                    .join("<br>")
                  : "None"
                }
                  </td>

                  <td>${item.remarks || '-'}</td>
                </tr>
              `);
            });

            $("#total_budget_modal").text(formatAccounting(grandTotal));
          }

          $("#ViewPPMPModal").modal("show");
        },
        error: function (xhr, status, error) {
          showSweetAlert("Server Error", error, "error");
        }
      });
    });


  });
</script>