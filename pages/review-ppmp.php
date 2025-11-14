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
                <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="ppmpItemsTable">
                    <thead class="table-danger text-center">
                      <tr>
                        <th>CATEGORY</th>
                        <th>SUBCATEGORY</th>
                        <th>ITEM NAME</th>
                        <th>DESCRIPTION</th>
                        <th>SPECIFICATIONS</th>
                        <th>QUANTITY</th>
                        <th>UNIT</th>
                        <th>UNIT COST</th>
                        <th>TOTAL COST</th>
                        <th>QUARTER</th>
                        <th>PROC. METHOD</th>
                        <th>JUSTIFICATION</th>
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
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>OFFICE</th>
                <th>SUBMITTED BY</th>
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
              ?>
                <tr>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td><?= htmlspecialchars($fullName); ?></td>
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
                  <td>Add here...</td>
                  <td class="text-center">
                    <button 
                      class="btn btn-primary btn-sm view-ppmp"
                      data-ppmp-id="<?= $row['ppmp_id']; ?>"
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
    
  // VIEW PPMP DETAILS
    $('#datatable').on('click', '.view-ppmp', function () {
      const ppmpId = $(this).data("ppmp-id");

      $.ajax({
        url: "../php/processes.php", 
        type: "POST",
        data: { action: "GetPPMPItems", ppmp_id: ppmpId },
        dataType: "json",
        success: function(response) {
          if (response.success) {
            const tbody = $("#ppmpItemsTable tbody");
            tbody.empty();

            if (response.data.length === 0) {
              tbody.append(`<tr><td colspan="12" class="text-center">No items found.</td></tr>`);
            } else {
              response.data.forEach(item => {
                tbody.append(`
                  <tr>
                    <td>${item.category_name}</td>
                    <td>${item.sub_cat_name || '-'}</td>
                    <td>${item.item_name}</td>
                    <td>${item.item_description}</td>
                    <td>${item.specifications}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td>${item.unit_of_measure}</td>
                    <td class="text-end">₱${parseFloat(item.unit_cost).toFixed(2)}</td>
                    <td class="text-end">₱${parseFloat(item.total_cost).toFixed(2)}</td>
                    <td class="text-center">${item.quarter_needed}</td>
                    <td>${item.procurement_method || '-'}</td>
                    <td>${item.justification}</td>
                  </tr>
                `);
              });
            }

            $("#ViewPPMPModal").modal("show");
          } else {
            showSweetAlert("Error", response.message || "Unable to load PPMP items.", "error"); 
          }
        },
        error: function(xhr, status, error) {
          showSweetAlert("Server Error", error, "error"); 
        }
      });
    });
  // END VIEW PPMP DETAILS

  });

</script>