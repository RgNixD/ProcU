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
              <h4 class="mt-3">VERIFY YOUR IDENTITY</h4>
              <p>We’ll send a verification code to your email.</p>
            </div>
            <form method="POST" id="sendCodeForm">
              <input type="hidden" name="email" value="<?= $email; ?>">
              <input type="hidden" name="user_id" value="<?= $id; ?>">
              <div class="text-center mb-4">
                <img src="assets/img/user-profiles/<?= $fetch_user['profile'] ?>" alt="Profile Image"
                  class="rounded-circle shadow-sm mb-3" style="width:90px; height:90px; object-fit:cover;">
                <h6 class="fw-semibold mb-1"><?= ucwords($fetch_user['first_name'] . ' ' . $fetch_user['last_name']) ?></h6>
                <p>Code will be sent to: <strong><?= $email; ?></strong></p>
              </div>
              <div class="form-group d-flex justify-content-between align-items-center mb-3">
                <span><a href="forgot-password.php" class="text-decoration-none text-light"><strong>Not
                      you?</strong></a></span>
                <button type="submit" class="btn text-light px-4">Continue</button>
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