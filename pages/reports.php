<?php
require_once 'sidebar.php';

function formatAccountingPHP($amount)
{
  $n = (float) $amount;
  $abs = number_format(abs($n), 2);
  return $n < 0 ? "(₱{$abs})" : "₱{$abs}";
}

$currentFY = $db->getCurrentFiscalYear();
$currentFiscalYearId = $currentFY['fiscal_year_id'] ?? 0;
$currentFiscalYearText = $currentFY['year'] ?? '';

$annual_budgets = $db->getAnnualBudgets();
$allocations = $db->getAllBudgetAllocations();
$offices = $db->getAllOffices();
$fysBudgetAllocation = $db->getFiscalYears();
$filter_office = $db->getAllOffices();
$yearsApp = $db->getFiscalYears();
$yearsAppVersion = $db->getFiscalYears();
$ppmpRecords = $db->getAllPPMPRecordsForReports();
$appVersions = $db->getAppVersionsByFiscalYear($currentFiscalYearId);
$totalVersions = $appVersions ? $appVersions->num_rows : 0;
?>
<style>
  .money {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
  }
</style>

<div class="right_col" role="main">
  <div class="page-title">
    <div class="title_left">
      <h3>Reports</h3>
    </div>
  </div>
  <div class="clearfix"></div>

  <div class="x_panel">
    <div class="x_title">
      <h2>Manage Reports</h2>
      <div class="clearfix"></div>
    </div>

    <div class="x_content">
      <ul id="myTab" class="nav nav-tabs bar_tabs py-0">
        <li class="active"><a href="#annual-budget-tab" data-toggle="tab">Annual Budget</a></li>
        <li><a href="#budget-allocation-tab" data-toggle="tab">Budget Allocation</a></li>
        <?php if ($canApprovePPMP && $canViewReports && $canManageBudget): ?>
          <li><a href="#ppmp-tab" data-toggle="tab">PPMP</a></li>
          <li><a href="#app-tab" data-toggle="tab">APP</a></li>
          <li><a href="#app-version-tab" data-toggle="tab">APP Version</a></li>
        <?php endif; ?>
      </ul>

      <div class="tab-content">

        <div class="tab-pane fade active show" id="annual-budget-tab">
          <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
              <h5 class="mb-1 font-weight-bold ml-3">Annual Budget Overview</h5>
            </div>
            <form method="POST" action="../php/export_records.php" target="_blank" class="mb-0 mr-2">
              <button type="submit" class="btn btn-primary btn-sm" name="export_annual_budget">
                <i class="fa fa-download mr-1"></i>Export Annual Budget
              </button>
            </form>
          </div>

          <table id="annualBudgetTable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-light">
              <tr>
                <th>FISCAL YEAR</th>
                <th class="text-right">ANNUAL BUDGET</th>
                <th class="text-right">REMAINING BALANCE</th>
                <th>CREATED BY</th>
                <th>LAST UPDATED BY</th>
                <th>DATE CREATED</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $annual_budgets->fetch_assoc()): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($row['fiscal_year']); ?></strong></td>
                  <td class="money font-weight-bold text-success"><?= formatAccountingPHP($row['total_budget_amount']); ?>
                  </td>
                  <td class="money"><?= formatAccountingPHP($row['remaining_budget_amount']); ?></td>
                  <td><?= htmlspecialchars($row['submitted_by_name'] ?: 'N/A'); ?></td>
                  <td><?= htmlspecialchars($row['updated_by_name'] ?: 'N/A'); ?></td>
                  <td><?= !empty($row['date_added']) ? date('M d, Y', strtotime($row['date_added'])) : '-'; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="tab-pane fade" id="budget-allocation-tab">
          <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="ml-3">
              <h5 class="mb-1 font-weight-bold">Budget Allocation</h5>
            </div>
            <form method="POST" action="../php/export_records.php" target="_blank" class="w-100">
              <div class="d-flex justify-content-between align-items-end flex-wrap mb-3" style="gap:10px;">

                <div class="d-flex align-items-end flex-wrap ml-3" style="gap:10px;">
                  <div>
                    <label class="mb-1 font-weight-bold d-block">Fiscal Year</label>
                    <select id="budget_allocation_filter_fy" name="fiscal_year_id" class="form-control select2"
                      style="width:160px;">
                      <?php while ($fy = $fysBudgetAllocation->fetch_assoc()): ?>
                        <option value="<?= $fy['fiscal_year_id'] ?>">
                          <?= $fy['year'] ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1 font-weight-bold d-block">Office</label>
                    <select name="office_id" id="budget_allocation_filter_office" class="form-control select2"
                      style="width:300px;">
                      <option value="" selected>All Offices</option>
                      <?php while ($o = $offices->fetch_assoc()): ?>
                        <option value="<?= $o['office_id'] ?>">
                          <?= $o['office_name'] ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                    <button type="button" id="clearBudgetFilters" class="btn btn-primary btn-sm">
                      <i class="fa fa-times-circle"></i> Clear
                    </button>
                  </div>
                </div>

                <div class="mr-2">
                  <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                  <button type="submit" class="btn btn-primary btn-sm" name="export_budget_allocation">
                    <i class="fa fa-download"></i> Export Allocation
                  </button>
                </div>

              </div>
            </form>
          </div>

          <table id="budgetAllocationTable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-light">
              <tr>
                <th>OFFICE</th>
                <th>HEAD</th>
                <th class="text-right">ALLOTMENT</th>
                <th class="text-right">ALLOCATED</th>
                <th class="text-right">BALANCE</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = $allocations->fetch_assoc()):
                $spent = $r['allocated_amount'] - $r['remaining_amount']; ?>
                <tr>
                  <td><?= htmlspecialchars($r['office_name']) ?></td>
                  <td><?= htmlspecialchars($r['head_name']) ?></td>
                  <td class="money"><?= formatAccountingPHP($r['allocated_amount']) ?></td>
                  <td class="money"><?= formatAccountingPHP($spent) ?></td>
                  <td class="money"><?= formatAccountingPHP($r['remaining_amount']) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="tab-pane fade" id="ppmp-tab">
          <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div class="ml-3">
              <h5 class="mb-1 font-weight-bold">PPMP</h5>
            </div>
            <form method="POST" action="../php/export_records.php" target="_blank" class="w-100">
              <div class="d-flex justify-content-between align-items-end flex-wrap" style="gap:10px;">

                <div class="d-flex align-items-end flex-wrap ml-3" style="gap:10px;">
                  <div>
                    <label for="filter_office" class="mb-1 font-weight-bold d-block">
                      Office
                    </label>
                    <select id="filter_office" name="office_id" class="form-control select2" style="width:350px;">
                      <option value="" selected>All Offices</option>
                      <?php while ($y = $filter_office->fetch_assoc()) { ?>
                        <option value="<?= $y['office_id']; ?>">
                          <?= $y['office_name']; ?>
                        </option>
                      <?php } ?>
                    </select>
                  </div>

                  <div>
                    <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                    <button type="button" id="clearPPMPFilters" class="btn btn-primary btn-sm">
                      <i class="fa fa-times-circle"></i> Clear
                    </button>
                  </div>
                </div>

                <div class="mr-2">
                  <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                  <button type="submit" class="btn btn-primary btn-sm" title="Export PPMP" name="export_ppmp">
                    <i class="fa fa-download mr-1"></i> Export PPMP
                  </button>
                </div>

              </div>
            </form>
          </div>

          <table id="ppmpTable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>OFFICE NAME</th>
                <th>OFFICE HEAD</th>
                <th class="text-center">FISCAL YEAR</th>
                <th class="text-right">TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($ppmpRecords && $ppmpRecords->num_rows > 0):
                while ($row = $ppmpRecords->fetch_assoc()):
                  $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
                  $totalAmount = formatAccountingPHP($row['total_amount']);
                  $submissionDate = !empty($row['submitted_at'])
                    ? date('M d, Y', strtotime($row['submitted_at']))
                    : date('M d, Y', strtotime($row['created_at']));
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($row['office_name']); ?></td>
                    <td><?= htmlspecialchars($fullName); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                    <td class="text-right fw-bold money"><?= $totalAmount; ?></td>
                    <td><?= $submissionDate; ?></td>
                  </tr>
                <?php endwhile; else: ?>
                <tr>
                  <td colspan="5" class="text-center">No PPMP records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="tab-pane fade" id="app-tab">
          <div class="table-responsive" style="overflow-x:auto;">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div class="ml-3">
                <h5 class="mb-1 font-weight-bold">Finalized APP</h5>
              </div>
              <form method="POST" action="../php/export_records.php" target="_blank" class="w-100">
                <div class="d-flex justify-content-between align-items-end flex-wrap mb-3" style="gap:10px;">

                  <div class="d-flex align-items-end flex-wrap ml-3" style="gap:10px;">
                    <div>
                      <label for="fiscalYearFilter" class="mb-1 font-weight-bold d-block">Fiscal Year</label>
                      <select id="fiscalYearFilter" name="fiscal_year_id" class="form-select form-control select2"
                        style="width:150px;">
                        <?php while ($y = $yearsApp->fetch_assoc()): ?>
                          <option value="<?= (int) ($y['fiscal_year_id'] ?? 0); ?>" <?= ((int) ($y['fiscal_year_id'] ?? 0) === (int) $currentFiscalYearId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($y['year'] ?? ''); ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>

                    <div class="mr-3">
                      <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                      <button type="button" id="clearAppFilters" class="btn btn-primary btn-sm">
                        <i class="fa fa-times-circle"></i> Clear
                      </button>
                    </div>
                  </div>

                  <div>
                    <div class="d-flex" style="gap:8px;">

                      <button type="submit" class="btn btn-danger btn-sm" name="export_app_pdf" id="export_app_pdf_btn">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                      </button>

                      <button type="submit" class="btn btn-success btn-sm" name="export_app_excel"
                        id="export_app_excel_btn">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                      </button>

                    </div>
                  </div>

                </div>
              </form>
            </div>

            <table id="appTable" class="table table-bordered table-hover" style="min-width: 1500px; width:100%;">
              <thead>
                <tr>
                  <th rowspan="2" class="header-cell" style="width: 15%;">Project Title</th>
                  <th colspan="5" class="header-cell">PROCUREMENT PROJECT DETAILS</th>
                  <th colspan="2" class="header-cell">PROJECTED TIMELINE (MM/YYYY)</th>
                  <th colspan="2" class="header-cell">FUNDING DETAILS</th>
                  <th rowspan="2" class="header-cell" style="width: 12%;">PROCUREMENT STRATEGY OR TOOLS</th>
                  <th rowspan="2" class="header-cell" style="width: 18%;">REMARKS<br>(Other relevant descriptions of the
                    procurement project, if applicable)</th>
                </tr>
                <tr>
                  <th style="width: 10%;">End-User or Implementing Unit</th>
                  <th style="width: 10%;">General Description of the Project</th>
                  <th style="width: 8%;">Mode of Procurement</th>
                  <th style="width: 10%;">To be covered by an Early Procurement Activity</th>
                  <th style="width: 10%;">Criteria for Bid Evaluation</th>
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
              <tbody></tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="app-version-tab">
          <div class="table-responsive">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
              <div class="ml-3">
                <h5 class="mb-1 font-weight-bold">APP Version</h5>
              </div>
              <form method="POST" action="../php/export_records.php" target="_blank" class="w-100">
                <div class="d-flex justify-content-between align-items-end flex-wrap" style="gap:10px;">

                  <div class="d-flex align-items-end flex-wrap ml-3" style="gap:10px;">
                    <div>
                      <label for="appVersionFiscalYear" class="mb-1 font-weight-bold d-block">Fiscal Year</label>
                      <select name="fiscal_year_id" id="appVersionFiscalYear" class="form-control select2"
                        style="width:180px;">
                        <?php while ($fy = $yearsAppVersion->fetch_assoc()): ?>
                          <option value="<?= (int) $fy['fiscal_year_id']; ?>" <?= ((int) $fy['fiscal_year_id'] === (int) $currentFiscalYearId) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($fy['year']); ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>

                    <div>
                      <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                      <button type="button" id="clearAppVersionFilters" class="btn btn-primary btn-sm">
                        <i class="fa fa-times-circle"></i> Clear
                      </button>
                    </div>
                  </div>

                  <div class="mr-2">
                    <label class="mb-1 font-weight-bold d-block">&nbsp;</label>
                    <button type="submit" name="export_app_versions" class="btn btn-primary btn-sm">
                      <i class="fa fa-download mr-1"></i> Export APP History
                    </button>
                  </div>

                </div>
              </form>
            </div>

            <table class="table table-bordered table-hover" id="appVersionsTable">
              <thead class="bg-light">
                <tr>
                  <th>FISCAL YEAR</th>
                  <th>VERSION</th>
                  <th>REFERENCE CODE</th>
                  <th>STATUS</th>
                  <th class="text-center">TOTAL ITEMS</th>
                  <th class="text-right">TOTAL BUDGET</th>
                  <th>NOTES</th>
                  <th>CREATED</th>
                  <th>FINALIZED</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  $(function () {

    const escapeHtml = s => s ? String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", "&#039;") : '';

    const money = n => "₱" + Number(n || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const safeDestroy = id => {
      if ($.fn.DataTable.isDataTable(id)) {
        $(id).DataTable().destroy();
      }
    };

    const initTable = id => {
      if (!$.fn.DataTable.isDataTable(id)) {
        return $(id).DataTable({
          responsive: false,
          autoWidth: false,
          pageLength: 10
        });
      }
      return $(id).DataTable();
    };

    const annualBudgetTable = initTable('#annualBudgetTable');
    const budgetAllocationTable = initTable('#budgetAllocationTable');
    const ppmpTable = initTable('#ppmpTable');

    const appTable = null;

    $('#appVersionsTable').DataTable({
      responsive: false,
      autoWidth: false,
      pageLength: 10
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    function buildDesc(a) {
      return `
        <div style="font-weight:600; margin-bottom:4px;">${escapeHtml(a.sub_cat_name)}</div>
        <div>${escapeHtml(a.item_name)}</div>
        ${a.item_description ? `<div style="margin-top:4px; font-size:12px; opacity:.85;">${escapeHtml(a.item_description)}</div>` : ''}
      `;
    }

    function buildOffices(o) {
      return (o || '').split(',').map(x => x.trim()).filter(Boolean).map(x => escapeHtml(x)).join(', ');
    }

    function appendStaticRows(table) {
      table.row.add([
        'Miscellaneous Items (For Direct Acquisition only) Sec 32.2 of RA 12009',
        '', '', '', '', '', '', '', '', '', '', ''
      ]).node().classList.add('app-section-row');

      for (let i = 0; i < 5; i++) {
        table.row.add(['', '', '', '', '', '', '', '', '', '', '', '']);
      }

      table.row.add([
        'Common Use Supplies and Equipment (CSE) to be purchased from PS-DBM (Kindly indicate the summary/total amounts only)',
        '', '', '', '', '', '', '', '', '', '', ''
      ]).node().classList.add('app-section-row');

      for (let i = 0; i < 5; i++) {
        table.row.add(['', '', '', '', '', '', '', '', '', '', '', '']);
      }
    }

    function appRow(a) {
      return `
    <tr>
      <td>${escapeHtml(a.category_name)}</td>
      <td>${buildOffices(a.office_names)}</td>
      <td>${buildDesc(a)}</td>
      <td>${escapeHtml(a.proc_mode_name)}</td>
      <td>${escapeHtml(a.pre_procurement_conference)}</td>
      <td>${escapeHtml(a.bid_cat_name)}</td>
      <td>${escapeHtml(a.procurement_start_date)}</td>
      <td>${escapeHtml(a.bidding_date)}</td>
      <td>${escapeHtml(a.source_of_funds)}</td>
      <td class="money">${money(a.total_cost)}</td>
      <td>${escapeHtml(a.proc_strat_name)}</td>
      <td>${escapeHtml(a.remarks)}</td>
    </tr>
  `;
    }

    function totalRow(label, total) {
      return `
    <tr class="font-weight-bold">
      <td colspan="9" class="text-right">${label}</td>
      <td class="money">${money(total)}</td>
      <td colspan="2"></td>
    </tr>
  `;
    }

    function sectionRow(label) {
      return `
    <tr>
      <th colspan="12" class="text-left table-secondary font-weight-bold p-1">
        ${label}
      </th>
    </tr>
  `;
    }

    function loadApps(fyId) {
      $.post("../php/processes.php", {
        action: "GetFinalizedAppsByYear",
        fiscal_year_id: fyId
      }, function (res) {

        const tbody = $('#appTable tbody');
        tbody.empty();

        if (!res.data || !res.data.length) {
          $('#export_app_btn').prop('disabled', true);
          tbody.html(`
        <tr>
          <td colspan="12" class="text-center text-muted">
            No finalized APPs found for the selected fiscal year.
          </td>
        </tr>
      `);
          return;
        }

        $('#export_app_btn').prop('disabled', false);

        let mainTotal = 0;
        let directTotal = 0;
        let cseTotal = 0;

        const main = [];
        const direct = [];
        const cse = [];

        res.data.forEach(a => {
          const mode = (a.proc_mode_name || '').toLowerCase().trim();

          if (mode === 'direct acquisition') {
            direct.push(a);
          } else if (mode === 'common use supplies and equipment') {
            cse.push(a);
          } else {
            main.push(a);
          }
        });

        main.forEach(a => {
          mainTotal += parseFloat(a.total_cost || 0);
          tbody.append(appRow(a));
        });

        // tbody.append(totalRow('TOTAL MAIN APP ITEMS', mainTotal));

        tbody.append(sectionRow('Miscellaneous Items (For Direct Acquisition only) Sec 32.2 of RA 12009'));

        direct.forEach(a => {
          directTotal += parseFloat(a.total_cost || 0);
          tbody.append(appRow(a));
        });

        // tbody.append(totalRow('TOTAL DIRECT ACQUISITION', directTotal));

        tbody.append(sectionRow('Common Use Supplies and Equipment (CSE) to be purchased from PS-DBM (Kindly indicate the summary/total amounts only)'));

        cse.forEach(a => {
          cseTotal += parseFloat(a.total_cost || 0);
          tbody.append(appRow(a));
        });

        // tbody.append(totalRow('TOTAL CSE', cseTotal));

      }, 'json');
    }
    function loadAppVersions(fiscalYearId) {
      const fiscalYearText = $('#appVersionFiscalYear option:selected').text().trim();
      const table = $('#appVersionsTable').DataTable();

      $.post('../php/processes.php', {
        action: 'GetAppVersionsByYear',
        fiscal_year_id: fiscalYearId
      }, function (res) {
        table.clear();

        if (!res.data || !res.data.length) {
          table.clear().draw();

          $('#appVersionsTable tbody').html(`
            <tr>
              <td colspan="9" class="text-center text-muted">
                No APP version history found for this fiscal year.
              </td>
            </tr>
          `);
          return;
        }

        const latest = res.data[0];
        const totalVersions = res.data.length;
        const previousCount = Math.max(0, totalVersions - 1);
        const versionNo = parseInt(latest.version_no || 0, 10);
        const referenceCode = `APP-${fiscalYearText}-V${String(versionNo).padStart(2, '0')}`;

        let badgeClass = 'badge-secondary';
        if (latest.status === 'Finalized') badgeClass = 'badge-success';
        if (latest.status === 'Draft') badgeClass = 'badge-warning text-dark';
        if (latest.status === 'Superseded') badgeClass = 'badge-dark';

        table.row.add([
          fiscalYearText,
          `<strong>Version ${versionNo}</strong><div class="text-muted small">${previousCount} previous version${previousCount === 1 ? '' : 's'}</div>`,
          referenceCode,
          `<span class="badge ${badgeClass}">${escapeHtml(latest.status || '-')}</span>`,
          Number(latest.total_items || 0).toLocaleString(),
          money(latest.total_budget),
          latest.notes ? escapeHtml(latest.notes) : '<span class="text-muted">No notes</span>',
          latest.created_at || '-',
          latest.finalized_at || '-'
        ]).draw();
      }, 'json');
    }

    $('#budget_allocation_filter_office').on('change', function () {
      const val = $(this).val();
      const text = $(this).find('option:selected').text().trim();
      budgetAllocationTable.column(0).search(val ? text : '').draw();
    });

    $('#budget_allocation_filter_fy').on('change', function () {
      budgetAllocationTable.draw();
    });

    $('#filter_office').on('change', function () {
      const val = $(this).val();
      const text = $(this).find('option:selected').text().trim();
      ppmpTable.column(0).search(val ? text : '').draw();
    });

    $('#fiscalYearFilter').on('change', function () {
      loadApps(this.value);
    });

    $('#appVersionFiscalYear').on('change', function () {
      loadAppVersions(this.value);
    });

    $('#clearBudgetFilters').on('click', function () {
      $('#budget_allocation_filter_fy').prop('selectedIndex', 0).trigger('change');
      $('#budget_allocation_filter_office').val('').trigger('change');
      budgetAllocationTable.search('').columns().search('').draw();
    });

    $('#clearPPMPFilters').on('click', function () {
      $('#filter_office').val('').trigger('change');
      ppmpTable.search('').columns().search('').draw();
    });

    $('#clearAppFilters').on('click', function () {
      $('#fiscalYearFilter').prop('selectedIndex', 0).trigger('change');
    });

    $('#clearAppVersionFilters').on('click', function () {
      $('#appVersionFiscalYear').prop('selectedIndex', 0).trigger('change');
    });

    loadApps($('#fiscalYearFilter').val());
    loadAppVersions($('#appVersionFiscalYear').val());

  });
</script>