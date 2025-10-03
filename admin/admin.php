<?php require_once 'sidebar.php'; ?>
<title>Administrator - <?= $system_info['system_name'] ?></title>

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Administrator</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>Manage Administrator</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog"> <!-- Changed to modal-lg for larger size -->
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">

              <input type="hidden" class="form-control" name="user_type" id="user_type" value="Administrator" required>

              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">New Account</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <h6><a href="#" class="text-primary">Basic information</a></h6>
                  </div>
                  <div class="form-group">
                    <label for="firstname">First name</label>
                    <input type="text" class="form-control" name="firstname" id="firstname" placeholder="First name"
                      onkeyup="lettersOnly(this)" required>
                  </div>
                  <div class="form-group">
                    <label for="middlename">Middle name</label>
                    <input type="text" class="form-control" name="middlename" id="middlename" placeholder="Middle name"
                      onkeyup="lettersOnly(this)">
                  </div>
                  <div class="form-group">
                    <label for="lastname">Last name</label>
                    <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Last name"
                      required>
                  </div>
                  <div class="form-group">
                    <label for="suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" id="suffix"
                      placeholder="Jr., Sr., I, II, III">
                  </div>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                          <label for="gender">Gender</label>
                          <select class="form-control form-select" name="gender" id="gender" required>
                            <option selected disabled value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                          <label for="birthdate">Date of birth</label>
                          <input type="date" class="form-control" name="birthdate" id="birthdate"
                            data-toggle="birthday" title="Date of birth" max="<?php echo date('Y-m-d'); ?>" oninput="validateBirthdate(this)" required>
                          <div class="input-group text-danger">
                            <span class="birthdate-error-message text-danger"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="nationality">Nationality</label>
                    <input type="text" class="form-control" name="nationality" id="nationality"
                      placeholder="Nationality" required>
                  </div>
                  <div class="form-group">
                    <h6><a href="#" class="text-primary">Contact details</a></h6>
                  </div>
                  <div class="form-group">
                    <label for="contact">Mobile number</label>
                    <div class="input-group">
                      <div class="input-group-text">+63</div>
                      <input type="tel" class="form-control" pattern="[7-9]{1}[0-9]{9}" name="contact" id="contact"
                        placeholder="9123456789" maxlength="10" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email address"
                      autocomplete="off" oninput="email_validation(this)" required>
                    <!-- FOR INVALID EMAIL -->
                    <div class="input-group">
                      <div class="email-error-message text-danger"></div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                      class="fa fa-times"></i> Cancel</button>
                  <button type="submit" class="btn btn-primary btn-sm" id="submit_button"><i class="fa fa-save"></i>
                    Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- UPDATE MODAL -->
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog"> <!-- Changed to modal-lg for larger size -->
            <form id="UpdateForm" autocomplete="off" enctype="multipart/form-data">

              <input type="hidden" class="form-control" name="user_type" id="edit_user_type" value="Administrator"
                required>

              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">Update Account</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="user_ID" id="edit_user_ID" required>
                  <div class="form-group">
                    <h6><a href="#" class="text-primary">Basic information</a></h6>
                  </div>
                  <div class="form-group">
                    <label for="edit_firstname">First name</label>
                    <input type="text" class="form-control" name="firstname" id="edit_firstname"
                      placeholder="First name" onkeyup="lettersOnly(this)" required>
                  </div>
                  <div class="form-group">
                    <label for="edit_middlename">Middle name</label>
                    <input type="text" class="form-control" name="middlename" id="edit_middlename"
                      placeholder="Middle name" onkeyup="lettersOnly(this)">
                  </div>
                  <div class="form-group">
                    <label for="edit_lastname">Last name</label>
                    <input type="text" class="form-control" name="lastname" id="edit_lastname" placeholder="Last name"
                      required>
                  </div>
                  <div class="form-group">
                    <label for="edit_suffix">Suffix</label>
                    <input type="text" class="form-control" name="suffix" id="edit_suffix"
                      placeholder="Jr., Sr., I, II, III">
                  </div>
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                          <label for="edit_gender">Gender</label>
                          <select class="form-control form-select" name="gender" id="edit_gender" required>
                            <option selected disabled value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                          <label for="edit_birthdate">Date of birth</label>
                          <input type="date" class="form-control" name="birthdate" id="edit_birthdate"
                            data-toggle="birthday" title="Date of birth" max="<?php echo date('Y-m-d'); ?>" oninput="validateBirthdate(this)" required>
                          <div class="input-group text-danger">
                            <span class="birthdate-error-message text-danger"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="edit_nationality">Nationality</label>
                    <input type="text" class="form-control" name="nationality" id="edit_nationality"
                      placeholder="Nationality" required>
                  </div>
                  <div class="form-group">
                    <h6><a href="#" class="text-primary">Contact details</a></h6>
                  </div>
                  <div class="form-group">
                    <label for="edit_contact">Mobile number</label>
                    <div class="input-group">
                      <div class="input-group-text">+63</div>
                      <input type="tel" class="form-control" pattern="[7-9]{1}[0-9]{9}" name="contact" id="edit_contact"
                        placeholder="9123456789" maxlength="10" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="edit_email">Email address</label>
                    <input type="email" class="form-control" name="email" id="edit_email" placeholder="Email address"
                      autocomplete="off" oninput="email_validation(this)" required>
                    <!-- FOR INVALID EMAIL -->
                    <div class="input-group">
                      <div class="email-error-message text-danger"></div>
                    </div>
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

        <!-- VIEW PROFILE PICTURE -->
        <div class="modal fade" id="userProfilePictureModal" tabindex="-1"
          aria-labelledby="userProfilePictureModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="userProfilePictureModalLabel">Administrator Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                </button>
              </div>
              <div class="modal-body text-center">
                <img id="userProfile" src="default.jpg" alt="Candidate Image" class="rounded-circle d-block m-auto"
                  width="200" height="200" style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;">
                <h4 class="mt-3" id="UserProfileName">User Name</h4>
                <p class="text-secondary mb-1" id="userProfileType">Administrator</p>
              </div>
              <div class="modal-footer">
                <a id="downloadImageBtn" class="btn btn-primary btn-sm" download><i class="fa fa-download"></i>
                  Download</a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i>
                  Close</button>
              </div>
            </div>
          </div>
        </div>

        <!-- VIEW MODAL -->
        <div class="modal fade" id="userProfileModal" tabindex="-1" aria-labelledby="exampleModalLabel"
          aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header" id="userProfileModalLabel">
                <h5 class="modal-title">Administrator details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row gutters-sm">
                  <div class="col-md-4 mb-3">
                    <div class="card mt-3">
                      <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                          <img id="userImage" src="default.jpg" alt="Candidate Image" class="rounded-circle" width="150"
                            height="150" style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;">
                          <div class="mt-3">
                            <h4 id="UserName"></h4>
                            <p class="mb-1 badge badge-info" id="user_userType">Administrator</p>
                          </div>
                        </div>
                        <hr>
                        <p style="font-size: 15px;" id="userBirthday"><b>Birthday:</b> </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="card mb-3 mt-3">
                      <div class="card-body">
                        <div class="row mb-3">
                          <div class="col-sm-12 text-info">
                            <h6>Basic info:</h6>
                          </div>
                        </div>
                        <div class="row border-bottom mb-3">
                          <div class="col-sm-3">
                            <h6 class="mb-0">Nationality</h6>
                          </div>
                          <div class="col-sm-9 text-secondary">
                            <h6 id="usernationality"></h6>
                          </div>
                        </div>
                        <div class="row border-bottom mb-3">
                          <div class="col-sm-3">
                            <h6 class="mb-0">Gender</h6>
                          </div>
                          <div class="col-sm-9 text-secondary">
                            <h6 id="userGender"></h6>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="col-sm-12 text-info">
                            <h6>Contact details:</h6>
                          </div>
                        </div>
                        <div class="row border-bottom mb-3">
                          <div class="col-sm-3">
                            <h6 class="mb-0">Contact number</h6>
                          </div>
                          <div class="col-sm-9 text-secondary">
                            <h6 id="userContact"></h6>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-sm-3">
                            <h6 class="mb-0">Email</h6>
                          </div>
                          <div class="col-sm-9 text-secondary">
                            <h6 id="userEmail"></h6>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i>
                  Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1"><i
              class="fa fa-plus"></i> Add</button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1"><i class="fa fa-trash"></i>
            Delete</button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>PROFILE</th>
                <th>FULL NAME</th>
                <th>CONTACT NUMBER</th>
                <th>EMAIL</th>
                <th>DATE ADDED</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $instructor = $db->getAllAdminRecords($id);
              while ($row2 = $instructor->fetch_assoc()) {

                ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" value="<?= $row2['admin_ID'] ?>"></td>
                  <td>
                    <img src="../assets/img/admin/<?php echo $row2['image']; ?>" alt="" width="35" height="35"
                      class="img-circle d-block m-auto view-profile" data-image="<?= htmlspecialchars($row2['image']); ?>"
                      data-firstname="<?= htmlspecialchars($row2['firstname']); ?>"
                      data-middlename="<?= htmlspecialchars($row2['middlename']); ?>"
                      data-lastname="<?= htmlspecialchars($row2['lastname']); ?>"
                      data-suffix="<?= htmlspecialchars($row2['suffix']); ?>"
                      style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;">
                  </td>
                  <td>
                    <?= ucwords($row2['firstname'] . ' ' . $row2['middlename'] . ' ' . $row2['lastname'] . ' ' . $row2['suffix']) ?>
                  </td>
                  <td>+63<?= $row2['contact'] ?></td>
                  <td><?= $row2['email'] ?></td>
                  <td><?= date("F d, Y", strtotime($row2['created_at'])) ?></td>
                  <td>
                    <button type="button" class="btn btn-info btn-sm view-user" data-user-id="<?= $row2['admin_ID']; ?>"
                      data-firstname="<?= htmlspecialchars($row2['firstname']); ?>"
                      data-middlename="<?= htmlspecialchars($row2['middlename']); ?>"
                      data-lastname="<?= htmlspecialchars($row2['lastname']); ?>"
                      data-suffix="<?= htmlspecialchars($row2['suffix']); ?>"
                      data-gender="<?= htmlspecialchars($row2['gender']); ?>" data-birthdate="<?= $row2['birthdate']; ?>"
                      data-nationality="<?= htmlspecialchars($row2['nationality']); ?>"
                      data-contact="<?= htmlspecialchars($row2['contact']); ?>"
                      data-email="<?= htmlspecialchars($row2['email']); ?>"
                      data-image="<?= htmlspecialchars($row2['image']); ?>"
                      data-created-at="<?= htmlspecialchars($row2['created_at']); ?>">
                      <i class="fa fa-info-circle"></i>
                    </button>
                    <button type="button" class="btn btn-success btn-sm edit-user"
                      data-user-id="<?= $row2['admin_ID']; ?>"
                      data-firstname="<?= htmlspecialchars($row2['firstname']); ?>"
                      data-middlename="<?= htmlspecialchars($row2['middlename']); ?>"
                      data-lastname="<?= htmlspecialchars($row2['lastname']); ?>"
                      data-suffix="<?= htmlspecialchars($row2['suffix']); ?>"
                      data-gender="<?= htmlspecialchars($row2['gender']); ?>" data-birthdate="<?= $row2['birthdate']; ?>"
                      data-nationality="<?= htmlspecialchars($row2['nationality']); ?>"
                      data-contact="<?= htmlspecialchars($row2['contact']); ?>"
                      data-email="<?= htmlspecialchars($row2['email']); ?>"
                      data-image="<?= htmlspecialchars($row2['image']); ?>"
                      data-created-at="<?= htmlspecialchars($row2['created_at']); ?>">
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
      showSweetAlert("Please wait", "An email message will be sent to the newly registered user", "info"); //FORMAT: TITLE, TEXT, ICON
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
            showSweetAlert("Success!", response.message, "success", "admin.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
      const firstname = $(this).data('firstname');
      const middlename = $(this).data('middlename');
      const lastname = $(this).data('lastname');
      const suffix = $(this).data('suffix');
      const gender = $(this).data('gender');
      const birthdate = $(this).data('birthdate');
      const nationality = $(this).data('nationality');
      const contact = $(this).data('contact');
      const email = $(this).data('email');

      // Set values in the update modal
      $('#edit_user_ID').val(userID);
      $('#edit_firstname').val(firstname);
      $('#edit_middlename').val(middlename);
      $('#edit_lastname').val(lastname);
      $('#edit_suffix').val(suffix);
      $('#edit_gender').val(gender);
      $('#edit_birthdate').val(birthdate);
      $('#edit_nationality').val(nationality);
      $('#edit_contact').val(contact);
      $('#edit_email').val(email);

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
            showSweetAlert("Success!", response.message, "success", "admin.php"); //FORMAT: TITLE, TEXT, ICON, URL
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
        deleteMultipleRecords("administrator", "admin_ID", selectedIDs, "admin.php");
      });
    });

    // Populate update modal with candidate data
    $('#datatable').on('click', '.view-profile', function () {
      const firstname = $(this).data('firstname');
      const middlename = $(this).data('middlename');
      const lastname = $(this).data('lastname');
      const suffix = $(this).data('suffix');
      const image = $(this).data('image');

      // Set values in the profile modal
      $('#userProfilePictureModal #UserProfileName').text(firstname + ' ' + middlename + ' ' + lastname + ' ' + suffix);
      const imagePath = image ? '../assets/img/admin/' + image : 'avatar.png';
      $('#userProfilePictureModal #userProfile').attr('src', imagePath);

      // Set download link
      $('#downloadImageBtn').attr('href', imagePath);

      // Show the modal
      $('#userProfilePictureModal').modal('show');
    });

    $('#datatable').on('click', '.view-user', function () {
      const userID = $(this).data('user-id');
      const firstname = $(this).data('firstname');
      const middlename = $(this).data('middlename');
      const lastname = $(this).data('lastname');
      const suffix = $(this).data('suffix');
      const gender = $(this).data('gender');
      const birthdate = $(this).data('birthdate');
      const nationality = $(this).data('nationality');
      const contact = $(this).data('contact');
      const email = $(this).data('email');
      const image = $(this).data('image');
      const createdAt = $(this).data('created-at');

      // Set values in the profile modal
      $('#userProfileModal #UserName').text(firstname + ' ' + middlename + ' ' + lastname + ' ' + suffix);
      $('#userProfileModal #userBirthday').text('Birthday: ' + birthdate);
      $('#userProfileModal #usernationality').text(nationality);
      $('#userProfileModal #userGender').text(gender);
      $('#userProfileModal #userContact').text('+63' + contact);
      $('#userProfileModal #userEmail').text(email);
      $('#userProfileModal #userImage').attr('src', image ? '../assets/img/admin/' + image : 'avatar.png');
      $('#userProfileModal #userCreatedat').text(createdAt);

      // Show the modal
      $('#userProfileModal').modal('show');
    });

  });

</script>