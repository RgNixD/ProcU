<?php require_once 'sidebar.php'; ?>

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Offices</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Offices List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">New Office</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label for="office_name">Office Name</label>
                    <input type="text" class="form-control" name="office_name" id="office_name" placeholder="Enter office name" required>
                  </div>
                  <div class="form-group">
                    <label for="office_code">Office Code</label>
                    <input type="text" class="form-control" name="office_code" id="office_code" placeholder="Enter office code" required>
                  </div>
                  <div class="form-group">
                    <label for="head_id">Head of Office</label>
                    <select class="form-control" name="head_id" id="head_id" required>
                      <option value="" disabled selected>Select Office Head</option>
                      <?php
                        $users = $db->getAllSectorsAndDeans();
                        while ($user = $users->fetch_assoc()) {
                            $fullname = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                            echo '<option value="' . htmlspecialchars($user['user_id']) . '">' . $fullname . '</option>';
                        }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" id="description" placeholder="Enter office description"></textarea>
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
                  <h5 class="modal-title" id="exampleModalLabel">Update Office</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="office_id" id="edit_office_id" required>
                  <div class="form-group">
                    <label for="edit_office_name">Office Name</label>
                    <input type="text" class="form-control" name="office_name" id="edit_office_name" placeholder="Enter office name" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_office_code">Office Code</label>
                    <input type="text" class="form-control" name="office_code" id="edit_office_code" placeholder="Enter office code" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_head_id">Head of Office</label>
                    <select class="form-control" name="head_id" id="edit_head_id" required>
                      <option value="" disabled selected>Select Office Head</option>
                      <?php
                        $users = $db->getAllSectorsAndDeans();
                        while ($user = $users->fetch_assoc()) {
                            $fullname = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                            echo '<option value="' . htmlspecialchars($user['user_id']) . '">' . $fullname . '</option>';
                        }
                      ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea class="form-control" name="description" id="edit_description" placeholder="Enter office description"></textarea>
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
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"><i
              class="fa fa-plus"></i> Create Office</button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1"><i class="fa fa-trash"></i>
            Delete</button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>OFFICE NAME</th>
                <th>OFFICE CODE</th>
                <th>OFFICE HEAD</th>
                <th>EMAIL</th>
                <th>PHONE</th>
                <th>ROLE</th>
                <th>DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $offices = $db->getAllOffices();
              while ($row = $offices->fetch_assoc()) {
              ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" value="<?= $row['office_id'] ?>"></td>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td><?= htmlspecialchars($row['office_code']); ?></td>
                  <td><?= htmlspecialchars($row['head_name'] ?: '—'); ?></td>
                  <td><?= htmlspecialchars($row['head_email'] ?: '—'); ?></td>
                  <td><?= $row['head_phone'] ? '+63' . htmlspecialchars($row['head_phone']) : '—'; ?></td>
                  <td><?= htmlspecialchars($row['head_role'] ?: '—'); ?></td>
                  <td><?= date("M. d, Y", strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button 
                      class="btn btn-success btn-sm edit-office" title="Edit office"
                      data-office-id="<?= $row['office_id']; ?>"
                      data-office-name="<?= htmlspecialchars($row['office_name']); ?>"
                      data-office-code="<?= htmlspecialchars($row['office_code']); ?>"
                      data-description="<?= htmlspecialchars($row['description']); ?>"
                      data-head-id="<?= htmlspecialchars($row['head_name']); ?>"
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
      formData.append('action', 'AddOfficeForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "offices.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    // Populate update modal with candidate data
    $('#datatable').on('click', '.edit-office', function () {
      const officeId = $(this).data('office-id');
      const officeName = $(this).data('office-name');
      const officeCode = $(this).data('office-code');
      const description = $(this).data('description');
      const headName = $(this).data('head-id');

      $('#edit_office_id').val(officeId);
      $('#edit_office_name').val(officeName);
      $('#edit_office_code').val(officeCode);
      $('#edit_description').val(description);

      $('#edit_head_id option').filter(function () {
        return $(this).text().trim() === headName;
      }).prop('selected', true);

      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateOfficeForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "offices.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("offices", "office_id", selectedIDs, "offices.php");
      });
    });


  });

</script>