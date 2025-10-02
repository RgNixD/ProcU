<?php require_once 'header.php'; ?>

<div class="login-container">
  <h1 class="login-title text-uppercase"><?= $systemName ?></h1>

  <div class="card shadow-lg border-0 rounded-3 login-card">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <h4 class="mt-3">LOGIN</h4>
      </div>
      <form method="POST" id="loginForm">
        <div class="form-group mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="username" class="form-control" id="username" name="username" placeholder="Enter your username"
            autocomplete="off" required>
        </div>
        <div class="form-group mb-3 position-relative">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password"
            required>
          <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
        </div>
        <div class="form-group d-flex justify-content-between align-items-center mb-3">
          <span>Forgot your password?
            <a href="forgot-password.php" class="text-decoration-none text-light"><strong>Click here.</strong></a>
          </span>
          <button type="submit" class="btn text-light px-4">Login</button>
        </div>
        <!-- <div class="form-group mb-3 text-center"><a href="index.php" class="text-decoration-none">Home</a></div> -->
      </form>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>