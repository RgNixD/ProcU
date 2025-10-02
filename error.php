<div class="card shadow-lg border-0">
  <div class="card-body  text-center">

    <div class="mb-4">
      <img src="<?= $systemLogo ?>" alt="System Logo" class="login-logo" width="150">
    </div>

    <h1 class="display-4 text-danger">404</h1>
    <h4 class="mb-3"><?= isset($error_title) ? $error_title : "Page Not Found" ?></h4>
    <p class="text-muted mb-4"><?= isset($error_message) ? $error_message : "The page you are looking for might have been removed, had its name changed, or is
      temporarily unavailable." ?></p>

    <div class="d-grid gap-2 d-md-flex justify-content-center">
      <a href="index.php" class="btn btn-primary">Go to Home</a>
      <a href="javascript:history.back()" class="btn btn-outline-secondary">Go Back</a>
    </div>

  </div>
</div>