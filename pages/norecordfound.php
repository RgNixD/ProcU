<?php require_once 'sidebar.php'; ?>

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Page Not Found</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-md-12">
      <div class="col-middle">
        <div class="text-center">
          <h1 class="error-number">404</h1>
          <h2>Sorry, but we couldn't find the record you are looking for.</h2>
          <p>The page you are looking for does not exist.</p>
          <div class="mid_center">
            <!-- Close Button -->
            <button onclick="closeWindow()" class="btn btn-danger">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<!-- /page content -->

<script>
  function closeWindow() {
    window.close();
  }
</script>

<?php require_once 'footer.php'; ?>
