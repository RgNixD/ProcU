<?php
require_once 'sidebar.php';
function format_accounting($amount, $currency = '₱')
{
  if ($amount === null || $amount === '')
    return '';
  $num = (float) $amount;

  $formatted = number_format(abs($num), 2);

  if ($num < 0) {
    return "({$currency}{$formatted})";
  }
  return "{$currency}{$formatted}";
}
$fiscalYears = $db->getFiscalYears();
$fiscalYears2 = $db->getFiscalYears();
$currentFiscalYear = $db->getCurrentFiscalYear();
$currentFiscalYearId = $currentFiscalYear['fiscal_year_id'] ?? '';

?>
<style>
  .custom-header {
    background-color: #a83232;
    color: white;
    padding: 13px 1.25rem;
    font-weight: bold;
    font-size: 1.08rem;
    text-transform: uppercase;
  }

  .custom-card {
    background-color: #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 2rem;
    overflow: hidden;
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.20em 0.8em;
    border-radius: 50rem;
    font-size: 0.9em;
    font-weight: 600;
  }

  .completed-badge {
    color: #155724;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
  }

  .in-progress-badge {
    color: #856404;
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
  }

  .under-review-badge {
    color: #004085;
    background-color: #cce5ff;
    border: 1px solid #b8daff;
  }

  .input-like {
    background-color: #f7f7f7;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
    display: block;
    min-width: 50px;
  }

  .dashboard-section {
    margin-bottom: 24px;
  }

  .metric-card h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 6px;
  }

  .metric-card small {
    color: #6c757d;
  }

  .table td {
    height: 100px;
    vertical-align: top;
  }

  .chart-empty-state {
    min-height: 306px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    text-align: center;
  }

  .ppmp-chart-layout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    min-height: 310px;
  }

  .ppmp-chart-legend {
    width: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding-left: 10px;
  }

  .ppmp-chart-box {
    width: min(100%, 320px);
    height: 320px;
    position: relative;
  }

  @media (max-width: 768px) {
    .ppmp-chart-layout {
      flex-direction: column;
      gap: 14px;
    }

    .ppmp-chart-legend {
      width: 100%;
      flex-direction: row;
      flex-wrap: wrap;
      justify-content: center;
      padding-left: 0;
    }

    .ppmp-chart-box {
      width: min(100%, 280px);
      height: 280px;
    }
  }
</style>
<div class="right_col" role="main">
  <div class="page-title">
    <div class="title_left">
      <h3>Overview</h3>
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="x_content metric-card">

    <?php if ($canApprovePPMP && $canViewReports): ?>

      <div class="row">
        <div class="col-md-6">
          <div class="x_panel">
            <div class="x_title">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="mb-0">PPMP SUBMISSION OVERVIEW <strong><?= 'FY-' . $currentFiscalYear['year'] ?? '' ?></strong>
                </h2>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <canvas id="ppmpRecordsChart"></canvas>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="x_panel">
            <div class="x_title">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="mb-0">FINALIZED APP PREVIEW <strong><?= 'FY-' . $currentFiscalYear['year'] ?? '' ?></strong>
                </h2>
              </div>
              <div class="clearfix"></div>
            </div>

            <div class="x_content">
              <canvas id="consolidatedItemsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php
    if ($canCreatePPMP):
      $overview = $db->getOfficeBudgetOverview($userId);
      ?>
      <div class="row">
        <div class="col-md-6">
          <div class="x_panel">
            <div class="x_title">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="mb-0">PPMP Items Submitted</h2>
                <select id="sectorFiscalYearFilter" class="form-control" style="width: 180px;">
                  <?php if ($fiscalYears && $fiscalYears->num_rows > 0): ?>
                    <?php while ($fy = $fiscalYears->fetch_assoc()): ?>
                      <option value="<?= $fy['fiscal_year_id']; ?>" <?= ($fy['fiscal_year_id'] == $currentFiscalYearId) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($fy['year']); ?>
                      </option>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div id="ppmpItemsChartContainer">
                <canvas id="ppmpItemsChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="x_panel">
            <div class="x_title">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="mb-0">BUDGET OVERVIEW</h2>
                <select id="sectorBudgetFiscalYearFilter" class="form-control" style="width: 180px;">
                  <?php if ($fiscalYears2 && $fiscalYears2->num_rows > 0): ?>
                    <?php while ($fy = $fiscalYears2->fetch_assoc()): ?>
                      <option value="<?= $fy['fiscal_year_id']; ?>" <?= ($fy['fiscal_year_id'] == $currentFiscalYearId) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($fy['year']); ?>
                      </option>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="clearfix"></div>
            </div>

            <div class="x_content">
              <div id="budgetChartContainer">
                <canvas id="budgetChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>


    <?php if ($canManageBudget): ?>
      <div class="row">
        <div class="col-md-4">
          <div class="x_panel">
            <div class="x_title">
              <h2 class="mb-0">TOTAL ANNUAL BUDGET</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <h3 class="mb-0 font-weight-bold" id="annualBudgetTotalCard">₱0.00</h3>
              <small class="text-muted">Active fiscal year budget overview</small>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="x_panel">
            <div class="x_title">
              <h2 class="mb-0">TOTAL ALLOCATED</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <h3 class="mb-0 font-weight-bold" id="allocatedBudgetTotalCard">₱0.00</h3>
              <small class="text-muted">Budget already allocated to offices</small>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="x_panel">
            <div class="x_title">
              <h2 class="mb-0">UNALLOCATED</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <h3 class="mb-0 font-weight-bold" id="unallocatedBudgetTotalCard">₱0.00</h3>
              <small class="text-muted">Available budget not yet allocated</small>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="x_panel">
            <div class="x_title">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="mb-0">ANNUAL BUDGET SUMMARY</h2>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div style="height: 420px;">
                <canvas id="annualBudgetChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <br><br><br><br><br><br><br><br><br><br>
  </div>
</div>

<?php require_once 'footer.php'; ?>
<script>

  $(function () {

    function formatPesoAccounting(value) {
      const n = Number(value) || 0;
      const abs = Math.abs(n);

      const s = "₱" + abs.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      return n < 0 ? `(${s})` : s;
    }

    function formatNumberAccounting(value) {
      const n = Number(value) || 0;
      const abs = Math.abs(n);
      const s = abs.toLocaleString('en-US');
      return n < 0 ? `(${s})` : s;
    }


    // SECTORS ANALYTICS
    function loadPPMPItemsChart(fiscalYearId = null) {
      const parent = document.getElementById("ppmpItemsChartContainer");

      if (!parent) {
        console.warn("Container #ppmpItemsChartContainer not found in DOM!");
        return;
      }

      $.ajax({
        url: "../php/analytics.php",
        method: "GET",
        data: {
          action: "GetUserPPMPItems",
          user_id: <?= $userId ?>,
          fiscal_year_id: fiscalYearId
        },
        dataType: "json",
        success: function (response) {

          if (window.ppmpItemsChart instanceof Chart) {
            window.ppmpItemsChart.destroy();
            window.ppmpItemsChart = null;
          }

          if (!response.success || !response.data || response.data.length === 0) {
            parent.innerHTML = `
              <div class="chart-empty-state">
                <div>
                <i class="fa fa-bar-chart mb-2" style="font-size:40px; opacity:.35;"></i>
                  <div>No PPMP items available.</div>
                </div>
              </div>
            `;
            return;
          }

          parent.innerHTML = '<canvas id="ppmpItemsChart"></canvas>';
          const ctx = document.getElementById("ppmpItemsChart").getContext("2d");

          const labels = response.data.map(item => item.item_name);
          const quantities = response.data.map(item => Number(item.total_quantity) || 0);
          const costs = response.data.map(item => Number(item.total_cost) || 0);

          window.ppmpItemsChart = new Chart(ctx, {
            type: "bar",
            data: {
              labels: labels,
              datasets: [
                {
                  label: "Total Cost (₱)",
                  data: costs,
                  backgroundColor: "rgba(229, 9, 20, 0.82)",
                  borderColor: "rgba(229, 9, 20, 1)",
                  borderWidth: 2,
                  borderRadius: 6,
                  hoverBackgroundColor: "rgba(255, 35, 45, 0.95)"
                },
                {
                  label: "Quantity",
                  data: quantities,
                  backgroundColor: "rgba(255, 190, 11, 0.82)",
                  borderColor: "rgba(255, 190, 11, 1)",
                  borderWidth: 2,
                  borderRadius: 6,
                  hoverBackgroundColor: "rgba(255, 210, 40, 0.95)"
                }
              ]
            },
            options: {
              responsive: true,
              title: {
                display: true,
                text: "PPMP Items Submitted"
              },
              tooltips: {
                callbacks: {
                  label: function (tooltipItem, data) {
                    const ds = data.datasets[tooltipItem.datasetIndex];
                    const label = ds.label || '';
                    const val = tooltipItem.yLabel;

                    if (label.toLowerCase().includes("cost")) {
                      return label + ": " + formatPesoAccounting(val);
                    }
                    return label + ": " + formatNumberAccounting(val);
                  }
                }
              },
              scales: {
                yAxes: [{
                  ticks: {
                    beginAtZero: true,
                    callback: function (value) {
                      return value.toLocaleString('en-US');
                    }
                  }
                }]
              }
            }
          });
        },
        error: function (xhr, status, error) {
          console.error("PPMP Items AJAX Error:", error);
          parent.innerHTML = `
            <div class="chart-empty-state">
              <div>
                <i class="fa fa-chart-bar mb-2" style="font-size:40px; opacity:.35;"></i>
                <div>Failed to load PPMP items.</div>
              </div>
            </div>
          `;
        }
      });
    }

    function renderBudgetChart(fiscalYearId = null) {
      const parent = document.getElementById("budgetChartContainer");

      if (!parent) {
        console.warn("Container #budgetChartContainer not found in DOM!");
        return;
      }

      $.ajax({
        url: '../php/analytics.php',
        method: 'GET',
        data: {
          action: 'GetBudgetOverview',
          user_id: <?= $userId ?>,
          fiscal_year_id: fiscalYearId
        },
        dataType: 'json',
        success: function (response) {

          if (window.budgetChart instanceof Chart) {
            window.budgetChart.destroy();
            window.budgetChart = null;
          }

          if (!response.success || !response.data) {
            parent.innerHTML = `
              <div class="chart-empty-state">
                <div>
                  <i class="fa fa-chart-bar mb-2" style="font-size:40px; opacity:.35;"></i>
                  <div>No budget data available.</div>
                </div>
              </div>
            `;
            return;
          }

          parent.innerHTML = '<canvas id="budgetChart"></canvas>';
          const ctx = document.getElementById("budgetChart").getContext("2d");

          const data = response.data;

          window.budgetChart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: [data.fiscal_year],
              datasets: [
                {
                  label: 'Allocated Budget (₱)',
                  data: [Number(data.allocated_amount) || 0],
                  backgroundColor: 'rgba(229, 9, 20, 0.82)',
                  borderColor: 'rgba(229, 9, 20, 1)',
                  borderWidth: 2,
                  borderRadius: 6
                },
                {
                  label: 'PPMP Total Amount (₱)',
                  data: [Number(data.ppmp_total_amount) || 0],
                  backgroundColor: 'rgba(255, 190, 11, 0.82)',
                  borderColor: 'rgba(255, 190, 11, 1)',
                  borderWidth: 2,
                  borderRadius: 6
                },
                {
                  label: 'Remaining Budget (₱)',
                  data: [Number(data.remaining_amount) || 0],
                  backgroundColor: 'rgba(68, 68, 68, 0.82)',
                  borderColor: 'rgba(68, 68, 68, 1)',
                  borderWidth: 2,
                  borderRadius: 6
                }
              ]
            },
            options: {
              responsive: true,
              title: {
                display: true,
                text: "BUDGET OVERVIEW"
              },
              tooltips: {
                callbacks: {
                  label: function (tooltipItem, data) {
                    const ds = data.datasets[tooltipItem.datasetIndex];
                    const label = ds.label || '';
                    const val = tooltipItem.yLabel;
                    return label + ": " + formatPesoAccounting(val);
                  }
                }
              },
              scales: {
                yAxes: [{
                  ticks: {
                    beginAtZero: true,
                    callback: function (value) {
                      return formatPesoAccounting(value);
                    }
                  }
                }]
              }
            }
          });

        },
        error: function (err) {
          console.error('Budget Chart AJAX Error:', err);
          parent.innerHTML = `
            <div class="chart-empty-state">
              <div>
                <i class="fa fa-chart-bar mb-2" style="font-size:40px; opacity:.35;"></i>
                <div>Failed to load budget data.</div>
              </div>
            </div>
          `;
        }
      });
    }
    // END SECTORS ANALYTICS  

    // BAC Sec Head/ BAC Secretariat Head
    function abbreviateOfficeName(name) {
      if (!name) return '';

      name = String(name).trim();

      const customMap = {
        'Office of the President': 'OP',
        'Office of the Vice President for Academic Affairs': 'OVPAA',
        'Office of the Vice President for Planning, Development and Special Concerns': 'OVPPDSC',
        'Office of the Vice President for Research and Extension': 'OVPRE'
      };

      if (customMap[name]) return customMap[name];

      return name
        .replace(/\b(Office|Department|College|Campus|Institute|Center|Unit|of|the|for|and)\b/gi, '')
        .split(/\s+/)
        .filter(Boolean)
        .map(word => word.charAt(0).toUpperCase())
        .join('');
    }

    function renderPPMPRecordsChart() {
      $.ajax({
        url: '../php/analytics.php',
        method: 'GET',
        data: {
          action: 'GetAllPPMPRecordsChart',
          fiscal_year_id: "<?= $currentFiscalYearId ?>"
        },
        dataType: 'json',
        success: function (response) {

          const canvas = document.getElementById("ppmpRecordsChart");
          const parent = canvas.parentElement;

          if (window.ppmpRecordsChart instanceof Chart) {
            window.ppmpRecordsChart.destroy();
          }

          if (!response.success || !response.data || response.data.length === 0) {
            parent.innerHTML = `
              <div class="chart-empty-state">
                <div>
                  <i class="fa fa-chart-bar mb-2" style="font-size:40px; opacity:.35;"></i>
                  <div>No PPMP records available.</div>
                </div>
              </div>
            `;
            return;
          }

          parent.innerHTML = `
  <div class="ppmp-chart-layout">

    <div id="ppmpRecordsLegend" class="ppmp-chart-legend"></div>

    <div class="ppmp-chart-box">
      <canvas id="ppmpRecordsChart"></canvas>
    </div>

  </div>
`;
          const ctx = document.getElementById("ppmpRecordsChart").getContext("2d");

          const originalLabels = response.data.map(item => item.office_name);
          const labels = response.data.map(item => abbreviateOfficeName(item.office_name));
          const amounts = response.data.map(item => item.total_amount);

          const netflixPalette = [
            'rgba(229, 9, 20, 0.85)',
            'rgba(181, 18, 27, 0.85)',
            'rgba(34, 34, 34, 0.85)',
            'rgba(68, 68, 68, 0.85)',
            'rgba(97, 97, 97, 0.85)',
            'rgba(140, 140, 140, 0.85)',
            'rgba(255, 190, 11, 0.85)',
            'rgba(255, 87, 34, 0.85)',
            'rgba(255, 255, 255, 0.75)',
            'rgba(120, 20, 20, 0.85)'
          ];

          const backgroundColors = labels.map((_, index) => {
            return netflixPalette[index % netflixPalette.length];
          });

          const borderColors = backgroundColors.map(color =>
            color.replace('0.85', '1').replace('0.75', '1')
          );

          document.getElementById('ppmpRecordsLegend').innerHTML = labels.map((label, index) => `
  <div style="display:flex; align-items:center; margin:5px 10px 5px 0; font-size:12px;">
    <span style="
      width:16px;
      height:10px;
      display:inline-block;
      background:${backgroundColors[index]};
      border:1px solid ${borderColors[index]};
      margin-right:8px;
    "></span>
    <span>${label}</span>
  </div>
`).join('');

          window.ppmpRecordsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: labels,
              datasets: [{
                label: 'Total Amount (₱)',
                data: amounts,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2,
                hoverBorderWidth: 3,
                hoverOffset: 12
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutoutPercentage: 58,

              title: {
                display: false
              },

              legend: {
                display: false
              },

              tooltips: {
                callbacks: {
                  label: function (tooltipItem, data) {
                    const label = data.labels[tooltipItem.index] || '';
                    const value = data.datasets[0].data[tooltipItem.index] || 0;

                    return label + ': ₱' + Number(value).toLocaleString(undefined, {
                      minimumFractionDigits: 2
                    });
                  }
                }
              }
            }
          });

        },
        error: function (err) {
          console.error('PPMP Records Chart AJAX Error:', err);
        }
      });
    }
    function renderConsolidatedItemsChart() {
      $.ajax({
        url: '../php/analytics.php',
        method: 'GET',
        data: {
          action: 'GetFinalizedAPPItemsChart',
          fiscal_year_id: "<?= $currentFiscalYearId ?>"
        },
        dataType: 'json',
        success: function (response) {

          const canvas = document.getElementById("consolidatedItemsChart");
          const parent = canvas.parentElement;

          if (window.consolidatedItemsChart instanceof Chart) {
            window.consolidatedItemsChart.destroy();
          }

          if (!response.success || !response.data || response.data.length === 0) {
            parent.innerHTML = `
              <div class="chart-empty-state">
                <div>
                <i class="fa fa-bar-chart mb-2" style="font-size:40px; opacity:.35;"></i>
                  <div>No finalized APP items available.</div>
                </div>
              </div>
            `;
            return;
          }

          parent.innerHTML = '<canvas id="consolidatedItemsChart"></canvas>';
          const ctx = document.getElementById("consolidatedItemsChart").getContext("2d");

          const labels = response.data.map(item => item.item_name);
          const amounts = response.data.map(item => item.total_cost);

          const netflixPalette = [
            'rgba(229, 9, 20, 0.85)',
            'rgba(181, 18, 27, 0.85)',
            'rgba(34, 34, 34, 0.85)',
            'rgba(68, 68, 68, 0.85)',
            'rgba(140, 140, 140, 0.85)',
            'rgba(255, 190, 11, 0.85)',
            'rgba(255, 87, 34, 0.85)',
            'rgba(198, 40, 40, 0.85)',
            'rgba(97, 97, 97, 0.85)',
            'rgba(255, 255, 255, 0.75)'
          ];

          const backgroundColors = labels.map((_, index) => {
            return netflixPalette[index % netflixPalette.length];
          });

          const borderColors = backgroundColors.map(color =>
            color.replace('0.85', '1')
          );

          window.consolidatedItemsChart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'Total Finalized Cost (₱)',
                data: amounts,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1.5,
                borderRadius: 6,
                hoverBackgroundColor: borderColors
              }]
            },
            options: {
              responsive: true,
              plugins: {
                title: {
                  display: true,
                  text: 'Finalized APP Items Total Cost'
                },
                tooltip: {
                  callbacks: {
                    label: function (context) {
                      const value = context.parsed.y;
                      return `${context.label}: ₱${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    }
                  }
                },
                legend: {
                  display: false
                }
              },
              scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Amount (₱)' } }
              }
            }
          });

        },
        error: function (err) {
          console.error('Consolidated Items Chart AJAX Error:', err);
        }
      });
    }
    // END BAC Sec Head/ BAC Secretariat Head

    function renderAnnualBudgetChart() {
      $.ajax({
        url: '../php/analytics.php',
        method: 'GET',
        data: { action: 'GetAnnualBudgetChart' },
        dataType: 'json',
        success: function (response) {
          const canvas = document.getElementById("annualBudgetChart");
          if (!canvas) return;

          const parent = canvas.parentElement;

          if (window.annualBudgetChart instanceof Chart) {
            window.annualBudgetChart.destroy();
          }

          if (!response.success || !response.data || response.data.length === 0) {
            parent.innerHTML = `
              <div class="chart-empty-state">
                <div>
                  <i class="fa fa-chart-bar mb-2" style="font-size:40px; opacity:.35;"></i>
                  <div>No annual budget records found.</div>
                </div>
              </div>
            `;
            return;
          }

          const sortedData = [...response.data].sort((a, b) => Number(a.year) - Number(b.year));

          const labels = sortedData.map(item => item.year);
          const annual = sortedData.map(item => Number(item.annual) || 0);
          const allocated = sortedData.map(item => Number(item.allocated) || 0);
          const unallocated = sortedData.map(item => Number(item.unallocated) || 0);

          const totalAnnual = annual.reduce((sum, val) => sum + val, 0);
          const totalAllocated = allocated.reduce((sum, val) => sum + val, 0);
          const totalUnallocated = unallocated.reduce((sum, val) => sum + val, 0);

          const annualCard = document.getElementById("annualBudgetTotalCard");
          const allocatedCard = document.getElementById("allocatedBudgetTotalCard");
          const unallocatedCard = document.getElementById("unallocatedBudgetTotalCard");

          if (annualCard) annualCard.textContent = formatPesoAccounting(totalAnnual);
          if (allocatedCard) allocatedCard.textContent = formatPesoAccounting(totalAllocated);
          if (unallocatedCard) unallocatedCard.textContent = formatPesoAccounting(totalUnallocated);

          parent.innerHTML = '<canvas id="annualBudgetChart"></canvas>';
          const ctx = document.getElementById("annualBudgetChart").getContext("2d");

          window.annualBudgetChart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [
                {
                  label: 'Annual Budget',
                  data: annual,
                  backgroundColor: 'rgba(229, 9, 20, 0.82)',      // Netflix Red
                  borderColor: 'rgba(229, 9, 20, 1)',
                  borderWidth: 2,
                  borderRadius: 6,
                  hoverBackgroundColor: 'rgba(255, 35, 45, 0.95)'
                },
                {
                  label: 'Allocated',
                  data: allocated,
                  backgroundColor: 'rgba(255, 190, 11, 0.82)',    // Gold Accent
                  borderColor: 'rgba(255, 190, 11, 1)',
                  borderWidth: 2,
                  borderRadius: 6,
                  hoverBackgroundColor: 'rgba(255, 210, 40, 0.95)'
                },
                {
                  label: 'Unallocated',
                  data: unallocated,
                  backgroundColor: 'rgba(68, 68, 68, 0.82)',      // Dark Graphite
                  borderColor: 'rgba(68, 68, 68, 1)',
                  borderWidth: 2,
                  borderRadius: 6,
                  hoverBackgroundColor: 'rgba(95, 95, 95, 0.95)'
                }
              ]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              legend: {
                position: 'top',
                labels: {
                  fontColor: '#444',
                  boxWidth: 18,
                  padding: 20
                }
              },
              animation: {
                duration: 1400,
                easing: 'easeOutQuart'
              },

              title: {
                display: true,
                text: 'Annual Budget for Active Fiscal Year'
              },
              tooltips: {
                callbacks: {
                  label: function (tooltipItem, data) {
                    const dataset = data.datasets[tooltipItem.datasetIndex];
                    const value = tooltipItem.yLabel || 0;
                    return `${dataset.label}: ${formatPesoAccounting(value)}`;
                  }
                }
              },
              scales: {
                xAxes: [{
                  gridLines: {
                    display: false
                  }
                }],
                yAxes: [{
                  ticks: {
                    beginAtZero: true,
                    callback: function (value) {
                      return formatPesoAccounting(value);
                    }
                  },
                  scaleLabel: {
                    display: true,
                    labelString: 'Amount (₱)'
                  }
                }]
              }
            }
          });
        },
        error: function (err) {
          console.error('Annual Budget Chart AJAX Error:', err);
        }
      });
    }

    <?php if ($canCreatePPMP): ?>
      const defaultSectorFiscalYearId = $("#sectorFiscalYearFilter").val() || "<?= $currentFiscalYearId ?>";
      const defaultBudgetFiscalYearId = $("#sectorBudgetFiscalYearFilter").val() || "<?= $currentFiscalYearId ?>";

      loadPPMPItemsChart(defaultSectorFiscalYearId);
      renderBudgetChart(defaultBudgetFiscalYearId);

      $("#sectorFiscalYearFilter").on("change", function () {
        const fiscalYearId = $(this).val();
        loadPPMPItemsChart(fiscalYearId);
      });

      $("#sectorBudgetFiscalYearFilter").on("change", function () {
        const fiscalYearId = $(this).val();
        renderBudgetChart(fiscalYearId);
      });
    <?php endif; ?>

    renderAnnualBudgetChart();

    <?php if ($canApprovePPMP && $canViewReports): ?>
      renderPPMPRecordsChart();
      renderConsolidatedItemsChart();
    <?php endif; ?>
  });
</script>