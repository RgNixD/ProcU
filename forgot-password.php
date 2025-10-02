<?php require_once 'header.php'; ?>

<div class="login-container">
  <h1 class="login-title text-uppercase"><?= $systemName ?></h1>

  <div class="card shadow-lg border-0 rounded-3 login-card">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <h4 class="mt-3">FORGOT PASSWORD</h4>
        <p>Enter your email to search for your account.</p>
      </div>
      <form method="POST" id="forgotPasswordForm">
        <div class="form-group mt-4">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
            autocomplete="off" required>
        </div>
        <div class="form-group d-flex justify-content-between align-items-center mb-3">
          <span><a href="index.php" class="text-decoration-none text-light"><strong>Login</strong></a></span>
          <button type="submit" class="btn text-light px-4">Search</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>