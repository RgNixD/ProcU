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
        <h3>Item Categories</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Item Categories List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">New category</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" class="form-control" name="category_name" id="category_name" placeholder="Enter category name" required>
                  </div>
                  <div class="form-group">
                    <label for="category_code">Category Code</label>
                    <input type="text" class="form-control" name="category_code" id="category_code" placeholder="Enter category code" required>
                  </div>
                  <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="3" placeholder="Enter category description"></textarea>
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

        <!-- UPDATE MODAL -->
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="UpdateForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">Update category</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="category_id" id="edit_category_id" required>
                  <div class="form-group">
                    <label for="edit_category_name">Category Name</label>
                    <input type="text" class="form-control" name="category_name" id="edit_category_name" placeholder="Enter category name" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_category_code">Category Code</label>
                    <input type="text" class="form-control" name="category_code" id="edit_category_code" placeholder="Enter category code" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Enter category description"></textarea>
                  </div>
                  <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select class="form-control" name="is_active" id="edit_status" required>
                      <option value="1">Active</option>
                      <option value="0">Inactive</option>
                    </select>
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
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1" title="Create category"><i
              class="fa fa-plus"></i></button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete category"><i class="fa fa-trash"></i></button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>CATEGORY NAME</th>
                <th class="text-center">CODE</th>
                <th>DESCRIPTION</th>
                <th class="text-center d-none">STATUS</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $categories = $db->getAllCategories();
              while ($row = $categories->fetch_assoc()) {
                $status_badge = ($row['is_active'] == 1)
                  ? '<span class="badge bg-success p-1 rounded text-light">Active</span>'
                  : '<span class="badge bg-secondary p-1 rounded text-light">Inactive</span>';
              ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['category_id'] ?>" id="record_<?= $row['category_id'] ?>" value="<?= $row['category_id'] ?>"></td>
                  <td><?= htmlspecialchars($row['category_name']); ?></td>
                  <td class="text-center"><?= htmlspecialchars($row['category_code']); ?></td>
                  <td><?= htmlspecialchars($row['description']); ?></td>
                  <td class="text-center d-none"><?= $status_badge; ?></td>
                  <td class="text-center">
                    <button 
                      class="btn btn-success btn-sm edit-category" title="Edit category"
                      data-category-id="<?= $row['category_id']; ?>"
                      data-category_name="<?= htmlspecialchars($row['category_name']); ?>"
                      data-category_code="<?= htmlspecialchars($row['category_code']); ?>"
                      data-status="<?= htmlspecialchars($row['is_active']); ?>"
                      data-description="<?= htmlspecialchars($row['description']); ?>">
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
      formData.append('action', 'AddCategoryForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "categories.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });
    
    $('#datatable').on('click', '.edit-category', function () {
      const category_id = $(this).data('category-id');
      const category_name = $(this).data('category_name');
      const category_code = $(this).data('category_code');
      const description = $(this).data('description');
      const status = $(this).data('status');

      $('#edit_category_id').val(category_id);
      $('#edit_category_name').val(category_name);
      $('#edit_category_code').val(category_code);
      $('#edit_description').val(description);
      $('#edit_status').val(status);

      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateCategoryForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "categories.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("item_categories", "category_id", selectedIDs, "categories.php");
      });
    });


  });

</script>