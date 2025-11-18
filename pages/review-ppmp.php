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
        <h3>PPMP</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>PPMP List</h2>
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
                <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="ppmpItemsTable">
                    <thead class="table-danger text-center">
                      <tr>
                        <th>PROJ. DESC.</th>
                        <th>CATEGORY</th>
                        <th>SUBCATEGORY</th>
                        <th>ITEM NAME</th>
                        <th>SPECIFICATIONS</th>
                        <th>QUANTITY</th>
                        <th>UNIT COST</th>
                        <th>TOTAL COST</th>
                        <th>QTR NEEDED</th>
                        <th>MODE OF PROC.</th>
                        <th>REMARKS</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
                <hr>
                <div class="row">
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
                            <button class="btn btn-danger btn-sm" id="reject_ppmp_btn" data-status="Rejected">
                                <i class="fa fa-times"></i> Reject
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
                <th class="text-center">FISCAL YEAR</th>
                <th class="text-end">TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
                <th class="text-center">STATUS</th>
                <th>NOTES</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $ppmpRecords = $db->getAllPPMPRecords();
              while ($row = $ppmpRecords->fetch_assoc()) {
                $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
                $totalAmount = '₱' . number_format($row['total_amount'], 2);
                $submissionDate = date('M d, Y', strtotime($row['created_at']));
              ?>
                <tr>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td><?= htmlspecialchars($fullName); ?></td>
                  <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <td class="text-end fw-bold"><?= $totalAmount; ?></td>
                  <td><?= $submissionDate; ?></td>
                  <td class="text-center">
                    <?php if ($row['status'] == 'Pending'): ?>
                      <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif ($row['status'] == 'Approved'): ?>
                      <span class="badge bg-success">Approved</span>
                    <?php elseif ($row['status'] == 'Rejected'): ?>
                      <span class="badge bg-danger">Rejected</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($row['status']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($row['notes']); ?></td>
                  <td class="text-center">
                    <button 
                      class="btn btn-primary btn-sm view-ppmp"
                      data-ppmp-id="<?= $row['ppmp_id']; ?>"
                      data-ppmp-status="<?= htmlspecialchars($row['status']); ?>"
                      title="View Details">
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
    
    function updateStatusBadge(status) {
        let badgeClass = 'bg-secondary';
        if (status === 'Pending') {
            badgeClass = 'bg-warning text-dark';
        } else if (status === 'Approved') {
            badgeClass = 'bg-success';
        } else if (status === 'Rejected') {
            badgeClass = 'bg-danger';
        }
        $('#current_status_badge').text(status).removeClass().addClass('text-light badge ' + badgeClass);
    }
    
    // VIEW PPMP DETAILS
    $('#datatable').on('click', '.view-ppmp', function () {
      const ppmpId = $(this).data("ppmp-id");
      const currentStatus = $(this).data("ppmp-status");

      $("#current_ppmp_id").val(ppmpId);
      $("#reviewer_notes").val('');
      updateStatusBadge(currentStatus);

      if (currentStatus === 'Pending') {
          $('#review_actions').show();
      } else {
          $('#review_actions').hide();
      }

      $.ajax({
        url: "../php/processes.php",
        type: "POST",
        data: { action: "GetPPMPItems", ppmp_id: ppmpId },
        dataType: "json",
        success: function (response) {
          if (response.success) {

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
              response.items.forEach(item => {
                const unitCost = parseFloat(item.unit_cost || item.estimated_budget || 0).toFixed(2);
                const totalCost = parseFloat(item.total_cost || 0).toFixed(2);

                // Determine Quarter Needed 
                let quarterDisplay = '-';
                if (item.procurement_start_date) {
                    const date = new Date(item.procurement_start_date);
                    const month = date.getMonth() + 1; 
                    
                    if (month >= 1 && month <= 3) {
                      quarterDisplay = 'Q1';
                    } else if (month >= 4 && month <= 6) {
                      quarterDisplay = 'Q2';
                    } else if (month >= 7 && month <= 9) {
                      quarterDisplay = 'Q3';
                    } else if (month >= 10 && month <= 12) {
                      quarterDisplay = 'Q4';
                    }
                }
                // ----------------------

                const remarksDisplay = item.remarks || '-';

                tbody.append(`
                <tr>
                  <td>${item.item_description}</td>
                  <td>${item.category_name}</td>
                  <td>${item.sub_cat_name || '-'}</td>
                  <td>${item.item_name}</td>
                  <td>${item.specifications}</td>
                  <td class="text-center">${item.quantity}</td>
                  <td class="text-end">₱${unitCost}</td>
                  <td class="text-end">₱${totalCost}</td>
                  <td class="text-center">${quarterDisplay}</td>   
                  <td>${item.mode_of_procurement || '-'}</td>
                  <td>${remarksDisplay}</td>
                </tr>
              `);
              });
            }

            $("#ViewPPMPModal").modal("show");
          } else {
            showSweetAlert("Error", response.message || "Unable to load PPMP items.", "error");
          }
        },
        error: function (xhr, status, error) {
          showSweetAlert("Server Error", error, "error");
        }
      });
    });
    // END VIEW PPMP DETAILS
    
    // START PPMP STATUS UPDATE LOGIC
    $('#approve_ppmp_btn, #reject_ppmp_btn').on('click', function() {
        const ppmpId = $("#current_ppmp_id").val();
        const newStatus = $(this).data('status'); // 'Approved' or 'Rejected'
        const notes = $("#reviewer_notes").val();
        
        if (!ppmpId) {
            showSweetAlert("Error", "PPMP ID not found.", "error");
            return;
        }

        // Confirmation before proceeding
        Swal.fire({
            title: `Confirm ${newStatus}?`,
            text: `Are you sure you want to set this PPMP's status to ${newStatus}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${newStatus} it!`
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX call to update status
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
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message || `PPMP ${newStatus} successfully!`,
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Close modal and reload page/datatable
                                $("#ViewPPMPModal").modal("hide");
                                location.reload(); 
                            });
                        } else {
                            showSweetAlert("Error", response.message || "Failed to update PPMP status.", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showSweetAlert("Server Error", "An error occurred during status update: " + error, "error");
                    }
                });
            }
        });
    });
    // END PPMP STATUS UPDATE LOGIC

  });

</script>