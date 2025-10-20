<?php
  require_once 'header.php';
  if (isset($_GET['email']) && isset($_GET['id'])) {
    $email = $_GET['email'];
    $id = $_GET['id'];
    $fetch_user = $db->getUserByIDandEmail($id, $email);
    if ($fetch_user && $fetch_user['is_active'] == 1) {
      $codeQuery = $db->getVerificationCode($id, $email);
      $expiresAt = $codeQuery ? $codeQuery['expires_at'] : null;
      ?>
      <div class="login-container">
        <h1 class="login-title text-uppercase"><?= $systemName ?></h1>
        <div class="card shadow-lg border-0 rounded-3 login-card">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h4 class="mt-3">ENTER SECURITY CODE</h4>
              <p>Check your email for a message with your code. Your code is 6 numbers long.</p>
            </div>
            <form method="POST" id="verifyCodeForm">
              <input type="hidden" name="email" value="<?= $email; ?>">
              <input type="hidden" name="user_id" value="<?= $id; ?>">
              <div class="form-group">
                <label for="code" class="form-label">Security Code</label>
                <input type="number" name="code" class="form-control" id="code" placeholder="Enter verification code"
                  minlength="6" maxlength="6" required="">
                  <?php if ($expiresAt): ?>
                    <p id="countdown" class="text-warning fw-bold"></p>
                    <script>
                      const expiresAt = new Date("<?= $expiresAt ?>").getTime();
                    </script>
                  <?php endif; ?>
              </div>
              <div class="form-group d-flex justify-content-between align-items-center mb-3">
                <span>Didn't get a code?
                  <a href="send-code.php?email=<?= $email ?>&id=<?= $id ?>" class="text-decoration-none text-light"><strong>Request new one</strong></a> <br>
                  <a href="<?= $redirectURL ?>" class="text-light"><strong>Login</strong></a>
                </span>
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
<script>
  if (typeof expiresAt !== "undefined") {
    const countdownEl = document.getElementById("countdown");
    
    function updateCountdown() {
      const now = new Date().getTime();
      const distance = expiresAt - now;

      if (distance <= 0) {
        countdownEl.innerHTML = "Code expired. Please request a new one.";
        countdownEl.classList.remove("text-danger");
        countdownEl.classList.add("text-secondary");
        clearInterval(timer);
        return;
      }

      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      countdownEl.innerHTML = `Code will expire in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }

    updateCountdown(); 
    const timer = setInterval(updateCountdown, 1000);
  }
</script>
