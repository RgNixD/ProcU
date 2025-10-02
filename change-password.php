<?php
  require_once 'header.php';
  if (isset($_GET['email']) && isset($_GET['id'])) {
    $email = $_GET['email'];
    $id = $_GET['id'];
    $fetch_user = $db->getUserByIDandEmail($id, $email);
    if ($fetch_user && $fetch_user['is_active'] == 1) {
      ?>
      <div class="login-container">
        <h1 class="login-title text-uppercase"><?= $systemName ?></h1>
        <div class="card shadow-lg border-0 rounded-3 login-card">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h4 class="mt-3">CREATE NEW PASSWORD</h4>
              <p>Create a new password (min. 8 characters) using letters, numbers, and symbols.</p>
            </div>
            <form method="POST" id="changePasswordForm">
              <input type="hidden" name="email" value="<?= $email; ?>">
              <input type="hidden" name="user_id" value="<?= $id; ?>">
              <div class="form-group mt-3">
                <label for="password" class="form-label">New password</label>
                <input type="password" class="form-control text-dark" name="password" id="password"
                  onkeyup="validate_confirm_password(); passwordStrengthCheck()" placeholder="Password" required=""
                  minlength="8">
                <span id="password-message" class="text-bold"></span>
              </div>

              <div class="form-group mt-3">
                <label for="cpassword" class="form-label">Confirm new password</label>
                <input type="password" class="form-control text-dark" name="cpassword" id="cpassword"
                  onkeyup="validate_confirm_password()" placeholder="Confirm new password" required="" minlength="8">
                <span id="confirm_pass_alert" class="text-bold"></span>
              </div>

              <div class="form-group ml-4">
                <input type="checkbox" class="form-check-input" id="showAllPasswords" onchange="toggleAllPasswords()">
                <label class="form-check-label" for="showAllPasswords">Show password</label>
              </div>
              <div class="form-group d-flex justify-content-between align-items-center mb-3">
                <span>
                  <a href="<?= $redirectURL ?>" class="text-light"><strong>Login</strong></a>
                </span>
                <button type="submit" class="btn text-light px-4" id="submit_button">Continue</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <?php
    } else {
      require_once 'error.php';
    }
  } else {
    $error_title = "Invalid Request";
    $error_message = "The required parameters are missing. Please try again.";
    require_once 'error.php';
  }
?>
<?php require_once 'footer.php'; ?>