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
        <h3>Sub Categories</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Sub Categories List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD SUBCATEGORY MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddSubCategoryLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddSubCategoryLabel">New Sub-category</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>

                <div class="modal-body">
                  <div class="form-group">
                    <label for="category_id">Category</label>
                    <select class="form-control" name="category_id" id="category_id" required>
                      <option value="">Select Category</option>
                      <?php
                      $categories = $db->getAllCategories();
                      while ($row2 = $categories->fetch_assoc()):
                      ?>
                        <option value="<?= htmlspecialchars($row2['category_id']); ?>">
                          <?= htmlspecialchars($row2['category_name']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="sub_cat_name">Sub-category Name</label>
                    <input type="text" class="form-control" name="sub_cat_name" id="sub_cat_name" placeholder="Enter sub-category name" required>
                  </div>
                  <div class="form-group">
                    <label for="sub_cat_description">Description</label>
                    <textarea class="form-control" name="sub_cat_description" id="sub_cat_description" rows="3" placeholder="Enter sub-category description"></textarea>
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
                  <h5 class="modal-title" id="exampleModalLabel">Update Sub-Category</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="sub_category_id" id="edit_sub_category_id" required>
                  <div class="form-group">
                    <label for="edit_category_id">Category</label>
                    <select class="form-control" name="category_id" id="edit_category_id" required>
                      <option value="">Select Category</option>
                      <?php
                      $categories = $db->getAllCategories();
                      while ($row2 = $categories->fetch_assoc()):
                      ?>
                        <option value="<?= htmlspecialchars($row2['category_id']); ?>">
                          <?= htmlspecialchars($row2['category_name']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="edit_sub_cat_name">Sub-category Name</label>
                    <input type="text" class="form-control" name="sub_cat_name" id="edit_sub_cat_name" placeholder="Enter sub-category name" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_sub_cat_description">Description</label>
                    <textarea class="form-control" name="sub_cat_description" id="edit_sub_cat_description" rows="3" placeholder="Enter sub-category description"></textarea>
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
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1" title="Create sub-category"><i
              class="fa fa-plus"></i></button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete sub-category"><i class="fa fa-trash"></i></button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>CATEGORY NAME</th>
                <th>SUB-CATEGORY NAME</th>
                <th>DESCRIPTION</th>
                <th class="d-none">DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $subCategories = $db->getAllSubCategories();
              while ($row = $subCategories->fetch_assoc()) {
              ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['sub_category_id'] ?>" id="record_<?= $row['sub_category_id'] ?>" value="<?= $row['sub_category_id']; ?>"></td>
                  <td><?= htmlspecialchars($row['category_name']); ?></td>
                  <td><?= htmlspecialchars($row['sub_cat_name']); ?></td>
                  <td><?= htmlspecialchars($row['sub_cat_description']); ?></td>
                  <td class="d-none"><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button 
                      class="btn btn-success btn-sm edit-subcategory" 
                      title="Edit sub-category"
                      data-sub-category-id="<?= $row['sub_category_id']; ?>"
                      data-category-id="<?= $row['category_id']; ?>"
                      data-sub-cat-name="<?= htmlspecialchars($row['sub_cat_name']); ?>"
                      data-sub-cat-description="<?= htmlspecialchars($row['sub_cat_description']); ?>"
                    >
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
      formData.append('action', 'AddSubCategoryForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "sub-categories.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    
    $('#datatable').on('click', '.edit-subcategory', function () {
      const sub_category_id = $(this).data('sub-category-id');
      const category_id = $(this).data('category-id');
      const sub_cat_name = $(this).data('sub-cat-name');
      const sub_cat_description = $(this).data('sub-cat-description');

      $('#edit_sub_category_id').val(sub_category_id);
      $('#edit_category_id').val(category_id);
      $('#edit_sub_cat_name').val(sub_cat_name);
      $('#edit_sub_cat_description').val(sub_cat_description);

      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateSubCategoryForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "sub-categories.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("sub_categories", "sub_category_id", selectedIDs, "sub-categories.php");
      });
    });


  });

</script>