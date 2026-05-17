<?php
require_once '../php/auth_check.php';
if (!($canCreatePPMP)) {
  header("Location: 404.php");
  exit();
}
require_once 'sidebar.php';
?>
<style>
  th {
    vertical-align: middle;
    font-size: 0.75rem;
    padding: 5px;
  }

  td {
    height: 40px;
    vertical-align: top;
    font-size: 0.75rem;
  }

  .main-header {
    font-size: 0.85rem;
    text-transform: uppercase;
  }
</style>

<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>PPMP</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="x_panel">
      <div class="x_title">
        <h2>PPMP Creation</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="card-box">
          <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
            <div class="card-body p-0">
              <input type="hidden" class="form-control" name="user_id" id="user_id" value="<?= $userId ?>">
              <input type="hidden" class="form-control" name="remaining_budget" id="remaining_budget"
                value="<?= $remainingBudget ?>">
              <input type="hidden" id="latest_ppmp_status" value="<?= $latestPPMPStatus ?>">

              <div class="row">
                <!-- LEFT COLUMN -->
                <div class="col-md-6 mb-3">
                  <h5 class="fw-bold">Project/Item Identification</h5>

                  <div class="form-group">
                    <label for="item_description" class="form-label fw-bold">Project Description</label>
                    <textarea class="form-control" name="item_description" id="item_description"
                      placeholder="Enter Project Description" rows="2"></textarea>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="category_id" class="form-label fw-bold">Category</label>
                        <select class="form-control form-select" name="category_id" id="category_id">
                          <option value="" disabled selected>Select Category</option>
                          <?php
                          $categories = $db->getAllCategories();
                          while ($row = $categories->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['category_id']) . '">' . htmlspecialchars($row['category_name']) . '</option>';
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="sub_category_id" class="form-label fw-bold">Subcategory</label>
                        <select class="form-control form-select" name="sub_category_id" id="sub_category_id" disabled>
                          <option value="" disabled selected>Select Subcategory</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="item_name_id" class="form-label fw-bold">Item name</label>
                        <select class="form-control form-select" name="item_name_id" id="item_name_id" disabled>
                          <option value="" disabled selected>Select Item Name</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <h5 class="fw-bold mt-3">Activity Schedule</h5>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="procurement_start_date" class="form-label fw-bold">Start of Procurement
                          Activity</label>
                        <input type="date" class="form-control" name="procurement_start_date"
                          id="procurement_start_date" min="<?= date('Y-m-d') ?>">
                        <small id="procurement_start_error" class="text-danger"></small>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="bidding_date" class="form-label fw-bold">Bidding/Negotiation Date</label>
                        <input type="date" class="form-control" name="bidding_date" id="bidding_date"
                          min="<?= date('Y-m-d') ?>">
                        <small id="bidding_date_error" class="text-danger"></small>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="contract_signing_date" class="form-label fw-bold">Contract Signing/Award
                          Date</label>
                        <input type="date" class="form-control" name="contract_signing_date" id="contract_signing_date"
                          min="<?= date('Y-m-d') ?>">
                        <small id="contract_signing_error" class="text-danger"></small>
                      </div>
                    </div>
                  </div>

                  <h5 class="fw-bold mt-3">Supporting Documentation and Final Remarks</h5>

                  <div class="form-group">
                    <label for="file_attachment" class="form-label fw-bold">Attachment (Documents)</label>
                    <input type="file" class="form-control" name="file_attachment[]" id="file_attachment"
                      accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" multiple>
                    <div id="selected_files_preview" class="mt-2"></div>
                  </div>

                  <div class="form-group">
                    <label for="remarks" class="form-label fw-bold">Remarks/Other Details</label>
                    <textarea class="form-control" name="remarks" id="remarks" rows="2"
                      placeholder="Enter any final notes or remarks"></textarea>
                  </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-md-6 mb-3">
                  <h5 class="fw-bold">Item Details</h5>

                  <div class="row">
                    <div class="col-md-7">
                      <div class="form-group">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1">
                      </div>
                    </div>

                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="specification" class="form-label fw-bold">Specification</label>
                        <textarea class="form-control" name="specification" id="specification"
                          placeholder="Enter other specification (e.g., color, material, brand requirement, size)"
                          rows="2"></textarea>
                      </div>
                    </div>
                  </div>

                  <h5 class="fw-bold mt-3">Financial and Other Details</h5>

                  <div class="row">
                    <div class="col-md-7">
                      <div class="form-group">
                        <label for="source_of_funds" class="form-label fw-bold">Source of Funds</label>
                        <select class="form-control form-select select2" name="source_of_funds" id="source_of_funds">
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
                    </div>

                    <div class="col-md-5">
                      <div class="form-group">
                        <label for="estimated_budget" class="form-label fw-bold">Estimated Budget per Item</label>
                        <input type="text" class="form-control" name="estimated_budget" id="estimated_budget"
                          placeholder="Enter amount" inputmode="decimal" onkeyup="formatCurrency(this)"
                          onblur="formatCurrency(this, true)" onfocus="this.select()">
                      </div>
                    </div>
                  </div>

                  <h5 class="fw-bold mt-3">Procurement Details</h5>

                  <div class="row">
                    <div class="col-md-7">
                      <div class="form-group">
                        <label for="mode_of_procurement" class="form-label fw-bold">Mode of Procurement</label>
                        <?php $procurementModes = $db->getProcMode(); ?>
                        <select class="form-control form-select select2" name="mode_of_procurement"
                          id="mode_of_procurement">
                          <option value="" disabled selected>Select Mode of Procurement</option>
                          <?php while ($mode = $procurementModes->fetch_assoc()): ?>
                            <option value="<?= (int) $mode['proc_mode_id'] ?>">
                              <?= htmlspecialchars($mode['proc_mode_name']) ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-5">
                      <div class="form-group">
                        <label for="pre_procurement_conference" class="form-label fw-bold">Pre-procurement
                          Conference</label>
                        <select class="form-control form-select" name="pre_procurement_conference"
                          id="pre_procurement_conference">
                          <option value="" disabled selected>Select an option</option>
                          <option value="Yes">Yes</option>
                          <option value="No">No</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-danger btn-sm me-2" id="clearBtn">
                      <i class="fa fa-times me-1"></i> Clear
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="addBtn">
                      <i class="fa fa-plus me-1"></i> Add to PPMP
                    </button>
                  </div>
                </div>
              </div>

              <h6 class="text-white p-2 mb-0 mt-4" style="background-color: #a83232;">CURRENT ITEMS</h6>

              <div class="table-responsive">
                <table class="table table-bordered table-responsive" id="ppmpTable">
                  <thead>
                    <tr>
                      <th colspan="5" class="text-center main-header bg-light align-middle">PROCUREMENT PROJECT DETAILS
                      </th>
                      <th colspan="3" class="text-center main-header bg-light align-middle">PROJECTED TIMELINE (MM/YYYY)
                      </th>
                      <th colspan="2" class="text-center main-header bg-light align-middle">FUNDING DETAILS</th>
                      <th rowspan="2" class="text-center align-middle">ATTACHED SUPPORTING DOCUMENTS</th>
                      <th rowspan="2" class="text-center align-middle">REMARKS</th>
                      <th rowspan="2" class="text-center align-middle">ACTION</th>
                    </tr>
                    <tr>
                      <th class="text-center align-middle">General Description and Objective of the Project to be
                        Procured</th>
                      <th class="text-center align-middle">Type of the Project to be Procured</th>
                      <th class="text-center align-middle">Quantity and Size of the Project to be Procured</th>
                      <th class="text-center align-middle">Recommended Mode of Procurement</th>
                      <th class="text-center align-middle">Pre-Procurement Conference, if applicable (Yes/No)</th>
                      <th class="text-center align-middle">Start of Procurement Activity</th>
                      <th class="text-center align-middle">End of Procurement Activity</th>
                      <th class="text-center align-middle">Expected Delivery/ Implementation Period</th>
                      <th class="text-center align-middle">Source of Funds</th>
                      <th class="text-center align-middle">Estimated Budget per Item / Authorized Budgetary Allocation
                        (PhP)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end p-0">
              <a type="button" href="ppmp.php" class="btn btn-secondary btn-sm d-none">
                <i class="fa fa-arrow-left"></i> Back
              </a>

              <button type="submit" class="btn btn-secondary btn-sm me-2" name="is_final" value="No" id="draft_button">
                <i class="fa fa-pencil"></i> Save as Draft
              </button>

              <button type="submit" class="btn btn-primary btn-sm" name="is_final" value="Yes" id="submit_button">
                <i class="fa fa-save"></i> Submit
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
  $(document).ready(function () {

    function formatCurrency(input, final = false) {
      let value = input.value.replace(/[^0-9.]/g, '');
      value = value.replace(/(\..*)\./g, '$1');
      let parts = value.split('.');
      let integerPart = parts[0];
      let decimalPart = parts.length > 1 ? '.' + parts[1] : '';
      integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      input.value = integerPart + decimalPart;
      if (final) {
        let cleanValue = input.value.replace(/,/g, '');
        if (!isNaN(parseFloat(cleanValue)) && cleanValue.length > 0) {
          input.value = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          }).format(parseFloat(cleanValue));
        } else if (cleanValue.length === 0) {
          input.value = '';
        }
      }
    }
    window.formatCurrency = formatCurrency;

    $('#ppmpTable').on('DOMNodeInserted DOMNodeRemoved', 'tbody tr', function () {
      updateTotalDisplay();
    });

    updateTotalDisplay();

    function validateDateSequence() {
      const start = $('#procurement_start_date').val();
      const bidding = $('#bidding_date').val();
      const contract = $('#contract_signing_date').val();
      let isValid = true;

      if (start && bidding && new Date(bidding) < new Date(start)) {
        $('#bidding_date_error').text('Bidding Date must be on or after Start of Procurement Activity Date.');
        isValid = false;
        $('#bidding_date').val('');
      } else {
        $('#bidding_date_error').text('');
      }

      if (bidding && contract && new Date(contract) < new Date(bidding)) {
        $('#contract_signing_error').text('Contract Signing Date must be on or after Bidding/Negotiation Date.');
        isValid = false;
        $('#contract_signing_date').val('');
      } else {
        $('#contract_signing_error').text('');
      }

      return isValid;
    }

    $('#procurement_start_date, #bidding_date, #contract_signing_date').on('change', function () {
      $('#bidding_date_error').text('');
      $('#contract_signing_error').text('');
      validateDateSequence();
    });

    function getCurrentTotal(excludeRow = null) {
      let total = 0;

      $("#ppmpTable tbody tr:not(:last)").each(function () {
        if (excludeRow && this === excludeRow) return true;

        const quantity = parseInt($(this).attr('data-quantity') || '0', 10) || 0;
        const unitPrice = parseFloat($(this).attr('data-unit-price') || '0') || 0;

        total += quantity * unitPrice;
      });

      return total;
    }

    function validateBudgetInput() {
      const quantity = parseInt($("#quantity").val() || 0, 10);
      const unitPrice = parseFloat($("#estimated_budget").val().replace(/,/g, '') || 0);
      const newItemTotal = quantity * unitPrice;

      const allocatedBudget = parseFloat($("#remaining_budget").val() || 0);

      const totalOtherItems = getCurrentTotal(editingRow);

      const projectedTotal = totalOtherItems + newItemTotal;

      if (projectedTotal > allocatedBudget) {
        Swal.fire(
          "Budget Limit Exceeded",
          `Projected total is ₱${projectedTotal.toLocaleString('en-PH', { minimumFractionDigits: 2 })}, but allocated budget is only ₱${allocatedBudget.toLocaleString('en-PH', { minimumFractionDigits: 2 })}.`,
          "error"
        );
        return false;
      }

      return true;
    }

    $("#estimated_budget, #quantity").on("input", validateBudgetInput);

    function updateTotalDisplay() {
      const total = getCurrentTotal();
      const totalText = total.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

      const totalRow = document.querySelector("#ppmpTable tbody tr:last-child");

      if (totalRow && totalRow.cells.length > 1) {
        totalRow.cells[1].innerText = totalText;
      }
    }

    function formatQuantity(n) {
      const num = Number(n) || 0;
      return num.toLocaleString('en-US');
    }

    function formatPesoAccounting(n) {
      const num = Number(n) || 0;
      const abs = Math.abs(num);

      const formatted = abs.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      const withPeso = `₱${formatted}`;
      return num < 0 ? `(${withPeso})` : withPeso;
    }

    function resetEditMode() {
      editingRow = null;
      editingTempItemId = null;

      $('#SaveForm')[0].reset();

      $('#sub_category_id').empty().append('<option value="" disabled selected>Select Subcategory</option>').prop('disabled', true).trigger('change');
      $('#item_name_id').empty().append('<option value="" disabled selected>Select Item Name</option>').prop('disabled', true).trigger('change');

      $('#mode_of_procurement').val('').trigger('change');
      $('#source_of_funds').val('').trigger('change');
      $('#selected_files_preview').empty();

      $('#addBtn')
        .removeClass('btn-success')
        .addClass('btn-primary')
        .html('<i class="fa fa-plus me-1"></i> Add to PPMP');

      $('#clearBtn')
        .removeClass('btn-secondary')
        .addClass('btn-danger')
        .html('<i class="fa fa-times me-1"></i> Clear');
    }

    function renderAttachmentPreview(tempId) {
      const files = tempAttachments[tempId] || [];

      if (files.length === 0) {
        $('#selected_files_preview').html('<small class="text-muted">No file attached.</small>');
        return;
      }

      $('#selected_files_preview').html(
        files.map((file, index) => `
      <span class="badge bg-success me-1 mb-1 d-inline-flex align-items-center">
        <i class="fa fa-file me-1"></i> ${file.name}
        <button type="button"
          class="btn btn-sm btn-link text-white p-0 ml-1 remove-temp-file"
          data-temp-id="${tempId}"
          data-file-index="${index}"
          style="font-size: 12px; line-height: 1;">
          ×
        </button>
      </span>
    `).join('')
      );
    }

    $(document).on('click', '.remove-temp-file', function () {
      const tempId = $(this).data('temp-id');
      const fileIndex = Number($(this).data('file-index'));

      if (!tempAttachments[tempId]) return;

      tempAttachments[tempId].splice(fileIndex, 1);
      renderAttachmentPreview(tempId);

      if (editingRow && editingTempItemId == tempId) {
        editingRow.cells[10].innerHTML = tempAttachments[tempId].length > 0
          ? `<span class="badge bg-success">${tempAttachments[tempId].length} File(s) Attached</span>`
          : `<span class="badge bg-warning">None</span>`;
      }
    });

    function fileKey(file) {
      return `${file.name}_${file.size}_${file.lastModified}`;
    }

    function mergeUniqueFiles(existingFiles, newFiles) {
      const seen = new Set(existingFiles.map(fileKey));
      const merged = [...existingFiles];

      newFiles.forEach(file => {
        const key = fileKey(file);

        if (!seen.has(key)) {
          seen.add(key);
          merged.push(file);
        }
      });

      return merged;
    }

    let tempAttachments = {};
    let nextItemId = 0;
    let editingRow = null;
    let editingTempItemId = null;

    document.getElementById("addBtn").addEventListener("click", function () {
      const isEditing = editingRow !== null;
      const programDescription = document.getElementById("item_description").value.trim();
      const categoryId = document.getElementById("category_id").value;
      const categoryName = $('#category_id option:selected').text();
      const subCategoryId = document.getElementById("sub_category_id").value;
      const subCategoryName = $('#sub_category_id option:selected').text() === 'Select Subcategory' ? '' : $('#sub_category_id option:selected').text();
      const itemName = $('#item_name_id option:selected').text() === 'Select Item Name' ? '' : $('#item_name_id option:selected').text();
      const item_name_id_to_add = document.getElementById("item_name_id").value;

      const specification = document.getElementById("specification").value.trim();
      const modeOfProcurementId = $('#mode_of_procurement').val();
      const modeOfProcurement = $('#mode_of_procurement option:selected').text() === 'Select Mode of Procurement'
        ? ''
        : $('#mode_of_procurement option:selected').text();
      const preProcurementConference = document.getElementById("pre_procurement_conference").value.trim();
      const procurementStartDate = document.getElementById("procurement_start_date").value.trim();
      const biddingDate = document.getElementById("bidding_date").value.trim();
      const contractSigningDate = document.getElementById("contract_signing_date").value.trim();
      const sourceOfFunds = $('#source_of_funds option:selected').text() === 'Select Source of Funds' ? '' : $('#source_of_funds option:selected').text();
      const quantity = parseInt(document.getElementById("quantity").value.trim() || 0, 10);
      const estimatedBudgetInput = document.getElementById("estimated_budget").value;
      const cleanEstimatedBudget = estimatedBudgetInput.replace(/[^0-9.]/g, '');
      const unitPrice = parseFloat(cleanEstimatedBudget || 0);

      const totalItemBudget = quantity * unitPrice;
      const remarks = document.getElementById("remarks").value.trim();

      const combinedItemDetails = `${programDescription}`;
      const categoryDetail = `${categoryName}`;

      const quantityDisplay = formatQuantity(quantity);
      const unitPriceText = formatPesoAccounting(unitPrice);
      const combinedQuantitySpec = `${quantityDisplay}<br>${specification}`;
      const combinedBudgetDetails = `${unitPriceText}`;

      if (totalItemBudget <= 0 || quantity <= 0 || !programDescription || !categoryName || !itemName || !specification || !modeOfProcurement || !preProcurementConference || !procurementStartDate || !biddingDate || !contractSigningDate || !sourceOfFunds) {
        if (typeof Swal !== 'undefined') {
          Swal.fire("Missing Fields", "Please fill out all required fields (Project, Item, Quantity, Specification, Mode, Dates, Source of Funds, and a valid Unit Price) before adding to the table.", "warning");
        }
        return;
      }

      let isDuplicate = false;
      $("#ppmpTable tbody tr:not(:last)").each(function () {
        if (isEditing && this === editingRow) return true;

        const existingItemId = $(this).attr('data-item-id');

        if (existingItemId === item_name_id_to_add) {
          isDuplicate = true;
          return false;
        }
      });

      if (isDuplicate) {
        if (typeof Swal !== 'undefined') {
          Swal.fire("Duplicate Item", `The item **${itemName}** has already been added to the Procurement Plan.`, "error");
        }
        return;
      }

      if (!validateDateSequence()) {
        if (typeof Swal !== 'undefined') {
          Swal.fire("Date Error", "Please correct the sequence of procurement activity dates.", "error");
        }
        return;
      }

      if (!validateBudgetInput()) {
        return;
      }

      const fileInput = document.getElementById("file_attachment");
      const files = Array.from(fileInput.files);

      let currentItemId;

      if (isEditing) {
        currentItemId = editingTempItemId;

        if (!tempAttachments[currentItemId]) {
          tempAttachments[currentItemId] = [];
        }

        if (files.length > 0) {
          tempAttachments[currentItemId] = mergeUniqueFiles(
            tempAttachments[currentItemId],
            files
          );
        }
      } else {
        currentItemId = nextItemId++;
        tempAttachments[currentItemId] = mergeUniqueFiles([], files);
      }

      let newRow;

      if (isEditing) {
        newRow = editingRow;
      } else {
        const tableBody = document.querySelector("#ppmpTable tbody");
        newRow = tableBody.insertRow(tableBody.rows.length - 1);
      }

      newRow.setAttribute('data-temp-item-id', currentItemId);
      newRow.setAttribute('data-category-id', categoryId);
      newRow.setAttribute('data-subcategory-id', subCategoryId);
      newRow.setAttribute('data-item-id', item_name_id_to_add);
      newRow.setAttribute('data-quantity', String(quantity));
      newRow.setAttribute('data-specification', specification);
      newRow.setAttribute('data-proc-mode-id', modeOfProcurementId || '');
      newRow.setAttribute('data-unit-price', String(unitPrice));

      if (isEditing) {
        newRow.cells[0].innerHTML = combinedItemDetails;
        newRow.cells[1].innerHTML = categoryDetail;
        newRow.cells[2].innerHTML = combinedQuantitySpec;
        newRow.cells[3].innerText = modeOfProcurement;
        newRow.cells[4].innerText = preProcurementConference;
        newRow.cells[5].innerText = procurementStartDate;
        newRow.cells[6].innerText = biddingDate;
        newRow.cells[7].innerText = contractSigningDate;
        newRow.cells[8].innerText = sourceOfFunds;
        newRow.cells[9].innerHTML = combinedBudgetDetails;

        const attachmentCount = (tempAttachments[currentItemId] || []).length;

        newRow.cells[10].innerHTML = attachmentCount > 0
          ? `<span class="badge bg-success">${attachmentCount} File(s) Attached</span>`
          : `<span class="badge bg-warning">None</span>`;

        newRow.cells[11].innerText = remarks;
      } else {
        newRow.insertCell(0).innerHTML = combinedItemDetails;
        newRow.insertCell(1).innerHTML = categoryDetail;

        newRow.insertCell(2).innerHTML = combinedQuantitySpec;
        newRow.cells[2].classList.add("text-end");

        newRow.insertCell(3).innerText = modeOfProcurement;
        newRow.insertCell(4).innerText = preProcurementConference;
        newRow.insertCell(5).innerText = procurementStartDate;
        newRow.insertCell(6).innerText = biddingDate;
        newRow.insertCell(7).innerText = contractSigningDate;
        newRow.insertCell(8).innerText = sourceOfFunds;

        newRow.insertCell(9).innerHTML = combinedBudgetDetails;
        newRow.cells[9].classList.add("text-end");

        const attachmentCell = newRow.insertCell(10);
        attachmentCell.innerHTML = tempAttachments[currentItemId].length > 0
          ? `<span class="badge bg-success">${tempAttachments[currentItemId].length} File(s) Attached</span>`
          : `<span class="badge bg-warning">None</span>`;

        newRow.insertCell(11).innerText = remarks;
      }

      let actionCell;

      if (isEditing) {
        actionCell = newRow.cells[12];
        actionCell.innerHTML = "";
      } else {
        actionCell = newRow.insertCell(12);
      }

      actionCell.classList.add("text-center");

      const editBtn = document.createElement("button");
      editBtn.className = "btn btn-sm btn-success me-1";
      editBtn.innerHTML = '<i class="fa fa-edit"></i>';
      editBtn.title = "Edit Item";
      editBtn.type = "button";

      editBtn.addEventListener("click", function () {
        editingRow = newRow;
        editingTempItemId = newRow.getAttribute('data-temp-item-id');

        const savedQuantity = newRow.getAttribute('data-quantity') || '1';
        const savedSpecification = newRow.getAttribute('data-specification') || '';

        $('#item_description').val(newRow.cells[0].innerText.trim());
        $('#category_id').val(newRow.getAttribute('data-category-id')).trigger('change');

        setTimeout(function () {
          $('#sub_category_id')
            .val(newRow.getAttribute('data-subcategory-id'))
            .trigger('change');

          setTimeout(function () {
            $('#item_name_id')
              .val(newRow.getAttribute('data-item-id'))
              .trigger('change');

            $('#quantity').val(savedQuantity);
            $('#specification').val(savedSpecification);
          }, 600);
        }, 600);

        const savedProcModeId = String(newRow.getAttribute('data-proc-mode-id') || '');

        setTimeout(function () {
          $('#mode_of_procurement')
            .val(savedProcModeId)
            .trigger('change.select2')
            .trigger('change');
        }, 100);
        $('#pre_procurement_conference').val(newRow.cells[4].innerText.trim());
        $('#procurement_start_date').val(newRow.cells[5].innerText.trim());
        $('#bidding_date').val(newRow.cells[6].innerText.trim());
        $('#contract_signing_date').val(newRow.cells[7].innerText.trim());
        $('#source_of_funds').val(newRow.cells[8].innerText.trim()).trigger('change');

        const tempId = newRow.getAttribute('data-temp-item-id');
        const files = tempAttachments[tempId] || [];

        renderAttachmentPreview(tempId);

        $('#estimated_budget').val(newRow.getAttribute('data-unit-price'));
        formatCurrency(document.getElementById('estimated_budget'), true);

        $('#remarks').val(newRow.cells[11].innerText.trim());

        $('#addBtn')
          .removeClass('btn-primary')
          .addClass('btn-success')
          .html('<i class="fa fa-save me-1"></i> Update Item');

        $('#clearBtn')
          .removeClass('btn-danger')
          .addClass('btn-secondary')
          .html('<i class="fa fa-times me-1"></i> Cancel Edit');
      });

      const removeBtn = document.createElement("button");
      removeBtn.className = "btn btn-sm btn-danger";
      removeBtn.innerHTML = '<i class="fa fa-trash"></i>';
      removeBtn.title = "Remove Item";
      removeBtn.type = "button";

      removeBtn.addEventListener("click", function () {
        Swal.fire({
          title: "Are you sure?",
          text: "You are about to remove this item from PPMP.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, remove it!",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#a83232",
          cancelButtonColor: "#555555"
        }).then((result) => {
          if (result.isConfirmed) {
            const tempId = newRow.getAttribute('data-temp-item-id');
            delete tempAttachments[tempId];
            newRow.remove();
            updateTotalDisplay();

            if (editingRow === newRow) {
              resetEditMode();
            }
          }
        });
      });

      actionCell.appendChild(editBtn);
      actionCell.appendChild(removeBtn);

      $('#file_attachment').val('');
      resetEditMode();
      updateTotalDisplay();

      $('#sub_category_id').empty().append('<option value="" disabled selected>Select Subcategory</option>').prop('disabled', true).trigger('change');
      $('#item_name_id').empty().append('<option value="" disabled selected>Select Item Name</option>').prop('disabled', true).trigger('change');
      $('#mode_of_procurement').val('').trigger('change');
      $('#source_of_funds').val('').trigger('change');
    });

    $('#clearBtn').on('click', function () {
      resetEditMode();
    });

    $('#sub_category_id').select2({ width: '100%', placeholder: 'Select Subcategory' });
    $('#item_name_id').select2({ width: '100%', placeholder: 'Select Item Name' });
    $('#mode_of_procurement').select2({ width: '100%', placeholder: 'Select Mode of Procurement' });
    $('#source_of_funds').select2({ width: '100%', placeholder: 'Select Source of Funds' });

    $('#category_id').change(function () {
      const categoryId = $(this).val();
      if (!categoryId) return;

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { action: 'GetSubcategoriesByCategory', category_id: categoryId },
        dataType: 'json',
        success: function (response) {
          const $subCatSelect = $('#sub_category_id');
          $subCatSelect.empty();

          if (response.success && response.data.length > 0) {
            $subCatSelect.append('<option value="" disabled selected>Select Subcategory</option>');
            response.data.forEach(sub => {
              $subCatSelect.append(`<option value="${sub.sub_category_id}">${sub.sub_cat_name}</option>`);
            });
            $subCatSelect.prop('disabled', false);
          } else {
            $subCatSelect.append('<option value="" disabled selected>No subcategories available</option>');
            $subCatSelect.prop('disabled', true);
          }
        },
        error: function (xhr) {
          console.error('Error fetching subcategories:', xhr.responseText);
        }
      });
    });

    $('#sub_category_id').change(function () {
      const subCategoryId = $(this).val();
      const $itemSelect = $('#item_name_id');

      if ($itemSelect.data('select2')) {
        $itemSelect.select2('destroy');
      }

      $itemSelect.empty().append('<option value="" disabled selected>Select Item Name</option>');
      $itemSelect.prop('disabled', true);

      if (!subCategoryId) {
        $itemSelect.select2({
          width: '100%',
          placeholder: 'Select Item Name'
        });
        return;
      }

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { action: 'GetItemNamesBySubCategory', sub_category_id: subCategoryId },
        dataType: 'json',
        success: function (response) {
          $itemSelect.empty();

          if (response.success && response.data.length > 0) {
            $itemSelect.append('<option value="" disabled selected>Select Item Name</option>');
            response.data.forEach(item => {
              $itemSelect.append(`<option value="${item.item_name_id}">${item.item_name}</option>`);
            });
            $itemSelect.prop('disabled', false);
          } else {
            $itemSelect.append('<option value="" disabled selected>No items found in selected sub category</option>');
            $itemSelect.prop('disabled', true);
          }

          $itemSelect.select2({ width: '100%' });

          if (!editingRow) {
            $('#specification').val('');
            $('#quantity').val('1');
          }

          $('#unit_cost').val('');
        },
        error: function (xhr) {
          console.error('Error fetching item names:', xhr.responseText);
          Swal.fire("Error", "Could not load item names. Check console for details.", "error");

          $itemSelect.select2({
            width: '100%',
            placeholder: 'Select Item Name'
          });
        }
      });
    });

    let submissionAction = '';
    $('#draft_button').on('click', function () {
      submissionAction = 'Draft';
    });
    $('#submit_button').on('click', function () {
      submissionAction = 'Final';
    });

    $('#SaveForm').submit(async function (e) {
      e.preventDefault();

      if (submissionAction === 'Final') {
        const remainingBudget = parseFloat($('#remaining_budget').val() || 0);

        let ppmpTotal = 0;
        $('#ppmpTable tbody tr:not(:last)').each(function () {
          const row = $(this);
          const quantity = parseInt((row.attr('data-quantity') || '0').replace(/,/g, ''), 10) || 0;
          const unitPrice = parseFloat((row.attr('data-unit-price') || '0').replace(/,/g, '')) || 0;
          ppmpTotal += (quantity * unitPrice);
        });

        const remainingAfterPPMP = remainingBudget - ppmpTotal;

        if (remainingAfterPPMP >= 500) {
          Swal.fire(
            "Budget Underutilized",
            `Your PPMP would leave ₱${remainingAfterPPMP.toLocaleString()} unspent. 
                You must utilize your full budget (only ₱500 or less may remain).`,
            "warning"
          );
          return;
        }

        const firstPrompt = await Swal.fire({
          title: "Are you sure?",
          text: "Are you sure you want to submit this PPMP? Changes will no longer be allowed once finalized.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, continue",
          cancelButtonText: "Cancel"
        });

        if (!firstPrompt.isConfirmed) {
          return;
        }

        const secondPrompt = await Swal.fire({
          title: "Final Confirmation",
          text: "This action is final and cannot be undone. Proceed with submission?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Submit",
          cancelButtonText: "Cancel"
        });

        if (!secondPrompt.isConfirmed) {
          return;
        }
      }

      const items = [];
      const $itemRows = $('#ppmpTable tbody tr:not(:last)');

      if ($itemRows.length === 0) {
        showSweetAlert("No Items", "Please add at least one PPMP item before submitting.", "warning");
        return;
      }

      const fileFormData = new FormData();

      $itemRows.each(function () {
        const row = $(this);
        const tempItemId = row.attr('data-temp-item-id');
        const categoryId = row.attr('data-category-id');
        const subCategoryId = row.attr('data-subcategory-id');

        const programDescription = row.find('td:eq(0)').text().trim();
        const categoryName = row.find('td:eq(1)').text().trim();

        const quantity = parseInt((row.attr('data-quantity') || '0').replace(/,/g, ''), 10) || 0;
        const unitPrice = parseFloat((row.attr('data-unit-price') || '0').replace(/,/g, '')) || 0;

        const quantitySpecHTML = row.find('td:eq(2)').html();
        const parts = (quantitySpecHTML || '').split('<br>');
        const specification = (parts.length > 1 ? parts.slice(1).join('<br>').trim() : '');

        const estimated_budget = unitPrice;
        const total_cost = estimated_budget * quantity;

        const itemName = row.attr('data-item-id');

        const itemObject = {
          temp_item_id: tempItemId,
          category_id: categoryId,
          sub_category_id: subCategoryId,
          item_name_id: row.attr('data-item-id'),

          item_description: programDescription,
          specifications: specification,
          quantity: quantity,

          mode_of_procurement_id: row.attr('data-proc-mode-id'),
          mode_of_procurement: row.find('td:eq(3)').text().trim(),
          pre_procurement_conference: row.find('td:eq(4)').text().trim(),
          procurement_start_date: row.find('td:eq(5)').text().trim(),
          bidding_date: row.find('td:eq(6)').text().trim(),
          contract_signing_date: row.find('td:eq(7)').text().trim(),
          source_of_funds: row.find('td:eq(8)').text().trim(),

          estimated_budget: estimated_budget,
          total_cost: total_cost,
          remarks: row.find('td:eq(11)').text().trim(),

          category_name: categoryName,
          sub_category_name: subCategoryId,
          item_name: itemName
        };

        items.push(itemObject);

        const filesToUpload = tempAttachments[tempItemId] || [];
        filesToUpload.forEach(function (file, index) {
          fileFormData.append(`file_${tempItemId}_${index}`, file);
        });
      });

      const hasUnsavedInputs =
        $('#category_id').val() ||
        $('#sub_category_id').val() ||
        $('#item_name_id').val() ||
        $('#item_description').val().trim() ||
        $('#specification').val().trim() ||
        (parseInt($('#quantity').val() || 1, 10) !== 1) ||
        $('#estimated_budget').val().trim() ||
        $('#mode_of_procurement').val() ||
        $('#pre_procurement_conference').val() ||
        $('#procurement_start_date').val().trim() ||
        $('#bidding_date').val().trim() ||
        $('#contract_signing_date').val().trim() ||
        $('#source_of_funds').val() ||
        $('#remarks').val().trim() ||
        $('#file_attachment')[0].files.length > 0;

      if (hasUnsavedInputs) {
        showSweetAlert("Unsaved Item Detected", "You have filled out fields but didn’t add the item to the PPMP list. Please click 'Add to PPMP' first or clear the fields.", "warning");
        return;
      }

      const formData = new FormData();
      formData.append('user_id', $('#user_id').val());
      formData.append('remaining_budget', $('#remaining_budget').val());
      formData.append('action', 'AddPPMPForm');

      const isFinalValue = (submissionAction === 'Final') ? 1 : 0;
      formData.append('is_final', isFinalValue);

      submissionAction = '';

      formData.append('ppmp_items', JSON.stringify(items));
      for (let pair of fileFormData.entries()) {
        formData.append(pair[0], pair[1]);
      }

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,

        beforeSend: function () {
          const buttonId = (isFinalValue === 1) ? '#submit_button' : '#draft_button';
          const buttonText = (isFinalValue === 1) ? 'Submitting...' : 'Saving...';

          $('#submit_button, #draft_button').prop('disabled', true);
          $(buttonId).html('<i class="fa fa-spinner fa-spin"></i> ' + buttonText);
        },

        success: function (response) {
          $('#submit_button').prop('disabled', false).html('<i class="fa fa-save"></i> Submit');
          $('#draft_button').prop('disabled', false).html('<i class="fa fa-pencil"></i> Save as Draft');

          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "ppmp.php");
          } else {
            showSweetAlert("Error", response.message, "error");
          }
        },
        error: function (xhr) {
          $('#submit_button').prop('disabled', false).html('<i class="fa fa-save"></i> Submit');
          $('#draft_button').prop('disabled', false).html('<i class="fa fa-pencil"></i> Save as Draft');
          console.error(xhr.responseText);
          showSweetAlert("Error", "Something went wrong during submission. Check console for details.", "error");
        }
      });
    });

  });
</script>