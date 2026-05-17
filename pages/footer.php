<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br><br><br>

<footer>
  <div class="pull-right">
    <?= $clientName ?><a href="#"></a>
  </div>
  <div class="clearfix"></div>
</footer>
</div>
</div>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<!-- FastClick -->
<script src="../vendors/fastclick/lib/fastclick.js"></script>
<!-- NProgress -->
<script src="../vendors/nprogress/nprogress.js"></script>
<!-- jQuery Sparklines -->
<script src="../vendors/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<!-- easy-pie-chart -->
<script src="../vendors/jquery.easy-pie-chart/dist/jquery.easypiechart.min.js"></script>
<!-- Chart.js -->
<script src="../vendors/Chart.js/dist/Chart.min.js"></script>
<!-- gauge.js -->
<script src="../vendors/gauge.js/dist/gauge.min.js"></script>
<!-- bootstrap-progressbar -->
<script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<!-- iCheck -->
<script src="../vendors/iCheck/icheck.min.js"></script>
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>
<!-- Flot -->
<script src="../vendors/Flot/jquery.flot.js"></script>
<script src="../vendors/Flot/jquery.flot.pie.js"></script>
<script src="../vendors/Flot/jquery.flot.time.js"></script>
<script src="../vendors/Flot/jquery.flot.stack.js"></script>
<script src="../vendors/Flot/jquery.flot.resize.js"></script>
<!-- Flot plugins -->
<script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
<script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
<script src="../vendors/flot.curvedlines/curvedLines.js"></script>
<!-- DateJS -->
<script src="../vendors/DateJS/build/date.js"></script>
<!-- JQVMap -->
<script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
<script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
<script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
<!-- bootstrap-daterangepicker -->
<script src="../vendors/moment/min/moment.min.js"></script>
<script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- Datatables -->
<script src="../vendors/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
<script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
<script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
<script src="../vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
<script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
<script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
<script src="../vendors/jszip/dist/jszip.min.js"></script>
<script src="../vendors/pdfmake/build/pdfmake.min.js"></script>
<script src="../vendors/pdfmake/build/vfs_fonts.js"></script>
<!-- bootstrap-wysiwyg -->
<script src="../vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js"></script>
<script src="../vendors/jquery.hotkeys/jquery.hotkeys.js"></script>
<script src="../vendors/google-code-prettify/src/prettify.js"></script>
<!-- jQuery Tags Input -->
<script src="../vendors/jquery.tagsinput/src/jquery.tagsinput.js"></script>
<!-- Switchery -->
<script src="../vendors/switchery/dist/switchery.min.js"></script>
<!-- Select2 -->
<script src="../vendors/select2/dist/js/select2.full.min.js"></script>
<!-- CAN BE REMOVED, -->
<!-- Parsley -->
<!-- <script src="../vendors/parsleyjs/dist/parsley.min.js"></script> -->
<!-- CAN BE REMOVED, -->
<!-- Autosize -->
<script src="../vendors/autosize/dist/autosize.min.js"></script>
<!-- jQuery autocomplete -->
<script src="../vendors/devbridge-autocomplete/dist/jquery.autocomplete.min.js"></script>
<!-- starrr -->
<script src="../vendors/starrr/dist/starrr.js"></script>
<!-- jQuery Smart Wizard -->
<script src="../vendors/jQuery-Smart-Wizard/js/jquery.smartWizard.js"></script>
<!-- FullCalendar -->
<script src="../vendors/moment/min/moment.min.js"></script>
<script src="../vendors/fullcalendar/dist/fullcalendar.min.js"></script>
<!-- Chart.js -->
<script src="../vendors/Chart.js/dist/Chart.min.js"></script>
<!-- ECharts -->
<script src="../vendors/echarts/dist/echarts.min.js"></script>
<script src="../vendors/echarts/map/js/world.js"></script>
<!-- PNotify -->
<script src="../vendors/pnotify/dist/pnotify.js"></script>
<script src="../vendors/pnotify/dist/pnotify.buttons.js"></script>
<script src="../vendors/pnotify/dist/pnotify.nonblock.js"></script>
<!-- MY CUSTOM CSS -->
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script src="../assets/js/alerts.js"></script>
<!-- Custom JS -->
<script src="../assets/js/script.js"></script>
<!-- Custom Theme Scripts -->
<!-- <script src="../build/js/custom.min.js"></script> -->
 
 <script>
  $(document).ready(function() {

    $('#menu_toggle').on('click', function() {
        var body = $('body');
        var leftCol = $('.left_col');
        var sidebarMenu = $('.main_menu_side');

        body.toggleClass('nav-md nav-sm');

        if (body.hasClass('nav-sm')) {
            leftCol.css('position', 'fixed');
            sidebarMenu.find('ul.child_menu').hide(); 
        } else {
            leftCol.css('position', 'fixed');
            sidebarMenu.find('ul.child_menu').show(); 
        }
    });

    $('.nav.side-menu > li > a').on('click', function(e) {
        var $li = $(this).parent();
        var $submenu = $li.children('ul.child_menu');

        if ($submenu.length) {
            e.preventDefault();

            if ($li.hasClass('active')) {
                $li.removeClass('active');
                $submenu.slideUp();
            } else {
                // Close other submenus
                $('.nav.side-menu li.active').removeClass('active').children('ul.child_menu').slideUp();

                $li.addClass('active');
                $submenu.slideDown();
            }
        }
    });

    $('.nav.side-menu li ul.child_menu').on('click', function(e) {
        e.stopPropagation();
    });
    $('.nav.side-menu li').hover(function() {
      var $submenu = $(this).children('ul.child_menu');
      if ($submenu.length) {
          var offset = $submenu.offset();
          var width = $submenu.outerWidth();
          var viewportWidth = $(window).width();
          if (offset.left + width > viewportWidth) {
              $submenu.css('left', '-100%');
          } else {
              $submenu.css('left', '100%');
          }
      }
    });

    $('#datatable').DataTable();
    $('#datatable2').DataTable();

    var url = window.location.href;

    $('#sidebar-menu a').each(function () {
        if (this.href === url) {

            $(this).addClass('current-page');
            $(this).parent().addClass('active');

            $(this).closest('ul').slideDown();
            $(this).closest('ul').parent().addClass('active');
        }
    });


    $('.view-notification').on('click', function () {
        const notifId = $(this).data('id');
        const message = $(this).find('.message').text();

        $('#notifMessage').text(message);
        $('#NotificationModal').modal('show');

        $.post('../php/processes.php', { action: 'MarkNotificationRead', notification_id: notifId }, function (res) {
            if (res.success) {
                let badge = $('#notifBadge');
                let count = parseInt(badge.text());
                badge.text(Math.max(0, count - 1));

                $(`.view-notification[data-id=${notifId}]`).remove();
            }
        }, 'json');
    });

  });

  document.addEventListener("DOMContentLoaded", function () {

    let timeout;
    const LOGOUT_URL = "../php/processes.php";
    const REDIRECT_URL = "../index.php";

    // Unified logout handler
    function logoutUser() {
      $.ajax({
        type: "POST",
        url: LOGOUT_URL,
        data: { action: "logout_user" },
        complete: function () {
          window.location.href = "../index.php";
        }
      });
    }

    // Auto logout with timer
    function resetTimer() {
      clearTimeout(timeout);
      // timeout = setTimeout(autoLogout, 5 * 1000); // logout in 5 seconds for demo
      // timeout = setTimeout(autoLogout, 15 * 60 * 1000); // logout in 15 minutes
      timeout = setTimeout(autoLogout, 160 * 160 * 1000); // logout in 15 minutes
    }

    function autoLogout() {
      Swal.fire({
        title: "Session Timeout",
        text: "You have been logged out due to inactivity.",
        icon: "info",
        timer: 2000,
        showConfirmButton: false
      }).then(() => logoutUser("Logging out..."));
    }

    ["load", "mousemove", "keydown", "click", "scroll"].forEach(evt => {
      window.addEventListener(evt, resetTimer);
    });

    document.getElementById("signOutButton").addEventListener("click", function (event) {
      event.preventDefault();

      Swal.fire({
        title: "Are you sure you want to logout?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Confirm",
        timer: 10000,
        timerProgressBar: true,
        confirmButtonColor: "#800000",
        cancelButtonColor: "#555555",
        customClass: {
          confirmButton: 'swal2-confirm-custom',
          cancelButton: 'swal2-cancel-custom'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          logoutUser();
        }
      });
    });

  });
</script>

</body>

</html>
