<?php require_once 'sidebar.php'; ?>
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

  .table thead th {
    background-color: #d46a6a;
    color: #000;
    font-weight: bold;
  }

  .table td {
    s height: 100px;
    vertical-align: top;
  }
</style>
<div class="right_col" role="main">
  <div class="page-title">
    <div class="title_left">
      <h3>Overview</h3>
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="x_content">

    <?php if ($canApprovePPMP && $canViewReports): ?>

      <div class="container p-0 custom-card">
        <div class="custom-header mb-3">SUBMISSION OVERVIEW</div>

        <div class="table-responsive pb-3">
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th>OFFICE NAME</th>
                <th>OFFICE HEAD</th>
                <th class="text-center">FISCAL YEAR</th>
                <th class="text-end">TOTAL AMOUNT</th>
                <th class="text-center">STATUS</th>
                <th>NOTES</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $ppmpRecords = $db->getAllPPMPRecords();
              while ($row = $ppmpRecords->fetch_assoc()) {
                $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
                $totalAmount = '₱' . number_format($row['total_amount'], 2);
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td><?= htmlspecialchars($fullName); ?></td>
                  <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <td class="text-end fw-bold"><?= $totalAmount; ?></td>
                  <td class="text-center">
                    <?php if ($row['status'] == 'Pending'): ?>
                      <span class="status-badge in-progress-badge">Pending</span>
                    <?php elseif ($row['status'] == 'Approved'): ?>
                      <span class="status-badge in-progress-badge">Approved</span>
                    <?php elseif ($row['status'] == 'Rejected'): ?>
                      <span class="status-badge in-progress-badge">Rejected</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($row['status']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($row['notes']); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="container p-0 custom-card">
        <div class="custom-header mb-3">CONSOLIDATED APP PREVIEW</div>

        <div class="table-responsive pb-3">
          <table id="datatable2" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-white">
              <tr>
                <th>OFFICE NAME</th>
                <th>OFFICE HEAD</th>
                <th>DUE DATE</th>
                <th>STATUS</th>
                <th>NOTES</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="input-like">&nbsp;</span></td>
                <td><span class="input-like">8</span></td>
                <td><span class="input-like">📅</span></td>
                <td><span class="status-badge completed-badge">✅ Completed</span></td>
                <td>Add here</td>
              </tr>
              <tr>
                <td><span class="input-like">&nbsp;</span></td>
                <td><span class="input-like">8</span></td>
                <td><span class="input-like">📅</span></td>
                <td><span class="status-badge in-progress-badge">✏️ In progress</span></td>
                <td>Add here</td>
              </tr>
              <tr>
                <td><span class="input-like">&nbsp;</span></td>
                <td><span class="input-like">8</span></td>
                <td><span class="input-like">📅</span></td>
                <td><span class="status-badge under-review-badge">👀 Under review</span></td>
                <td>Add here</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($canCreatePPMP): ?>
      <div class="container mt-4">
        <div class="card shadow-sm">
          <div class="card-body p-0">
            <table class="table table-bordered mb-0">
              <thead>
                <tr>
                  <th>BUDGET OVERVIEW</th>
                  <th>SUBMISSION STATUS</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($canManageBudget): ?>
      <div class="container p-0 custom-card">
        <div class="custom-header mb-3">ANNUAL BUDGET SUMMARY</div>
        <div class="table-responsive pb-3">
          <table id="datatable3" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead class="bg-white">
              <tr>
                <th>FISCAL YEAR</th>
                <th>TOTAL ANNUAL ALLOTMENT BUDGET</th>
                <th>TOTAL ALLOCATED BUDGET</th>
                <th>TOTAL UNALLOCATED BUDGET</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $annual_budgets = $db->getAnnualBudgets();
              while ($row = $annual_budgets->fetch_assoc()):
                $allocatedData = $db->getTotalAllocatedAmountByFiscalYear($row['fiscal_year_id']);
                $totalAllocated = $allocatedData['total_allocated'] ?? 0.00;
                $totalAnnualBudget = $row['total_budget_amount']; 
                $unallocatedBudget = $totalAnnualBudget - $totalAllocated;
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <td class="text-end fw-bold">
                    ₱<?= number_format($totalAnnualBudget, 2); ?>
                  </td>
                  <td class="text-end text-danger">
                    ₱<?= number_format($totalAllocated, 2); ?>
                  </td>
                  <td class="text-end text-success fw-bold">
                    ₱<?= number_format($unallocatedBudget, 2); ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
<br><br><br><br><br>
<br><br><br><br><br>

<?php require_once 'footer.php'; ?>