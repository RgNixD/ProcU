<?php
require_once '../php/auth_check.php';
if (!($canApprovePPMP && $canViewReports && $canManageBudget)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';

$currentFY = $db->getCurrentFiscalYear();
$currentFiscalYearId = $currentFY['fiscal_year_id'] ?? null;

$selectedFiscalYearId = isset($_GET['fiscal_year_id'])
  ? (int) $_GET['fiscal_year_id']
  : $currentFiscalYearId;

$selectedFY = $db->getFiscalYearById($selectedFiscalYearId);
$fiscalYearText = $selectedFY['year'] ?? 'N/A';

$finalizedApps = $db->getFinalizedApps($selectedFiscalYearId);
$years = $db->getFiscalYears();

$directAcquisitionItems = $db->getFinalizedAppsByProcurementType($selectedFiscalYearId, 'Direct Acquisition');
$cseItems = $db->getFinalizedAppsByProcurementType($selectedFiscalYearId, 'Common Use Supplies and Equipment');

function renderBlankRows($rows = 5, $cols = 12)
{
  for ($i = 0; $i < $rows; $i++) {
    echo '<tr>';
    for ($j = 0; $j < $cols; $j++) {
      echo '<td></td>';
    }
    echo '</tr>';
  }
}
?>
<style>
  .table-bordered th,
  .table-bordered td {
    padding: 0.7rem 0.5rem;
    vertical-align: top;
    line-height: 1.2;
  }

  .table-bordered th {
    font-size: 0.7rem;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
    background-color: #f8f9fa;
  }

  .allow-revision-popup .swal2-html-container {
    overflow: visible !important;
  }

  .select2-container {
    z-index: 999999 !important;
  }

  .select2-dropdown {
    z-index: 999999 !important;
  }

  .select2-results__option {
    white-space: normal !important;
  }
</style>

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Annual Procurement Plans for F.Y. <?= htmlspecialchars($fiscalYearText); ?></h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="card-box">
          <div class="table-responsive" style="overflow-x:auto;">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

              <div>
                <button type="button" class="btn btn-warning btn-sm" id="allow_revision_btn">
                  <i class="fa fa-unlock"></i> Allow Revision
                </button>
              </div>

              <div class="d-flex align-items-center">
                <label for="fiscalYearFilter" class="mb-0 mr-2">Fiscal Year</label>
                <select id="fiscalYearFilter" name="fiscal_year_id" class="form-select form-control select2"
                  style="width:150px;">
                  <?php while ($y = $years->fetch_assoc()): ?>
                    <option value="<?= (int) $y['fiscal_year_id']; ?>" <?= ((int) $selectedFiscalYearId === (int) $y['fiscal_year_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($y['year']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>

            </div>

            <table id="appTable" class="table table-bordered table-hover" style="min-width: 1500px; width:100%;">
              <thead>
                <tr>
                  <th rowspan="2" style="width: 15%;">Project Title</th>
                  <th colspan="5">Procurement Project Details</th>
                  <th colspan="2">Projected Timeline (MM/YYYY)</th>
                  <th colspan="2">Funding Details</th>
                  <th rowspan="2" style="width: 12%;">Procurement Strategy or Tools</th>
                  <th rowspan="2" style="width: 18%;">Remarks<br>(Other relevant descriptions of the procurement
                    project, if applicable)</th>
                </tr>
                <tr>
                  <th style="width: 10%;">End-User or Implementing Unit</th>
                  <th style="width: 10%;">General Description of the Project</th>
                  <th style="width: 8%;">Mode of Procurement</th>
                  <th style="width: 10%;">To be covered by an Early Procurement Activity</th>
                  <th style="width: 10%;">Criteria for Bid Evaluation (including Sustainability and Domestic Preference)
                  </th>
                  <th style="width: 8%;">Start of Procurement Activity</th>
                  <th style="width: 8%;">End of Procurement Activity</th>
                  <th style="width: 8%;">Source of Fund</th>
                  <th style="width: 10%;">Estimated Budget per Item / Approved Budget for the Contract (PhP)</th>
                </tr>
                <tr class="text-center bg-light">
                  <th>Column 1</th>
                  <th>Column 2</th>
                  <th>Column 3</th>
                  <th>Column 4</th>
                  <th>Column 5</th>
                  <th>Column 6</th>
                  <th>Column 7</th>
                  <th>Column 8</th>
                  <th>Column 9</th>
                  <th>Column 10</th>
                  <th>Column 11</th>
                  <th>Column 12</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($finalizedApps && $finalizedApps->num_rows > 0): ?>
                  <?php
                  $mainAppTotal = 0;
                  $epaTotal = 0;
                  while ($app = $finalizedApps->fetch_assoc()): ?>
                    <?php

                    $procMode = strtolower(trim($app['proc_mode_name'] ?? ''));

                    if (
                      $procMode === 'direct acquisition' ||
                      $procMode === 'common use supplies and equipment'
                    ) {
                      continue;
                    }
                    $officeNames = $db->getOfficeNamesByIds($app['offices_involved']);
                    $budget = '₱' . number_format((float) $app['total_cost'], 2);
                    $mainAppTotal += (float) $app['total_cost'];
                    if (strtolower(trim($app['pre_procurement_conference'] ?? '')) === 'yes') {
                      $epaTotal += (float) ($app['total_cost'] ?? 0);
                    }
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($app['category_name']); ?></td>
                      <td><?= htmlspecialchars($officeNames); ?></td>
                      <td>
                        <div style="font-weight:600; margin-bottom:4px;">
                          <?= htmlspecialchars($app['sub_cat_name'] ?? ''); ?>
                        </div>
                        <div><?= htmlspecialchars($app['item_name'] ?? ''); ?></div>
                        <?php if (!empty($app['item_description'])): ?>
                          <div style="margin-top:4px; font-size:12px; opacity:.85;">
                            <?= nl2br(htmlspecialchars($app['item_description'])); ?>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($app['proc_mode_name'] ?? ''); ?></td>
                      <td><?= htmlspecialchars($app['pre_procurement_conference'] ?? ''); ?></td>
                      <td><?= htmlspecialchars($app['bid_cat_name'] ?? ''); ?></td>
                      <td><?= htmlspecialchars($app['procurement_start_date'] ?? ''); ?></td>
                      <td><?= htmlspecialchars($app['bidding_date'] ?? ''); ?></td>
                      <td><?= htmlspecialchars($app['source_of_funds'] ?? ''); ?></td>
                      <td class="text-end"><?= $budget; ?></td>
                      <td><?= htmlspecialchars($app['proc_strat_name'] ?? ''); ?></td>
                      <td><?= nl2br(htmlspecialchars($app['remarks'] ?? '')); ?></td>
                    </tr>
                  <?php endwhile; ?>
                  <tr class="d-none">
                    <td colspan="9" class="text-end"><strong>TOTAL MAIN APP ITEMS</strong></td>
                    <td class="text-end fw-bold">₱<?= number_format($mainAppTotal, 2); ?></td>
                    <td colspan="2"></td>
                  </tr>
                  <tr>
                    <th colspan="12" class="text-left table-secondary p-1">
                      Miscellaneous Items (For Direct Acquisition only) Sec 32.2 of RA 12009
                    </th>
                  </tr>

                  <?php if ($directAcquisitionItems && $directAcquisitionItems->num_rows > 0): ?>
                    <?php
                    $directTotal = 0;

                    while ($item = $directAcquisitionItems->fetch_assoc()):
                      $officeNames = $db->getOfficeNamesByIds($item['offices_involved']);
                      $budget = '₱' . number_format((float) $item['total_cost'], 2);
                      $directTotal += (float) $item['total_cost'];
                      if (strtolower(trim($item['pre_procurement_conference'] ?? '')) === 'yes') {
                        $epaTotal += (float) ($item['total_cost'] ?? 0);
                      }
                      ?>

                      <tr>
                        <td><?= htmlspecialchars($item['category_name']); ?></td>
                        <td><?= htmlspecialchars($officeNames); ?></td>
                        <td>
                          <div style="font-weight:600;">
                            <?= htmlspecialchars($item['sub_cat_name'] ?? ''); ?>
                          </div>
                          <div><?= htmlspecialchars($item['item_name'] ?? ''); ?></div>
                        </td>
                        <td><?= htmlspecialchars($item['proc_mode_name'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['pre_procurement_conference'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['bid_cat_name'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['procurement_start_date'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['bidding_date'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['source_of_funds'] ?? ''); ?></td>
                        <td class="text-end"><?= $budget; ?></td>
                        <td><?= htmlspecialchars($item['proc_strat_name'] ?? ''); ?></td>
                        <td><?= nl2br(htmlspecialchars($item['remarks'] ?? '')); ?></td>
                      </tr>

                    <?php endwhile; ?>

                    <tr class="d-none">
                      <td colspan="9" class="text-end"><strong>TOTAL DIRECT ACQUISITION</strong></td>
                      <td class="text-end fw-bold">₱<?= number_format($directTotal, 2); ?></td>
                      <td colspan="2"></td>
                    </tr>

                  <?php else: ?>
                    <?php renderBlankRows(); ?>
                  <?php endif; ?>

                  <tr>
                    <th colspan="12" class="text-left table-secondary p-1">
                      Common Use Supplies and Equipment (CSE) to be purchased from PS-DBM (Kindly indicate the
                      summary/total amounts only)
                    </th>
                  </tr>

                  <?php if ($cseItems && $cseItems->num_rows > 0): ?>
                    <?php
                    $cseTotal = 0;

                    while ($item = $cseItems->fetch_assoc()):
                      $officeNames = $db->getOfficeNamesByIds($item['offices_involved']);
                      $budget = '₱' . number_format((float) $item['total_cost'], 2);
                      $cseTotal += (float) $item['total_cost'];
                      if (strtolower(trim($item['pre_procurement_conference'] ?? '')) === 'yes') {
                        $epaTotal += (float) ($item['total_cost'] ?? 0);
                      }
                      ?>

                      <tr>
                        <td><?= htmlspecialchars($item['category_name']); ?></td>
                        <td><?= htmlspecialchars($officeNames); ?></td>
                        <td>
                          <div style="font-weight:600;">
                            <?= htmlspecialchars($item['sub_cat_name'] ?? ''); ?>
                          </div>
                          <div><?= htmlspecialchars($item['item_name'] ?? ''); ?></div>

                          <?php if (!empty($item['item_description'])): ?>
                            <div style="margin-top:4px; font-size:12px; opacity:.85;">
                              <?= nl2br(htmlspecialchars($item['item_description'])); ?>
                            </div>
                          <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['proc_mode_name'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['pre_procurement_conference'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['bid_cat_name'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['procurement_start_date'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['bidding_date'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($item['source_of_funds'] ?? ''); ?></td>
                        <td class="text-end"><?= $budget; ?></td>
                        <td><?= htmlspecialchars($item['proc_strat_name'] ?? ''); ?></td>
                        <td><?= nl2br(htmlspecialchars($item['remarks'] ?? '')); ?></td>
                      </tr>

                    <?php endwhile; ?>

                    <tr class="d-none">
                      <td colspan="9" class="text-end"><strong>TOTAL CSE</strong></td>
                      <td class="text-end fw-bold">₱<?= number_format($cseTotal, 2); ?></td>
                      <td colspan="2"></td>
                    </tr>

                  <?php else: ?>
                    <?php renderBlankRows(); ?>
                  <?php endif; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="12" class="text-center">No finalized APPs found for the selected fiscal year.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
            <?php
            $totalEstimatedBudget = ($mainAppTotal ?? 0) + ($directTotal ?? 0) + ($cseTotal ?? 0);
            ?>

            <div class="d-flex justify-content-end mt-3">
              <div style="min-width: 480px;">
                <table class="table table-sm table-borderless mb-0">
                  <tr>
                    <td class="text-end fw-bold">
                      Total Amount of Estimated Budget for EPA Projects:
                    </td>
                    <td class="text-end fw-bold" style="width: 160px;">
                      ₱<?= number_format($epaTotal ?? 0, 2); ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-end fw-bold">
                      Total Amount of CSEs to be purchased from PS-DBM:
                    </td>
                    <td class="text-end fw-bold">
                      ₱<?= number_format($cseTotal ?? 0, 2); ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-end fw-bold">
                      Total Amount of Estimated Budget:
                    </td>
                    <td class="text-end fw-bold">
                      ₱<?= number_format($totalEstimatedBudget, 2); ?>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  $(document).ready(function () {
    $('#fiscalYearFilter').on('change', function () {
      window.location.href = 'app.php?fiscal_year_id=' + $(this).val();
    });

    $('#allow_revision_btn').on('click', function () {

      $.ajax({
        url: '../php/processes.php',
        type: 'POST',
        data: { action: 'GetSectorsWithFinalizedPPMP' },
        dataType: 'json',
        success: function (res) {

          if (!res.success) {
            Swal.fire("Error", res.message || "Unable to load offices.", "error");
            return;
          }

          if (!res.data.length) {
            Swal.fire(
              "No Available Office",
              "All Sectors with finalized PPMP are already allowed for revision.",
              "info"
            );
            return;
          }

          let options = `
  <option value="__all__">Select All</option>
`;

          options += res.data.map(s =>
            `<option value="${s.office_id}">${s.office_name}</option>`
          ).join('');

          Swal.fire({
            title: 'Allow Revision',
            html: `
    <select id="sector_select" class="form-control" multiple style="width:100%;">
      ${options}
    </select>
  `,
            showCancelButton: true,
            confirmButtonText: 'Allow Revision',
            customClass: {
              popup: 'allow-revision-popup'
            },
            didOpen: () => {
              $('#sector_select').select2({
                dropdownParent: $('.swal2-container'),
                width: '100%',
                placeholder: 'Select office/s',
                closeOnSelect: false
              });

              $('#sector_select').on('select2:select', function (e) {
                if (e.params.data.id === '__all__') {
                  const allValues = res.data.map(s => String(s.office_id));
                  $('#sector_select').val(allValues).trigger('change');
                }
              });
            },
            preConfirm: () => {
              const officeIds = $('#sector_select').val() || [];

              const filteredIds = officeIds.filter(id => id !== '__all__');

              if (!filteredIds.length) {
                Swal.showValidationMessage('Please select at least one office.');
                return false;
              }

              return filteredIds;
            }
          }).then(result => {

            if (!result.isConfirmed) return;

            $.ajax({
              url: '../php/processes.php',
              type: 'POST',
              data: {
                action: 'AllowPPMPRevision',
                office_ids: result.value
              },
              dataType: 'json',
              success: function (res) {
                if (res.success) {
                  Swal.fire("Success", res.message, "success").then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire("Error", res.message, "error");
                }
              }
            });

          });

        }
      });

    });

  });
</script>