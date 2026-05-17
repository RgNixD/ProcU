<?php
require_once '../php/auth_check.php';
if (!($canApprovePPMP && $canViewReports && $canManageBudget)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';
if (!isset($_GET['ppmp_id'])) {
  header("Location: review-ppmp.php");
  exit();
}
$ppmpId = (int) $_GET['ppmp_id'];
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
  <div class="x_panel">
    <div class="x_title">
      <h2>PPMP Details</h2>
      <div class="clearfix"></div>
    </div>

    <div class="x_content">
      <div class="row mb-3">
        <div class="col-md-3"><b>PPMP Code:</b> <span id="ppmp_code">-</span></div>
        <div class="col-md-3"><b>Office:</b> <span id="office_name">-</span></div>
        <div class="col-md-3"><b>Fiscal Year:</b> <span id="fiscal_year">-</span></div>
        <div class="col-md-3"><b>Current Status:</b><span id="current_status_badge" class="badge bg-secondary">-</span>
        </div>
      </div>
      <hr>
      <h6 class="text-white p-2 mb-0 mt-4" style="background-color:#a83232;">CURRENT ITEMS</h6>
      <div class="table-responsive">
        <table class="table table-bordered" id="ppmpItemsTable">
          <thead>
            <tr>
              <th colspan="5" class="text-center bg-light">PROCUREMENT PROJECT DETAILS</th>
              <th colspan="3" class="text-center bg-light">PROJECTED TIMELINE (MM/YYYY)</th>
              <th colspan="2" class="text-center bg-light">FUNDING DETAILS</th>
              <th rowspan="2" class="text-center">ATTACHED SUPPORTING DOCUMENTS</th>
              <th rowspan="2" class="text-center">REMARKS</th>
            </tr>
            <tr>
              <th class="text-center">General Description and Objective...</th>
              <th class="text-center">Type...</th>
              <th class="text-center">Quantity and Size...</th>
              <th class="text-center">Recommended Mode...</th>
              <th class="text-center">Pre-Procurement...</th>
              <th class="text-center">Start</th>
              <th class="text-center">End</th>
              <th class="text-center">Expected Delivery...</th>
              <th class="text-center">Source of Funds</th>
              <th class="text-center">Estimated Budget per Item (PhP)</th>
            </tr>
          </thead>
          <tbody></tbody>
          <tfoot>
            <tr>
              <td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td>
              <td id="total_budget" class="fw-bold money"></td>
              <td></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>


      <div class="row" id="reviewer_section">
        <div class="col-md-12">
          <label class="fw-bold">Reviewer Notes (Optional):</label>
          <textarea id="reviewer_notes" class="form-control mb-3" rows="3"></textarea>
        </div>
        <div class="col-md-12 d-flex justify-content-between align-items-center">
          <div>
            <input type="hidden" id="current_ppmp_id" value="<?= $ppmpId ?>">
            <a href="ppmp-pending.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to
              List</a>
          </div>
          <div id="review_actions">
            <button class="btn btn-danger btn-sm" id="reject_ppmp_btn" data-status="Rejected"><i
                class="fa fa-times"></i> Disapprove</button>
            <button class="btn btn-success btn-sm" id="approve_ppmp_btn" data-status="Approved"><i
                class="fa fa-check"></i> Approve</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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

    function setStatusBadge(status) {

      let cls = "bg-secondary";

      if ((status || "").includes("Pending")) {
        cls = "bg-warning text-dark";
        $("#reviewer_section").show();
        $("#review_actions").show();
      } else if ((status || "").includes("Approved")) {
        cls = "bg-success text-light";
        $("#review_actions").hide();
      } else if ((status || "").includes("Returned")) {
        cls = "bg-info text-light";
        $("#review_actions").hide();
      } else if ((status || "").includes("Rejected")) {
        cls = "bg-danger text-light";
        $("#review_actions").hide();
      } else {
        $("#review_actions").hide();
      }

      $("#current_status_badge")
        .text(status || "-")
        .removeClass()
        .addClass("badge " + cls);
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
            $('#ppmp_code').text(response.header.ppmp_code || '-');
            $('#office_name').text(response.header.office_name || '-');
            $('#fiscal_year').text(response.header.fiscal_year || '-');

            setStatusBadge(response.header.status || response.header.display_status || 'Pending');
          }

          const tbody = $("#ppmpItemsTable tbody");
          tbody.empty();

          let grandTotal = 0;

          if (!response.items || response.items.length === 0) {
            tbody.append(`<tr><td colspan="12" class="text-center">No items found.</td></tr>`);
          } else {
            response.items.forEach(item => {
              const unitCost = parseFloat(item.unit_cost || item.estimated_budget || 0);
              const totalCost = parseFloat(item.total_cost || 0);
              grandTotal += totalCost;

              let quantityValue = item.quantity;
              if (!isNaN(quantityValue) && quantityValue !== "" && Number(quantityValue) >= 1000) {
                quantityValue = formatAccounting(quantityValue);
              }

              const quantityAndSpecs = `${quantityValue}<br>${item.specifications || '-'}`;

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
                <td class="text-center">
  ${item.file_attachment
                  ? `<button type="button"
          class="btn btn-sm btn-primary view-attachments-btn"
          data-files="${encodeURIComponent(item.file_attachment)}">
          View All (${item.file_attachment.split(',').filter(f => f.trim()).length})
       </button>`
                  : "None"
                }
</td>
                <td>${item.remarks || '-'} ${item.status_label ? `<span class="badge bg-info ms-2">${item.status_label}</span>` : ''}</td>
              </tr>
            `);
            });
          }

          $("#total_budget").text(formatAccounting(grandTotal));

        },
        error: function (xhr, status, error) {
          showSweetAlert("Server Error", error, "error");
        }
      });
    }

    $(document).on('click', '.view-attachments-btn', function () {
      const files = decodeURIComponent($(this).data('files'))
        .split(',')
        .map(file => file.trim())
        .filter(Boolean);

      let html = '<div class="text-start">';

      files.forEach((file, index) => {
        const ext = file.split('.').pop().toLowerCase();
        const fileName = file.split('/').pop();

        let label = 'Open File';
        let icon = 'fa-file';

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
          label = 'View Image';
          icon = 'fa-image';
        } else if (ext === 'pdf') {
          label = 'View PDF';
          icon = 'fa-file-pdf-o';
        } else if (['doc', 'docx'].includes(ext)) {
          label = 'Download Word';
          icon = 'fa-file-word-o';
        } else if (['xls', 'xlsx'].includes(ext)) {
          label = 'Download Excel';
          icon = 'fa-file-excel-o';
        }

        html += `
      <a href="../assets/ppmp_attachments/${file}"
         target="_blank"
         class="btn btn-outline-primary btn-sm w-100 mb-2 text-start">
        <i class="fa ${icon} me-1"></i>
        ${index + 1}. ${label} - ${fileName}
      </a>
    `;
      });

      html += '</div>';

      Swal.fire({
        title: `Attachments (${files.length})`,
        html: html,
        width: 600,
        showConfirmButton: false,
        showCloseButton: true
      });
    });

    const ppmpId = $("#current_ppmp_id").val();
    loadPPMPDetails(ppmpId);

    $('#approve_ppmp_btn, #reject_ppmp_btn').on('click', function () {
      const ppmpId = $("#current_ppmp_id").val();
      const newStatus = $(this).data('status');
      const actionText = newStatus === 'Approved' ? 'approve' : 'reject';
      const notes = $("#reviewer_notes").val();

      Swal.fire({
        title: newStatus === 'Approved' ? 'Approve PPMP?' : 'Reject PPMP?',
        text: `Are you sure you want to ${actionText} this PPMP?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Yes, ${actionText} it!`
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "../php/processes.php",
            type: "POST",
            data: {
              action: "UpdatePPMPStatus",
              ppmp_id: ppmpId,
              status: newStatus,
              notes: notes
            },
            dataType: "json",
            success: function (response) {
              if (response.success) {
                Swal.fire({
                  title: "Success!",
                  text: response.message || `PPMP ${newStatus.toLowerCase()} successfully!`,
                  icon: "success",
                  timer: 2000,
                  showConfirmButton: false
                }).then(() => {
                  window.location.href = "ppmp-pending.php";
                });
              } else {
                showSweetAlert(
                  "Error",
                  response.message || "Failed to update PPMP status.",
                  "error"
                );
              }
            },
            error: function (xhr, status, error) {
              showSweetAlert(
                "Server Error",
                "An error occurred during status update: " + error,
                "error"
              );
            }
          });
        }
      });
    });

  });
</script>