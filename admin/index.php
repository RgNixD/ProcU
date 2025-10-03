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
</style>
<div class="right_col" role="main">
  <div class="page-title">
    <div class="title_left">
      <h3>Overview</h3>
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="x_content">

    <div class="container p-0 custom-card">
      <div class="custom-header mb-3">SUBMISSION OVERVIEW</div>

      <div class="table-responsive pb-3">
        <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
          <thead class="bg-white">
            <tr>
              <th>Department</th>
              <th>Submitted by</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Notes</th>
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

    <div class="container p-0 custom-card">
      <div class="custom-header mb-3">CONSOLIDATED APP PREVIEW</div>

      <div class="table-responsive pb-3">
        <table id="datatable2" class="table table-striped table-bordered table-hover" style="width:100%">
          <thead class="bg-white">
            <tr>
              <th>Department</th>
              <th>Submitted by</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Notes</th>
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

  </div>
</div>

<?php require_once 'footer.php'; ?>