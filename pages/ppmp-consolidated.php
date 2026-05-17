<?php
require_once '../php/auth_check.php';
if (!($canApprovePPMP && $canViewReports && $canManageBudget)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';

$currentFY = $db->getCurrentFiscalYear();
$currentFiscalYearId = $currentFY['fiscal_year_id'] ?? null;
$currentFiscalYearLabel = $currentFY['year'] ?? 'No Fiscal Year';

$consolidatedItems = $db->getConsolidatedPPMPItems($currentFiscalYearId);
$draftApp = $db->getCurrentDraftAppByFiscalYear($currentFiscalYearId);

function formatAccountingPHP($amount)
{
  $n = (float) $amount;
  $abs = number_format(abs($n), 2);
  return ($n < 0) ? "(₱{$abs})" : "₱{$abs}";
}
?>
<style>
  .select2-container {
    z-index: 9999 !important;
  }

  .select2-dropdown {
    z-index: 999999 !important;
  }

  .select2-container--open {
    z-index: 999999 !important;
  }

  #EditAPPItemModal {
    overflow-y: auto !important;
  }

  #EditAPPItemModal .select2-container {
    width: 100% !important;
  }

  .select2-container--open {
    z-index: 999999 !important;
  }

  .select2-container--default.select2-container--open .select2-selection--single {
    border-bottom-left-radius: 4px !important;
    border-bottom-right-radius: 4px !important;
  }
</style>
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Consolidated PPMPs</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <div class="d-flex justify-content-between align-items-center w-100">
          <h2 class="mb-0">Consolidated PPMPs for APP Finalization</h2>
          <span class="text-dark mr-3" style="font-size: 0.95rem;">
            Fiscal Year: <strong><?= htmlspecialchars($currentFiscalYearLabel); ?></strong>
          </span>
        </div>
        <div class="clearfix"></div>
      </div>

      <div class="x_content">

        <!-- EDIT ITEM MODAL -->
        <div class="modal fade" id="EditAPPItemModal" tabindex="-1" role="dialog" data-backdrop="static"
          data-keyboard="false" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <form id="SaveAppItemForm" autocomplete="off">
              <input type="hidden" name="app_item_id" id="app_item_id">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title">Edit APP Item Details</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>

                <div class="modal-body">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <h5 class="fw-bold">Consolidated Item Info</h5>

                      <div class="form-group">
                        <label class="form-label fw-bold">Category</label>
                        <input type="text" id="view_category" class="form-control" readonly>
                      </div>

                      <div class="form-group">
                        <label class="form-label fw-bold">Subcategory</label>
                        <input type="text" id="view_subcategory" class="form-control" readonly>
                      </div>

                      <div class="form-group">
                        <label class="form-label fw-bold">Item Name</label>
                        <input type="text" id="view_item_name" class="form-control" readonly>
                      </div>

                      <div class="form-group">
                        <label class="form-label fw-bold">Offices Involved</label>
                        <div id="view_offices" class="border rounded p-2 bg-light" style="min-height: 80px;"></div>
                      </div>

                      <div class="form-group">
                        <label for="item_description" class="form-label fw-bold">Project Description</label>
                        <textarea class="form-control" name="item_description" id="item_description" rows="2"
                          required></textarea>
                      </div>

                      <h5 class="fw-bold mt-3">Procurement Details</h5>

                      <div class="form-group">
                        <label for="mode_of_procurement" class="form-label fw-bold">Mode of Procurement</label>
                        <?php $procurementModes = $db->getProcMode(); ?>
                        <select class="form-control select2" name="mode_of_procurement" id="mode_of_procurement"
                          required>
                          <option value="" disabled>Select Mode of Procurement</option>
                          <?php while ($mode = $procurementModes->fetch_assoc()): ?>
                            <option value="<?= (int) $mode['proc_mode_id'] ?>">
                              <?= htmlspecialchars($mode['proc_mode_name']) ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>

                      <div class="form-group">
                        <label for="pre_procurement_conference" class="form-label fw-bold">To be covered by an Early
                          Procurement Activity</label>
                        <select class="form-control" name="pre_procurement_conference" id="pre_procurement_conference"
                          required>
                          <option value="" disabled selected>Select an option</option>
                          <option value="Yes">Yes</option>
                          <option value="No">No</option>
                        </select>
                      </div>

                      <div class="form-group">
                        <label for="bid_cat_ID" class="form-label fw-bold">Bidding Category</label>
                        <select class="form-control" name="bid_cat_ID" id="bid_cat_ID" required>
                          <option value="" disabled selected>Select Bidding Category</option>
                          <?php
                          $biddingCategories = $db->getBiddingCategory();
                          while ($row = $biddingCategories->fetch_assoc()) {
                            echo '<option value="' . (int) $row['bid_cat_ID'] . '">' . htmlspecialchars($row['bid_cat_name']) . '</option>';
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6 mb-3">
                      <h5 class="fw-bold">Projected Timeline</h5>

                      <div class="form-group">
                        <label for="procurement_start_date" class="form-label fw-bold">Start of Procurement
                          Activity</label>
                        <input type="date" class="form-control" name="procurement_start_date"
                          id="procurement_start_date" min="<?= date('Y-m-d') ?>" required>
                      </div>

                      <div class="form-group">
                        <label for="bidding_date" class="form-label fw-bold">End of Procurement Activity</label>
                        <input type="date" class="form-control" name="bidding_date" id="bidding_date"
                          min="<?= date('Y-m-d') ?>" required>
                        <small id="bidding_date_error" class="text-danger"></small>
                      </div>

                      <h5 class="fw-bold mt-3">Funding Details</h5>

                      <div class="form-group">
                        <label for="source_of_funds" class="form-label fw-bold">Source of Funds</label>
                        <select class="form-control select2" name="source_of_funds" id="source_of_funds" required>
                          <option value="" disabled selected>Select Source of Funds</option>
                          <option value="Current Appropriation">Current Appropriation</option>
                          <option value="Continuing Appropriation">Continuing Appropriation</option>
                          <option value="Corporate Operating Budget">Corporate Operating Budget</option>
                          <option value="Appropriation Ordinance">Appropriation Ordinance</option>
                          <option value="Internally Generated Income">Internally Generated Income</option>
                          <option value="Special Purpose Fund">Special Purpose Fund</option>
                          <option value="Trust Fund">Trust Fund</option>
                          <option value="Foreign-Assisted Fund">Foreign-Assisted Fund</option>
                        </select>
                      </div>

                      <h5 class="fw-bold mt-3">Procurement Tools</h5>

                      <div class="form-group">
                        <label for="proc_strat_ID" class="form-label fw-bold">Procurement Strategy</label>
                        <select class="form-control select2" name="proc_strat_ID" id="proc_strat_ID" required>
                          <option value="" disabled selected>Select Procurement Strategy</option>
                          <?php
                          $procStrategies = $db->getProcStrategy();
                          while ($row = $procStrategies->fetch_assoc()) {
                            echo '<option value="' . (int) $row['proc_strat_ID'] . '">' . htmlspecialchars($row['proc_strat_name']) . '</option>';
                          }
                          ?>
                          <option value="others">Others</option>
                        </select>
                      </div>

                      <div class="form-group">
                        <label for="remarks" class="form-label fw-bold">Remarks / Other Details</label>
                        <textarea class="form-control" name="remarks" id="remarks" rows="3"></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa fa-save"></i> Save Items
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="modal fade" id="ViewOfficePPMPModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header bg-light">
                <h5 class="modal-title">Submitted PPMP Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                  <span><i class="fa fa-times-circle"></i></span>
                </button>
              </div>

              <div class="modal-body">
                <div id="officePPMPContent"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-box bg-white table-responsive">
          <div class="d-flex justify-content-end align-items-center flex-wrap mb-3 mr-2" style="gap:10px;">
            <button type="button" id="FinalizeEntireAPPBtn" class="btn btn-success btn-sm" <?= $draftApp ? '' : 'disabled' ?>>
              <i class="fa fa-check-circle"></i> Finalize APP
            </button>
          </div>

          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th class="text-center">CATEGORY CODE</th>
                <th class="text-center">SUB-CATEGORY</th>
                <th>ITEM DESCRIPTION</th>
                <th class="text-center">TOTAL QTY</th>
                <th class="text-right">TOTAL CONSOLIDATED COST</th>
                <th>OFFICES INVOLVED</th>
                <th class="text-center">DETAILS STATUS</th>
                <th class="text-center">ACTION</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $consolidatedItems->fetch_assoc()): ?>
                <?php
                $totalCost = formatAccountingPHP($row['total_cost']);
                $sourceRows = $db->getAppItemOfficePPMPSources((int) $row['app_item_id']);

                $officeListHtml = '<ul style="list-style:none; padding:0; margin:0;">';
                $officeTitleList = [];

                while ($source = $sourceRows->fetch_assoc()) {
                  $officeDisplay = trim($source['office_display'] ?? $source['office_name']);
                  $projectDescription = trim($source['app_item_description'] ?: $source['item_description']);

                  $officeLabel = $officeDisplay . ' - ' . $projectDescription;
                  $officeTitleList[] = $officeLabel;

                  $officeListHtml .= '
        <li class="mb-1">
          ❖ <a href="javascript:void(0)"
                class="view-office-ppmp"
                data-app-item-id="' . (int) $row['app_item_id'] . '"
                data-office-id="' . (int) $source['office_id'] . '">
                ' . htmlspecialchars($officeLabel) . '
             </a>
        </li>';
                }

                $officeListHtml .= '</ul>';
                $officeTitleText = implode(', ', $officeTitleList);

                $isCompleted = !empty($row['mode_of_procurement']) &&
                  !empty($row['pre_procurement_conference']) &&
                  !empty($row['bid_cat_ID']) &&
                  !empty($row['procurement_start_date']) &&
                  !empty($row['bidding_date']) &&
                  !empty($row['source_of_funds']) &&
                  !empty($row['proc_strat_ID']);
                ?>

                <tr>
                  <td class="text-center">
                    <span title="<?= htmlspecialchars($row['category_name']); ?>">
                      <?= htmlspecialchars($row['category_code']); ?>
                    </span>
                  </td>
                  <td class="text-center"><?= htmlspecialchars($row['sub_cat_name']); ?></td>
                  <td><?= htmlspecialchars($row['item_name']); ?></td>
                  <td class="text-center fw-bold"><?= htmlspecialchars($row['total_quantity']); ?></td>
                  <td class="text-right fw-bold"><?= $totalCost; ?></td>

                  <td title="<?= htmlspecialchars($officeTitleText); ?>">
                    <?= $officeListHtml; ?>
                  </td>

                  <td class="text-center">
                    <?php if ($isCompleted): ?>
                      <span class="badge badge-success p-2">Completed</span>
                    <?php else: ?>
                      <span class="badge badge-warning p-2 text-dark">Incomplete</span>
                    <?php endif; ?>
                  </td>

                  <td class="text-center">
                    <button type="button" class="btn btn-primary btn-sm edit-app-item-btn" data-toggle="modal"
                      data-target="#EditAPPItemModal" data-app-item-id="<?= (int) $row['app_item_id'] ?>"
                      data-category="<?= htmlspecialchars($row['category_name'], ENT_QUOTES) ?>"
                      data-subcategory="<?= htmlspecialchars($row['sub_cat_name'], ENT_QUOTES) ?>"
                      data-item-name="<?= htmlspecialchars($row['item_name'], ENT_QUOTES) ?>"
                      data-offices="<?= htmlspecialchars($officeTitleText, ENT_QUOTES) ?>"
                      data-item-description="<?= htmlspecialchars($row['item_description'] ?? '', ENT_QUOTES) ?>"
                      data-mode-of-procurement="<?= (int) ($row['mode_of_procurement'] ?? 0) ?>"
                      data-pre-procurement-conference="<?= htmlspecialchars($row['pre_procurement_conference'] ?? '', ENT_QUOTES) ?>"
                      data-bid-cat-id="<?= (int) ($row['bid_cat_ID'] ?? 0) ?>"
                      data-procurement-start-date="<?= htmlspecialchars($row['procurement_start_date'] ?? '', ENT_QUOTES) ?>"
                      data-bidding-date="<?= htmlspecialchars($row['bidding_date'] ?? '', ENT_QUOTES) ?>"
                      data-source-of-funds="<?= htmlspecialchars($row['source_of_funds'] ?? '', ENT_QUOTES) ?>"
                      data-proc-strat-id="<?= (int) ($row['proc_strat_ID'] ?? 0) ?>"
                      data-remarks="<?= htmlspecialchars($row['remarks'] ?? '', ENT_QUOTES) ?>">
                      <i class="fa fa-pencil"></i> Edit
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  $(document).ready(function () {

    $(document).on('click', '.view-office-ppmp', function () {
      const appItemId = $(this).data('app-item-id');
      const officeId = $(this).data('office-id');

      $('#officePPMPContent').html(`
    <div class="text-center p-4">
      <i class="fa fa-spinner fa-spin"></i> Loading PPMP details...
    </div>
  `);

      $('#ViewOfficePPMPModal').modal('show');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: {
          action: 'GetSubmittedPPMPItemsByOffice',
          office_id: officeId
        },
        dataType: 'json',
        success: function (response) {
          if (!response.success) {
            $('#officePPMPContent').html(`<div class="alert alert-danger">${response.message}</div>`);
            return;
          }

          const rows = response.data;

          if (!rows.length) {
            $('#officePPMPContent').html(`<div class="alert alert-warning">No submitted PPMP found.</div>`);
            return;
          }

          let html = `
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Office</th>
                <th>PPMP Code</th>
                <th>Project Description</th>
                <th>Specifications</th>
                <th class="text-right">Estimated Budget</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total Cost</th>
                <th class="text-center">Attachment</th>
              </tr>
            </thead>
            <tbody>
      `;

          rows.forEach(item => {
            let attachment = `<span class="badge badge-secondary">No File</span>`;

            if (item.file_attachment && item.file_attachment.trim() !== '') {
              const files = item.file_attachment
                .split(',')
                .map(file => file.trim())
                .filter(Boolean);

              attachment = files.map((file, index) => {
                const fileUrl = `../assets/ppmp_attachments/${encodeURIComponent(file)}`;

                return `
                  <a href="${fileUrl}" target="_blank" class="btn btn-info btn-sm mb-1">
                    <i class="fa fa-eye"></i> View File ${files.length > 1 ? index + 1 : ''}
                  </a>
                `;
              }).join(' ');
            }

            html += `
          <tr>
            <td>${escapeHtml(item.office_name)}</td>
            <td>${escapeHtml(item.ppmp_code)}</td>
            <td>${escapeHtml(item.item_description)}</td>
            <td>${escapeHtml(item.specifications || '')}</td>
            <td class="text-right">₱${item.estimated_budget}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-right">₱${item.total_cost}</td>
            <td class="text-center">${attachment}</td>
          </tr>
        `;
          });

          html += `</tbody></table></div>`;
          $('#officePPMPContent').html(html);
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          $('#officePPMPContent').html(`<div class="alert alert-danger">Failed to load PPMP details.</div>`);
        }
      });
    });

    function escapeHtml(value) {
      return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    }



    $.fn.modal.Constructor.prototype.enforceFocus = function () { };

    const $appModal = $('#EditAPPItemModal');

    $('#mode_of_procurement, #source_of_funds, #proc_strat_ID').select2({
      dropdownParent: $appModal.find('.modal-content'),
      width: '100%',
      allowClear: false,
      closeOnSelect: true
    });

    function validateDateSequence() {
      const start = $('#procurement_start_date').val();
      const bidding = $('#bidding_date').val();

      if (start && bidding && new Date(bidding) < new Date(start)) {
        $('#bidding_date_error').text('End date must be on or after start date.');
        return false;
      }

      $('#bidding_date_error').text('');
      return true;
    }

    $(document).on('click', '.edit-app-item-btn', function () {
      const btn = $(this);

      $('#app_item_id').val(btn.data('app-item-id') || '');
      $('#view_category').val(btn.data('category') || '');
      $('#view_subcategory').val(btn.data('subcategory') || '');
      $('#view_item_name').val(btn.data('item-name') || '');

      const offices = (btn.data('offices') || '').split(',').map(x => x.trim()).filter(Boolean);
      let officeHtml = '<ul style="list-style:none; padding-left:0; margin-bottom:0;">';
      offices.forEach(function (office) {
        officeHtml += `<li>❖ ${office}</li>`;
      });
      officeHtml += '</ul>';
      $('#view_offices').html(officeHtml);

      $('#item_description').val('');
      // $('#item_description').val(btn.data('item-description') || '');
      $('#procurement_start_date').val(btn.data('procurement-start-date') || '');
      $('#bidding_date').val(btn.data('bidding-date') || '');
      $('#remarks').val(btn.data('remarks') || '');

      $('#pre_procurement_conference').val(String(btn.data('pre-procurement-conference') || ''));
      $('#bid_cat_ID').val(String(btn.data('bid-cat-id') || ''));
      $('#proc_strat_ID').val(String(btn.data('proc-strat-id') || ''));

      setTimeout(function () {
        $('#mode_of_procurement').val(String(btn.data('mode-of-procurement') || '')).trigger('change');
        $('#source_of_funds').val(String(btn.data('source-of-funds') || '')).trigger('change');
      }, 50);

      $('#bidding_date_error').text('');
    });

    $('#procurement_start_date, #bidding_date').on('change', function () {
      validateDateSequence();
    });

    $('#SaveAppItemForm').on('submit', function (e) {
      e.preventDefault();

      if (!validateDateSequence()) {
        return;
      }

      const formData = new FormData(this);
      formData.append('action', 'SaveAppItemDetails');

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            showSweetAlert('Success!', response.message, 'success', 'ppmp-consolidated.php');
          } else {
            showSweetAlert('Error', response.message, 'error');
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          showSweetAlert('Error', 'An unexpected error occurred while saving item details.', 'error');
        }
      });
    });

    $('#FinalizeEntireAPPBtn').on('click', function () {
      Swal.fire({
        title: 'Finalize Entire APP?',
        text: 'This will finalize the current APP draft once all items are complete.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, finalize it'
      }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
          type: 'POST',
          url: '../php/processes.php',
          data: {
            action: 'FinalizeEntireApp'
          },
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              showSweetAlert('Success!', response.message, 'success', 'ppmp-consolidated.php');
            } else {
              showSweetAlert('Error', response.message, 'error');
            }
          },
          error: function (xhr) {
            console.error(xhr.responseText);
            showSweetAlert('Error', 'An unexpected error occurred while finalizing APP.', 'error');
          }
        });
      });
    });
  });
</script>