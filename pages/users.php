<?php require_once 'sidebar.php'; ?>

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Users</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Users List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">New User Account</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <h6 class="mb-2">User Information</h6>
                  <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                  </div>
                  <div class="form-group">
                    <label for="first_name">First name</label>
                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First name" required>
                  </div>
                  <div class="form-group">
                    <label for="last_name">Last name</label>
                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last name" required>
                  </div>
                  <div class="form-group">
                    <label for="phone">Mobile number</label>
                    <div class="input-group">
                      <div class="input-group-text">+63</div>
                      <input type="tel" class="form-control" name="phone" id="phone" placeholder="9123456789" maxlength="10" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email address" required>
                  </div>
                  <h6 class="mb-2 mt-4">Access Control</h6>
                  <div class="form-group">
                    <label for="access_name">Access Role</label>
                    <select class="form-control" name="access_name" id="access_name" required>
                      <option value="" disabled selected>Select access level</option>
                      <option value="Procurement Head">Procurement Head</option>
                      <option value="Sectors and Deans">Sectors and Deans</option>
                      <option value="Budget Office">Budget Office</option>
                    </select>
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

              <input type="hidden" class="form-control" name="user_type" id="edit_user_type" value="Administrator"
                required>

              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">Update User Account</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="user_id" id="edit_user_id" required>
                  <h6 class="mb-2">User Information</h6>
                  <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" class="form-control" name="username" id="edit_username" placeholder="Username" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_first_name">First name</label>
                    <input type="text" class="form-control" name="first_name" id="edit_first_name" placeholder="First name" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_last_name">Last name</label>
                    <input type="text" class="form-control" name="last_name" id="edit_last_name" placeholder="Last name" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_phone">Mobile number</label>
                    <div class="input-group">
                      <div class="input-group-text">+63</div>
                      <input type="tel" class="form-control" name="phone" id="edit_phone" placeholder="9123456789" maxlength="10" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="edit_email">Email address</label>
                    <input type="email" class="form-control" name="email" id="edit_email" placeholder="Email address" required>
                  </div>
                  <h6 class="mb-2 mt-4">Access Control</h6>
                  <div class="form-group">
                    <label for="edit_access_name">Access Role</label>
                    <select class="form-control" name="access_name" id="edit_access_name" required>
                      <option value="" disabled selected>Select access level</option>
                      <option value="Procurement Head">Procurement Head</option>
                      <option value="Sectors and Deans">Sectors and Deans</option>
                      <option value="Budget Office">Budget Office</option>
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
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"><i
              class="fa fa-plus"></i> Create User</button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1"><i class="fa fa-trash"></i>
            Delete</button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>PROFILE</th>
                <th>USERNAME</th>
                <th>FULL NAME</th>
                <th>EMAIL</th>
                <th>PHONE</th>
                <th>ACCESS ROLE</th>
                <th>DATE CREATED</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $users = $db->getAllUsersWithAccess($userId);
              while ($row = $users->fetch_assoc()) {
              ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" value="<?= $row['user_id'] ?>"></td>
                  <td>
                    <img src="../assets/img/user-profiles/<?= htmlspecialchars($row['profile'] ?: 'avatar.png'); ?>"
                        alt="" width="35" height="35" class="img-circle d-block m-auto">
                  </td>
                  <td><?= htmlspecialchars($row['username']); ?></td>
                  <td><?= ucwords($row['first_name'] . ' ' . $row['last_name']); ?></td>
                  <td><?= htmlspecialchars($row['email']); ?></td>
                  <td>+63<?= htmlspecialchars($row['phone']); ?></td>
                  <td><?= htmlspecialchars($row['access_name']); ?></td>
                  <td ><?= date("F d, Y", strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                    <button 
                      class="btn btn-success btn-sm edit-user" title="Edit user"
                      data-user-id="<?= $row['user_id']; ?>"
                      data-username="<?= htmlspecialchars($row['username']); ?>"
                      data-first_name="<?= htmlspecialchars($row['first_name']); ?>"
                      data-last_name="<?= htmlspecialchars($row['last_name']); ?>"
                      data-phone="<?= htmlspecialchars($row['phone']); ?>"
                      data-email="<?= htmlspecialchars($row['email']); ?>"
                      data-access_name="<?= htmlspecialchars($row['access_name']); ?>"
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
      showSweetAlert("Please wait", "An email message will be sent to the new user", "info"); //FORMAT: TITLE, TEXT, ICON, URL
      var formData = new FormData($(this)[0]);
      formData.append('action', 'AddUserForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "users.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
        }, error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    });

    // Populate update modal with candidate data
    $('#datatable').on('click', '.edit-user', function () {
      const userID = $(this).data('user-id');
      const username = $(this).data('username');
      const firstName = $(this).data('first_name');
      const lastName = $(this).data('last_name');
      const phone = $(this).data('phone');
      const email = $(this).data('email');
      const accessName = $(this).data('access_name');

      // Set values in the modal fields
      $('#edit_user_id').val(userID);
      $('#edit_username').val(username);
      $('#edit_first_name').val(firstName);
      $('#edit_last_name').val(lastName);
      $('#edit_phone').val(phone);
      $('#edit_email').val(email);
      $('#edit_access_name').val(accessName);

      // Show modal
      $('#UpdateModal').modal('show');
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();
      var formData = new FormData($(this)[0]);
      formData.append('action', 'UpdateUserForm');
      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "users.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("users", "user_id", selectedIDs, "users.php");
      });
    });


  });

</script>