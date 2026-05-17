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
        <h3>Bidding Categories</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Bidding Categories List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddSubCategoryLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddSubCategoryLabel">New Bidding Category</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="bid_cat_name">Bidding Category Name</label>
                    <input type="text" class="form-control" name="bid_cat_name" id="bid_cat_name" placeholder="Enter bidding category name" required>
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
                  <h5 class="modal-title" id="exampleModalLabel">Update Bidding Category</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="bid_cat_ID" id="edit_bid_cat_ID" required>
                  <div class="form-group">
                    <label for="edit_bid_cat_name">Bidding Category Name</label>
                    <input type="text" class="form-control" name="bid_cat_name" id="edit_bid_cat_name" placeholder="Enter bidding category name" required>
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
          <div class="d-flex justify-content-end mr-2">
            <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"
              title="Create sub-category"><i class="fa fa-plus"></i></button>
            <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete sub-category"><i
                class="fa fa-trash"></i></button>
          </div>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>BIDDING CATEGORY</th>
                <th>DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $bidCat = $db->getBiddingCategory();
              while ($row = $bidCat->fetch_assoc()) {
                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['bid_cat_ID'] ?>" id="record_<?= $row['bid_cat_ID'] ?>" value="<?= $row['bid_cat_ID']; ?>"></td>
                  <td><?= htmlspecialchars($row['bid_cat_name']); ?></td>
                  <td><?= date("M. d, Y", strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button class="btn btn-success btn-sm edit-item" title="Edit Item Name"
                      data-bid-cat-id="<?= $row['bid_cat_ID']; ?>"
                      data-bid-cat-name="<?= htmlspecialchars($row['bid_cat_name']); ?>">
                      <i class="fa fa-edit"></i> 
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

    $('#SaveForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'AddBiddingCategoryForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "bid-category.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });


    $('#datatable').on('click', '.edit-item', function () {
      const bid_cat_ID = $(this).data('bid-cat-id');
      const bid_cat_name = $(this).data('bid-cat-name');

      $('#edit_bid_cat_ID').val(bid_cat_ID);
      $('#edit_bid_cat_name').val(bid_cat_name);

      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateBiddingCategoryForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "bid-category.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("bidding_category", "bid_cat_ID", selectedIDs, "bid-category.php");
      });
    });


  });

</script>