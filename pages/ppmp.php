<?php
require_once '../php/auth_check.php';
if (!($canCreatePPMP)) {
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
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <a href="ppmp-create.php" class="btn btn-sm btn-primary mb-3 ml-3 mt-1" title="Create PPMP"><i
              class="fa fa-plus"></i> </a>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete PPMP"><i
              class="fa fa-trash"></i></button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>PPMP CODE</th>
                <th>OFFICE</th>
                <th class="text-center">FISCAL YEAR</th>
                <?php if ($canApprovePPMP && $canViewReports): ?>
                  <th>SUBMITTED BY</th>
                <?php endif; ?>
                <th class="text-center">TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
                <th class="text-center">STATUS</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $ppmpRecords = $db->getAllPPMPRecordsBySector($userId);
              while ($row = $ppmpRecords->fetch_assoc()) {
                $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['ppmp_id'] ?>"
                      id="record_<?= $row['ppmp_id'] ?>" value="<?= $row['ppmp_id'] ?>"></td>
                  <td><?= htmlspecialchars($row['ppmp_code']); ?></td>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <?php if ($canApprovePPMP && $canViewReports): ?>
                    <td><?= htmlspecialchars($fullName); ?></td>
                  <?php endif; ?>
                  <td class="text-center">₱<?= number_format($row['total_amount'], 2); ?></td>
                  <td><?= date("F d, Y", strtotime($row['created_at'])); ?></td>
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
                  <td class="text-center">
                    <button class="btn btn-primary btn-sm view-ppmp" data-ppmp-id="<?= $row['ppmp_id']; ?>"
                      title="View Details">
                      <i class="fa fa-eye"></i>
                    </button>
                    <?php if ($row['status'] == 'Pending'): ?>
                    <button class="btn btn-success btn-sm" title="Edit PPMP"
                      onclick="window.location.href = 'ppmp-update.php?ppmp_id=<?= $row['ppmp_id']; ?>'">
                      <i class="fa fa-edit"></i>
                    </button>
                    <?php endif; ?>
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

    // VIEW PPMP DETAILS
    $('#datatable').on('click', '.view-ppmp', function () {
      const ppmpId = $(this).data("ppmp-id");

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
                  const month = parseInt(item.procurement_start_date.substring(5, 7));
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

    // Multiple Deletion
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
        deleteMultipleRecords("ppmp", "ppmp_id", selectedIDs, "ppmp.php");
      });
    });

  });

</script>