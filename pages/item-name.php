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
        <h3>Item Names</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Item Names List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="AddSubCategoryLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="AddSubCategoryLabel">New Item</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="sub_category_id">Sub Category</label>
                    <select class="form-control" name="sub_category_id" id="sub_category_id" required>
                      <option value="">Select Category</option>
                      <?php
                      $subcategories = $db->getAllSubCategories();
                      while ($row2 = $subcategories->fetch_assoc()):
                        ?>
                        <option value="<?= htmlspecialchars($row2['sub_category_id']); ?>">
                          <?= htmlspecialchars($row2['sub_cat_name']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="item_name">Item Name</label>
                    <input type="text" class="form-control" name="item_name" id="item_name" placeholder="Enter item name" required>
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
                  <h5 class="modal-title" id="exampleModalLabel">Update Item</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="item_name_id" id="edit_item_name_id" required>
                  <div class="form-group">
                    <label for="edit_sub_category_id">Sub Category</label>
                    <select class="form-control" name="sub_category_id" id="edit_sub_category_id" required>
                      <option value="">Select Category</option>
                      <?php
                      $subcategories = $db->getAllSubCategories();
                      while ($row2 = $subcategories->fetch_assoc()):
                        ?>
                        <option value="<?= htmlspecialchars($row2['sub_category_id']); ?>">
                          <?= htmlspecialchars($row2['sub_cat_name']); ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="edit_item_name">Item Name</label>
                    <input type="text" class="form-control" name="item_name" id="edit_item_name" placeholder="Enter item name" required>
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
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"
            title="Create sub-category"><i class="fa fa-plus"></i></button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete sub-category"><i
              class="fa fa-trash"></i></button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>CATEGORY</th>
                <th>SUB-CATEGORY</th>
                <th>ITEM NAME</th>
                <th class="d-none">DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $itemNames = $db->getAllItemNames();
              while ($row = $itemNames->fetch_assoc()) {
                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['item_name_id'] ?>"
                      id="record_<?= $row['item_name_id'] ?>" value="<?= $row['item_name_id']; ?>"></td>
                  <td><?= htmlspecialchars($row['category_name']); ?></td>
                  <td><?= htmlspecialchars($row['sub_cat_name']); ?></td>
                  <td><?= htmlspecialchars($row['item_name']); ?></td>
                  <td class="d-none"><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button class="btn btn-success btn-sm edit-item" title="Edit Item Name"
                      data-item-id="<?= $row['item_name_id']; ?>" data-sub-category-id="<?= $row['sub_category_id']; ?>"
                      data-sub-cat-id="<?= htmlspecialchars($row['sub_category_id']); ?>"
                      data-item-name="<?= htmlspecialchars($row['item_name']); ?>">
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

    $('#sub_category_id').select2({
      dropdownParent: $('#AddNew'),
      width: '100%',
      placeholder: 'Select Subcategory'
    });

    $('#edit_sub_category_id').select2({
      dropdownParent: $('#UpdateModal'),
      width: '100%',
      placeholder: 'Select Subcategory'
    });

    $('#SaveForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'AddItemNameForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "item-name.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    $('#datatable').on('click', '.edit-item', function () {
      const item_name_id = $(this).data('item-name-id');
      const sub_cat_id = $(this).data('sub-cat-id');
      const item_name = $(this).data('item-name');

      $('#edit_item_name_id').val(item_name_id);
      $('#edit_sub_category_id').val(sub_cat_id);
      $('#edit_sub_category_id').trigger('change');
      $('#edit_item_name').val(item_name);

      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateItemNameForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "item-name.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("item_names", "item_name_id", selectedIDs, "item-name.php");
      });
    });


  });

</script>