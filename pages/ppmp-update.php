<?php
require_once '../php/auth_check.php';

if (!($canCreatePPMP)) {
  header("Location: 404.php");
  exit();
}

$ppmpVersionId = $_GET['ppmp_version_id'] ?? null;

if (empty($ppmpVersionId) || !is_numeric($ppmpVersionId)) {
  header("Location: ppmp.php");
  exit();
}

$ppmp_header = $db->getPPMPHeaderDetailsByVersionId((int) $ppmpVersionId);

if (!$ppmp_header) {
  header("Location: ppmp.php");
  exit();
}

$ppmp_id = (int) $ppmp_header['ppmp_id'];
$ppmp_items_result = $db->getPPMPItemsByVersionId((int) $ppmpVersionId);

$ppmp_items = [];
while ($row = $ppmp_items_result->fetch_assoc()) {
  $ppmp_items[] = $row;
}

$ppmp_code = htmlspecialchars($ppmp_header['ppmp_code'] ?? 'N/A');
$ppmp_status = htmlspecialchars($ppmp_header['status'] ?? 'N/A');
$fiscal_year = htmlspecialchars($ppmp_header['fiscal_year'] ?? 'N/A');

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
        <h2>PPMP Update</h2>
        <div class="clearfix"></div>
      </div>

      <div class="x_content">
        <div class="card-box">
          <form id="UpdateForm" autocomplete="off" enctype="multipart/form-data">
            <div class="card-body p-0">
              <input type="hidden" class="form-control" name="ppmp_id" id="ppmp_id" value="<?= $ppmp_id ?>">
              <input type="hidden" class="form-control" name="ppmp_code" id="ppmp_code" value="<?= $ppmp_code ?>">
              <input type="hidden" class="form-control" name="user_id" id="user_id" value="<?= $userId ?>">
              <?php
              $budgetCeiling = (float) ($ppmp_header['total_amount'] ?? 0);
              ?>

              <input type="hidden" class="form-control" name="remaining_budget" id="remaining_budget"
                value="<?= $budgetCeiling ?>">

              <input type="hidden" id="budget_ceiling" value="<?= $budgetCeiling ?>">
              <input type="hidden" class="form-control" name="ppmp_status" id="ppmp_status" value="<?= $ppmp_status ?>">

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
                    <div id="existing_attachments_display" class="mt-2"></div>
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
                      <th class="text-center align-middle">Pre-Procurement Conference, if applicable</th>
                      <th class="text-center align-middle">Start of Procurement Activity</th>
                      <th class="text-center align-middle">End of Procurement Activity</th>
                      <th class="text-center align-middle">Expected Delivery/ Implementation Period</th>
                      <th class="text-center align-middle">Source of Funds</th>
                      <th class="text-center align-middle">Estimated Budget per Item / Authorized Budgetary Allocation
                      </th>
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
              <a type="button" href="ppmp.php" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Back
              </a>
              <button type="submit" class="btn btn-primary btn-sm" id="submit_button">
                <i class="fa fa-save"></i> Update
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
  const PPMP_ITEMS_DATA = <?= json_encode($ppmp_items) ?>;

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
        input.value = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(cleanValue));
      } else if (cleanValue.length === 0) {
        input.value = '';
      }
    }
  }
  window.formatCurrency = formatCurrency;

  function convertDateFormat(dateString) {
    if (!dateString || dateString === '0000-00-00') return '';
    return dateString;
  }

  function cleanCurrency(text) {
    return parseFloat(String(text || '').replace(/[^0-9.]+/g, '') || 0);
  }

  function parseQuantityFromCellHtml(html) {
    const firstLine = String(html || '').split('<br>')[0] || '';
    const match = firstLine.match(/(\d+)/);
    return match ? parseInt(match[1], 10) : 0;
  }

  function parseSpecFromCellHtml(html) {
    const parts = String(html || '').split('<br>');
    if (parts.length < 2) return '';
    return parts.slice(1).join('<br>').replace(/<[^>]*>/g, '').trim();
  }

  function buildItemFromRow($row) {
    const qtySpecHtml = $row.find('td:eq(2)').html() || '';
    const quantity = parseQuantityFromCellHtml(qtySpecHtml);
    const specifications = parseSpecFromCellHtml(qtySpecHtml);

    const unitCostText = $row.find('td:eq(9)').text().trim();
    const estimated_budget = cleanCurrency(unitCostText);

    return {
      row_key: $row.attr('data-row-key') || '',
      temp_item_id: $row.attr('data-temp-item-id') || '',
      is_new: $row.attr('data-is-new') === 'true',
      file_attachment: $row.attr('data-file-attachment') || '',

      category_id: $row.attr('data-category-id') || '',
      sub_category_id: $row.attr('data-subcategory-id') || '',
      item_name_id: $row.attr('data-item-name-id') || $row.attr('data-item-id') || '',
      mode_of_procurement_id: $row.attr('data-proc-mode-id') || '',

      item_description: $row.find('td:eq(0)').text().trim(),
      category_name: $row.find('td:eq(1)').text().trim(),
      quantity,
      specifications,


      mode_of_procurement: $row.find('td:eq(3)').text().trim(),
      pre_procurement_conference: $row.find('td:eq(4)').text().trim(),
      procurement_start_date: $row.find('td:eq(5)').text().trim(),
      bidding_date: $row.find('td:eq(6)').text().trim(),
      contract_signing_date: $row.find('td:eq(7)').text().trim(),
      source_of_funds: $row.find('td:eq(8)').text().trim(),

      estimated_budget,
      remarks: $row.find('td:eq(11)').text().trim()
    };
  }

  function loadExistingItems(items) {
    const $tbody = $("#ppmpTable tbody");
    $tbody.empty();

    let grandTotal = 0;

    if (!items || items.length === 0) {
      $tbody.append('<tr><td colspan="13" class="text-center">No existing items found for this PPMP.</td></tr>');
      $tbody.append('<tr><td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td><td></td><td></td><td></td><td></td></tr>');
      return;
    }

    items.forEach((item, index) => {
      const totalCost = parseFloat(item.total_cost || 0);
      grandTotal += totalCost;

      const formattedUnitCost = parseFloat(item.unit_cost || item.estimated_budget || 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
      const procStart = item.procurement_start_date || 'N/A';
      const biddingDate = item.bidding_date || 'N/A';
      const deliveryDate = item.contract_signing_date || 'N/A';

      const combinedItemDetails = `${item.item_description || 'N/A'}`;
      const combinedQuantitySpec = `${item.quantity || 0}<br>${item.specifications || 'N/A'}`;
      const categoryDetail = `${item.category_name || 'N/A'}`;
      const combinedBudgetDetails = `${formattedUnitCost}`;

      const fileAttachmentDisplay = item.file_attachment ? `<span class="badge bg-primary">Attached</span>` : `<span class="badge bg-warning">None</span>`;
      const fileAttachmentString = item.file_attachment || '';

      const rowKey = `ex_${item.ppmp_item_id || item.ppmp_version_item_id || item.item_id}_${index}`;

      $tbody.append(`
        <tr
            data-item-id="${item.item_id}"
            data-type="existing"
            data-file-attachment="${fileAttachmentString}"
            data-is-new="false"
            data-category-id="${item.category_id || ''}"
            data-subcategory-id="${item.sub_category_id || ''}"
            data-item-name-id="${item.item_name_id || ''}"
            data-proc-mode-id="${item.mode_of_procurement_id || ''}"
            data-is-returned="${item.is_returned}"
            data-row-key="${rowKey}"
          >
          <td>${combinedItemDetails}</td>
          <td>${categoryDetail}</td>
          <td>${combinedQuantitySpec}</td>
          <td class="text-center">${item.mode_of_procurement || '-'}</td>
          <td class="text-center">${item.pre_procurement_conference || '-'}</td>
          <td class="text-center">${procStart}</td>
          <td class="text-center">${biddingDate}</td>
          <td class="text-center">${deliveryDate}</td>
          <td class="text-center">${item.source_of_funds || '-'}</td>
          <td class="text-end">${combinedBudgetDetails}</td>
          <td class="text-center">${fileAttachmentDisplay}</td>
          <td>${item.remarks || '-'}</td>
          <td class="text-center">
            <button type="button" class="btn btn-success btn-sm edit-item-btn" title="Edit Item">
              <i class="fa fa-edit"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm delete-item-btn" data-id="${item.item_id}" title="Delete Item">
              <i class="fa fa-trash"></i>
            </button>
            
          </td>
        </tr>
      `);
    });

    const formattedGrandTotal = grandTotal.toLocaleString('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
    $tbody.append(`
      <tr>
        <td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td>
        <td class="text-end fw-bold">${formattedGrandTotal}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    `);
  }

  $(document).ready(function () {
    let editingRowEl = null;
    let tempAttachments = {};
    let nextItemId = 0;

    let existingAttachmentsMap = {};
    let removedExistingAttachmentsMap = {};

    let suppressDependentChange = false;
    let populateToken = 0;

    $('#sub_category_id').select2({ width: '100%', placeholder: 'Select Subcategory' });
    $('#item_name_id').select2({ width: '100%', placeholder: 'Select Item Name' });
    $('#mode_of_procurement').select2({ width: '100%', placeholder: 'Select Mode of Procurement' });
    $('#source_of_funds').select2({ width: '100%', placeholder: 'Select Source of Funds' });

    loadExistingItems(PPMP_ITEMS_DATA);
    updateTotalDisplay();

    function normalizeAttachmentList(fileAttachment) {
      return String(fileAttachment || '')
        .split(',')
        .map(x => x.trim())
        .filter(Boolean);
    }

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

    function renderUpdateAttachmentPreview(rowKey) {
      const existingFiles = existingAttachmentsMap[rowKey] || [];
      const removedFiles = removedExistingAttachmentsMap[rowKey] || [];
      const newFiles = tempAttachments[rowKey] || [];

      const activeExisting = existingFiles.filter(file => !removedFiles.includes(file));

      let html = '';

      activeExisting.forEach(fileName => {
        const displayName = fileName.substring(fileName.lastIndexOf('/') + 1);
        const fullPath = `../assets/ppmp_attachments/${fileName}`;

        html += `
      <span class="badge bg-primary me-1 mb-1 d-inline-flex align-items-center">
        <i class="fa fa-file me-1"></i>
        <a href="${fullPath}" target="_blank" class="text-white text-decoration-none">${displayName}</a>
        <button type="button"
          class="btn btn-sm btn-link text-white p-0 ml-1 remove-existing-file"
          data-row-key="${rowKey}"
          data-file-name="${fileName}"
          style="font-size:12px; line-height:1;">×</button>
      </span>
    `;
      });

      newFiles.forEach((file, index) => {
        html += `
      <span class="badge bg-success me-1 mb-1 d-inline-flex align-items-center">
        <i class="fa fa-file me-1"></i> ${file.name}
        <button type="button"
          class="btn btn-sm btn-link text-white p-0 ml-1 remove-new-file"
          data-row-key="${rowKey}"
          data-file-index="${index}"
          style="font-size:12px; line-height:1;">×</button>
      </span>
    `;
      });

      $('#existing_attachments_display').html(
        html || '<small class="text-muted">No files currently attached.</small>'
      );
    }

    function isEditing() {
      return editingRowEl !== null;
    }

    function resetDependentSelects() {
      $('#sub_category_id').empty().append('<option value="" disabled selected>Select Subcategory</option>').prop('disabled', true).trigger('change');
      $('#item_name_id').empty().append('<option value="" disabled selected>Select Item Name</option>').prop('disabled', true).trigger('change');
    }

    function setAddMode() {
      editingRowEl = null;
      $('#item_id_to_edit').remove();
      $('#existing_attachments_display').empty();
      $('#UpdateForm')[0].reset();
      resetDependentSelects();

      if ($('#updateBtn').length) {
        $('#updateBtn')
          .html('<i class="fa fa-plus me-1"></i> Add to PPMP')
          .removeClass('btn-success')
          .addClass('btn-primary')
          .attr('id', 'addBtn');
      }

      $('#clearBtn').text('Clear').removeClass('btn-secondary').addClass('btn-danger');

    }

    function setEditMode() {
      if ($('#addBtn').length) {
        $('#addBtn')
          .html('<i class="fa fa-save me-1"></i> Update Item')
          .removeClass('btn-primary')
          .addClass('btn-success')
          .attr('id', 'updateBtn');
      } else {
        $('#updateBtn').html('<i class="fa fa-save me-1"></i> Update Item');
      }

      $('#clearBtn')
        .html('<i class="fa fa-times me-1"></i> Cancel Edit')
        .removeClass('btn-danger')
        .addClass('btn-secondary');
    }

    async function loadSubcategories(categoryId, selectedSubCategoryId) {
      const $subCatSelect = $('#sub_category_id');
      $subCatSelect.empty();

      if (!categoryId) {
        $subCatSelect.append('<option value="" disabled selected>Select Subcategory</option>').prop('disabled', true).trigger('change');
        return;
      }

      return $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { action: 'GetSubcategoriesByCategory', category_id: categoryId },
        dataType: 'json'
      }).then(function (response) {
        if (response.success && response.data.length > 0) {
          $subCatSelect.append('<option value="" disabled selected>Select Subcategory</option>');
          response.data.forEach(sub => {
            $subCatSelect.append(`<option value="${sub.sub_category_id}">${sub.sub_cat_name}</option>`);
          });
          $subCatSelect.prop('disabled', false);

          if (selectedSubCategoryId) {
            $subCatSelect.val(String(selectedSubCategoryId)).trigger('change');
          }
        } else {
          $subCatSelect.append('<option value="" disabled selected>No subcategories available</option>');
          $subCatSelect.prop('disabled', true).trigger('change');
        }
      });
    }

    async function loadItemNames(subCategoryId, selectedItemNameId) {
      const $itemSelect = $('#item_name_id');

      if ($itemSelect.data('select2')) {
        $itemSelect.select2('destroy');
      }

      $itemSelect.empty().append('<option value="" disabled selected>Select Item Name</option>').prop('disabled', true);

      if (!subCategoryId) {
        $itemSelect.select2({ width: '100%', placeholder: 'Select Item Name' });
        return;
      }

      return $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { action: 'GetItemNamesBySubCategory', sub_category_id: subCategoryId },
        dataType: 'json'
      }).then(function (response) {
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

        if (selectedItemNameId && $itemSelect.find(`option[value="${selectedItemNameId}"]`).length) {
          $itemSelect.val(String(selectedItemNameId)).trigger('change.select2');
        }

        if (!isEditing()) {
          $('#quantity').val('1');
        }
      }).fail(function (xhr) {
        console.error('Error fetching item names:', xhr.responseText);
        Swal.fire("Error", "Could not load item names. Check console for details.", "error");
        $itemSelect.select2({ width: '100%', placeholder: 'Select Item Name' });
      });
    }

    function renderExistingAttachments(fileAttachment) {
      const $attachmentDisplay = $('#existing_attachments_display');
      $attachmentDisplay.empty();

      if (fileAttachment) {
        String(fileAttachment).split(',').forEach(fileName => {
          const trimmed = String(fileName).trim();
          if (!trimmed) return;
          const fullPath = `../assets/ppmp_attachments/${trimmed}`;
          const displayName = trimmed.substring(trimmed.lastIndexOf('/') + 1);
          $attachmentDisplay.append(`
            <span class="badge bg-success me-2 mb-1 d-inline-flex align-items-center">
              <i class="fa fa-file me-1"></i>
              <a href="${fullPath}" target="_blank" class="text-white text-decoration-none">${displayName}</a>
            </span>
          `);
        });
      } else {
        $attachmentDisplay.html('<small class="text-muted">No files currently attached.</small>');
      }
    }

    async function populateFormForEdit(item) {
      const myToken = ++populateToken;
      suppressDependentChange = true;

      $('#UpdateForm')[0].reset();
      resetDependentSelects();

      const rowKey = item.row_key || '';
      existingAttachmentsMap[rowKey] = normalizeAttachmentList(item.file_attachment || '');

      if (!removedExistingAttachmentsMap[rowKey]) {
        removedExistingAttachmentsMap[rowKey] = [];
      }

      renderUpdateAttachmentPreview(rowKey);

      $(document).on('click', '.remove-existing-file', function () {
        const rowKey = String($(this).data('row-key'));
        const fileName = String($(this).data('file-name'));

        if (!removedExistingAttachmentsMap[rowKey]) {
          removedExistingAttachmentsMap[rowKey] = [];
        }

        if (!removedExistingAttachmentsMap[rowKey].includes(fileName)) {
          removedExistingAttachmentsMap[rowKey].push(fileName);
        }

        renderUpdateAttachmentPreview(rowKey);
      });

      $(document).on('click', '.remove-new-file', function () {
        const rowKey = String($(this).data('row-key'));
        const fileIndex = Number($(this).data('file-index'));

        if (!tempAttachments[rowKey]) return;

        tempAttachments[rowKey].splice(fileIndex, 1);
        renderUpdateAttachmentPreview(rowKey);
      });

      $('#item_description').val(item.item_description || '');
      $('#quantity').val(item.quantity || 1);
      $('#specification').val(item.specifications || '');
      $('#estimated_budget').val(Number(item.estimated_budget || 0).toFixed(2));
      $('#remarks').val(item.remarks || '');

      $('#procurement_start_date').val(convertDateFormat(item.procurement_start_date));
      $('#bidding_date').val(convertDateFormat(item.bidding_date));
      $('#contract_signing_date').val(convertDateFormat(item.contract_signing_date));

      $('#pre_procurement_conference').val(item.pre_procurement_conference || '').trigger('change');
      $('#mode_of_procurement').val(String(item.mode_of_procurement_id || '')).trigger('change.select2');
      $('#source_of_funds').val(item.source_of_funds || '').trigger('change.select2');

      $('#category_id').val(item.category_id || '');

      await loadSubcategories(item.category_id || '', item.sub_category_id || '');
      if (myToken !== populateToken) return;

      await loadItemNames(item.sub_category_id || '', item.item_name_id || '');
      if (myToken !== populateToken) return;

      $('#category_id').trigger('change.select2');
      $('#sub_category_id').trigger('change.select2');
      $('#item_name_id').trigger('change.select2');

      suppressDependentChange = false;
    }

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

    function getCurrentTotal(excludeRowEl = null) {
      let total = 0;

      $("#ppmpTable tbody tr:not(:last)").each(function () {
        const rowEl = this;
        if (excludeRowEl && rowEl === excludeRowEl) return;

        const $row = $(rowEl);
        const qtySpecHtml = $row.find('td:eq(2)').html() || '';
        const quantity = parseQuantityFromCellHtml(qtySpecHtml);

        const unitCostText = $row.find('td:eq(9)').text().trim();
        const unitPrice = cleanCurrency(unitCostText);

        const itemTotal = quantity * unitPrice;
        total += isNaN(itemTotal) ? 0 : itemTotal;
      });

      return total;
    }

    function updateTotalDisplay() {
      const total = getCurrentTotal();
      const totalText = total.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
      const totalRow = document.querySelector("#ppmpTable tbody tr:last-child");
      if (totalRow && totalRow.cells.length > 1) {
        totalRow.cells[1].innerText = totalText;
      }
    }

    function validateBudgetInput() {

      const quantity = parseInt($("#quantity").val() || 0, 10);

      const estimatedBudgetInput = $("#estimated_budget").val();
      const unitPrice = parseFloat(String(estimatedBudgetInput || '').replace(/,/g, '') || 0);

      const formItemTotal = quantity * unitPrice;

      const budgetCeiling = parseFloat($("#budget_ceiling").val() || 0);

      let currentTableTotal = 0;

      $("#ppmpTable tbody tr:not(:last)").each(function () {

        if (editingRowEl && this === editingRowEl) {
          return;
        }

        const $row = $(this);

        const qtySpecHtml = $row.find('td:eq(2)').html() || '';
        const qty = parseQuantityFromCellHtml(qtySpecHtml);

        const costText = $row.find('td:eq(9)').text().trim();
        const cost = cleanCurrency(costText);

        currentTableTotal += qty * cost;
      });

      const projectedTotal = currentTableTotal + formItemTotal;

      if (projectedTotal > budgetCeiling) {

        Swal.fire({
          title: "Budget Limit Exceeded",
          text: `Projected total (₱${projectedTotal.toLocaleString('en-PH', { minimumFractionDigits: 2 })}) exceeds PPMP budget ceiling (₱${budgetCeiling.toLocaleString('en-PH', { minimumFractionDigits: 2 })}).`,
          icon: "error"
        });

        return false;
      }

      return true;
    }

    $("#estimated_budget, #quantity").on("input", function () {
      validateBudgetInput();
    });

    $('#ppmpTable').on('DOMNodeInserted DOMNodeRemoved', 'tbody tr', function () {
      updateTotalDisplay();
    });

    $('#category_id').on('change', function () {
      if (suppressDependentChange) return;
      const categoryId = $(this).val();
      loadSubcategories(categoryId, null);
    });

    $('#sub_category_id').on('change', function () {
      if (suppressDependentChange) return;
      const subCategoryId = $(this).val();
      loadItemNames(subCategoryId, null);
    });


    $('#ppmpTable').on('click', '.edit-item-btn', function (e) {
      e.stopPropagation();

      const $row = $(this).closest('tr');
      if ($row.is(':last-child')) return;

      editingRowEl = $row[0];
      setEditMode();

      const item = buildItemFromRow($row);

      $('#item_id_to_edit').remove();
      $('#UpdateForm').prepend(`<input type="hidden" id="item_id_to_edit" value="${item.row_key || ''}">`);

      populateFormForEdit(item);
    });

    $(document).on('click', '#clearBtn', function () {
      if (isEditing()) {
        setAddMode();
        return;
      }
      const form = $('#UpdateForm')[0];
      form.reset();
      $('#existing_attachments_display').empty();
      resetDependentSelects();
    });

    $(document).on('click', '.delete-item-btn', function () {
      const $row = $(this).closest('tr');

      Swal.fire({
        title: "Remove Item?",
        text: "This item will be removed from the updated PPMP version after you submit.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, remove it",
        cancelButtonText: "Cancel"
      }).then((result) => {
        if (!result.isConfirmed) return;

        $row.remove();
        updateTotalDisplay();

        if (isEditing()) {
          setAddMode();
        }
      });
    });

    $(document).on('click', '#addBtn', function () {
      const programDescription = $('#item_description').val().trim();
      const categoryId = $('#category_id').val();
      const categoryName = $('#category_id option:selected').text();
      const subCategoryId = $('#sub_category_id').val();
      const itemNameId = $('#item_name_id').val();
      const itemName = $('#item_name_id option:selected').text() === 'Select Item Name' ? '' : $('#item_name_id option:selected').text();

      const specification = $('#specification').val().trim();
      const quantity = parseInt($('#quantity').val() || 0, 10);

      const modeOfProcurementId = $('#mode_of_procurement').val();
      const modeOfProcurement = $('#mode_of_procurement option:selected').text() === 'Select Mode of Procurement'
        ? ''
        : $('#mode_of_procurement option:selected').text();
      const preProcurementConference = $('#pre_procurement_conference').val();
      const procurementStartDate = $('#procurement_start_date').val();
      const biddingDate = $('#bidding_date').val();
      const contractSigningDate = $('#contract_signing_date').val();
      const sourceOfFunds = $('#source_of_funds option:selected').text() === 'Select Source of Funds' ? '' : $('#source_of_funds option:selected').text();

      const estimatedBudgetInput = $('#estimated_budget').val();
      const unitPrice = cleanCurrency(estimatedBudgetInput);
      const unitPriceText = unitPrice.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

      const remarks = $('#remarks').val().trim();

      const totalItemBudget = quantity * unitPrice;

      if (totalItemBudget <= 0 || quantity <= 0 || !programDescription || !categoryId || !subCategoryId || !itemNameId || !itemName || !specification || !modeOfProcurement || !preProcurementConference || !procurementStartDate || !biddingDate || !contractSigningDate || !sourceOfFunds) {
        Swal.fire("Missing Fields", "Please fill out all required fields before adding to the table.", "warning");
        return;
      }

      let isDuplicate = false;
      $("#ppmpTable tbody tr:not(:last)").each(function () {
        const existingItemNameId = $(this).attr('data-item-name-id') || $(this).attr('data-item-id');
        if (String(existingItemNameId) === String(itemNameId)) {
          isDuplicate = true;
          return false;
        }
      });

      if (isDuplicate) {
        Swal.fire("Duplicate Item", `The item **${itemName}** has already been added to the Procurement Plan.`, "error");
        return;
      }

      if (!validateDateSequence()) {
        Swal.fire("Date Error", "Please correct the sequence of procurement activity dates.", "error");
        return;
      }

      if (!validateBudgetInput()) {
        return;
      }

      const fileInput = document.getElementById("file_attachment");
      const files = Array.from(fileInput.files || []);
      const currentTempId = String(nextItemId++);
      tempAttachments[currentTempId] = files;

      const $tbody = $("#ppmpTable tbody");
      const $totalRow = $tbody.find('tr:last-child');

      const fileBadge = files.length > 0
        ? `<span class="badge bg-success">${files.length} File(s) Attached</span>`
        : `<span class="badge bg-warning">None</span>`;

      const rowKey = `new_${currentTempId}`;

      const $newRow = $(`
        <tr
          data-temp-item-id="${currentTempId}"
          data-category-id="${categoryId}"
          data-subcategory-id="${subCategoryId}"
          data-item-id="${itemNameId}"
          data-item-name-id="${itemNameId}"
          data-proc-mode-id="${modeOfProcurementId || ''}"
          data-is-new="true"
          data-file-attachment=""
          data-row-key="${rowKey}"
        >
          <td>${programDescription}</td>
          <td>${categoryName}</td>
          <td>${quantity}<br>${specification}</td>
          <td class="text-center">${modeOfProcurement}</td>
          <td class="text-center">${preProcurementConference}</td>
          <td class="text-center">${procurementStartDate}</td>
          <td class="text-center">${biddingDate}</td>
          <td class="text-center">${contractSigningDate}</td>
          <td class="text-center">${sourceOfFunds}</td>
          <td class="text-end">${unitPriceText}</td>
          <td class="text-center">${fileBadge}</td>
          <td>${remarks}</td>
          <td class="text-center">
            <button type="button" class="btn btn-success btn-sm edit-item-btn" title="Edit Item">
              <i class="fa fa-edit"></i>
            </button>

            <button type="button" class="btn btn-danger btn-sm remove-new-btn" title="Remove Item">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      `);

      $totalRow.before($newRow);

      $('#UpdateForm')[0].reset();
      $('#existing_attachments_display').empty();
      resetDependentSelects();
      $('#mode_of_procurement').val('').trigger('change.select2');
      $('#source_of_funds').val('').trigger('change.select2');
      updateTotalDisplay();
    });

    $(document).on('click', '.remove-new-btn', function (e) {
      e.stopPropagation();
      const $row = $(this).closest('tr');
      const tempId = $row.attr('data-temp-item-id');
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
        if (!result.isConfirmed) return;
        if (tempId) delete tempAttachments[tempId];
        $row.remove();
        updateTotalDisplay();
        if (isEditing()) setAddMode();
      });
    });

    $(document).on('click', '#updateBtn', function (e) {
      e.preventDefault();

      console.log('UPDATE BUTTON CLICKED');
      console.log('editingRowEl:', editingRowEl);

      if (!editingRowEl) {
        Swal.fire("No Item Selected", "Please select an item to edit first.", "warning");
        return;
      }

      const $row = $(editingRowEl);

      if (!validateBudgetInput()) return;

      const programDescription = $('#item_description').val().trim();
      const categoryId = $('#category_id').val();
      const categoryName = $('#category_id option:selected').text();

      const subCategoryId = $('#sub_category_id').val();
      const itemNameId = $('#item_name_id').val();

      const specification = $('#specification').val().trim();
      const quantity = parseInt($('#quantity').val() || 0, 10);

      const modeOfProcurementId = $('#mode_of_procurement').val();
      const modeOfProcurement = $('#mode_of_procurement option:selected').text();

      const preProcurementConference = $('#pre_procurement_conference').val();
      const procurementStartDate = $('#procurement_start_date').val();
      const biddingDate = $('#bidding_date').val();
      const contractSigningDate = $('#contract_signing_date').val();

      const sourceOfFunds = $('#source_of_funds').val();
      const estimatedBudgetInput = $('#estimated_budget').val();
      const unitPrice = cleanCurrency(estimatedBudgetInput);
      const unitPriceText = unitPrice.toLocaleString('en-PH', {
        style: 'currency',
        currency: 'PHP'
      });

      const remarks = $('#remarks').val().trim();

      if (
        !programDescription ||
        !categoryId ||
        !subCategoryId ||
        !itemNameId ||
        !specification ||
        !modeOfProcurementId ||
        !preProcurementConference ||
        !procurementStartDate ||
        !biddingDate ||
        !contractSigningDate ||
        !sourceOfFunds ||
        quantity <= 0 ||
        unitPrice <= 0
      ) {
        Swal.fire("Missing Fields", "Please fill out all required fields before updating the item.", "warning");
        return;
      }

      if (!validateDateSequence()) {
        Swal.fire("Date Error", "Please correct the sequence of procurement activity dates.", "error");
        return;
      }

      $row.attr('data-category-id', categoryId);
      $row.attr('data-subcategory-id', subCategoryId);
      $row.attr('data-item-name-id', itemNameId);
      $row.attr('data-item-id', itemNameId);
      $row.attr('data-proc-mode-id', modeOfProcurementId);

      $row.find('td:eq(0)').text(programDescription);
      $row.find('td:eq(1)').text(categoryName);
      $row.find('td:eq(2)').html(`${quantity}<br>${specification}`);
      $row.find('td:eq(3)').text(modeOfProcurement);
      $row.find('td:eq(4)').text(preProcurementConference);
      $row.find('td:eq(5)').text(procurementStartDate);
      $row.find('td:eq(6)').text(biddingDate);
      $row.find('td:eq(7)').text(contractSigningDate);
      $row.find('td:eq(8)').text(sourceOfFunds);
      $row.find('td:eq(9)').text(unitPriceText);
      $row.find('td:eq(11)').text(remarks || '-');

      updateTotalDisplay();

      Swal.fire("Updated", "Item updated in the table.", "success");

      setAddMode();
    });

    $('#UpdateForm').on('submit', async function (e) {
      e.preventDefault();

      const showDraftButton = true;

      const submissionAction = await Swal.fire({
        title: "Choose Submission Type",
        text: "Do you want to save this PPMP as Draft or submit it as Final?",
        icon: "question",
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: "Final Submission",
        denyButtonText: "Save as Draft",
        cancelButtonText: "Cancel"
      });

      if (submissionAction.isDismissed) return;

      let actionType = submissionAction.isConfirmed ? "Final" : "Draft";
      if (submissionAction.isDenied) {
        actionType = "Draft";
      }

      if (actionType === "Final") {
        const remainingBudget = parseFloat($('#remaining_budget').val() || 0);
        let ppmpTotal = 0;

        $('#ppmpTable tbody tr:not(:last)').each(function () {
          const $row = $(this);
          const qtySpecHtml = $row.find('td:eq(2)').html() || '';
          const quantity = parseQuantityFromCellHtml(qtySpecHtml);
          const unitCostText = $row.find('td:eq(9)').text().trim();
          const cleanCost = cleanCurrency(unitCostText);
          ppmpTotal += (quantity * cleanCost);
        });

        const remainingAfterPPMP = remainingBudget - ppmpTotal;

        if (remainingAfterPPMP >= 500) {
          Swal.fire(
            "Budget Underutilized",
            `Your PPMP would leave ₱${remainingAfterPPMP.toLocaleString()} unspent. You must utilize your full budget (only ₱500 or less may remain).`,
            "warning"
          );
          return;
        }

        const firstPrompt = await Swal.fire({
          title: "Are you sure?",
          text: "Are you sure you want to submit this PPMP? Changes will no longer be allowed once finalized.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Continue",
          cancelButtonText: "Cancel"
        });

        if (!firstPrompt.isConfirmed) return;

        const secondPrompt = await Swal.fire({
          title: "Final Confirmation",
          text: "This action is final and cannot be undone. Proceed with submission?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Submit",
          cancelButtonText: "Cancel"
        });

        if (!secondPrompt.isConfirmed) return;
      }

      const $itemRows = $('#ppmpTable tbody tr:not(:last)');
      if ($itemRows.length === 0) {
        showSweetAlert("No Items", "Please add at least one PPMP item before submitting.", "warning");
        return;
      }

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
        ($('#file_attachment')[0].files && $('#file_attachment')[0].files.length > 0);

      if (hasUnsavedInputs) {
        showSweetAlert("Unsaved Item Detected", "You have filled out fields but didn’t add/update the item in the PPMP list. Please click 'Add to PPMP' / 'Update Item' first or clear the fields.", "warning");
        return;
      }

      const items = [];
      const fileFormData = new FormData();

      $itemRows.each(function () {
        const $row = $(this);

        const existingFileAttachment = $row.attr('data-file-attachment') || '';
        const isNew = $row.attr('data-is-new') === 'true';
        const tempItemId = $row.attr('data-temp-item-id') || $row.attr('data-row-key') || '';

        const qtySpecHtml = $row.find('td:eq(2)').html() || '';
        const quantity = parseQuantityFromCellHtml(qtySpecHtml);
        const specification = parseSpecFromCellHtml(qtySpecHtml);

        const unitCostText = $row.find('td:eq(9)').text().trim();
        const estimated_budget = cleanCurrency(unitCostText);
        const total_cost = estimated_budget * quantity;

        const itemObject = {
          item_id: $row.attr('data-item-id') || '',
          category_id: $row.attr('data-category-id') || '',
          sub_category_id: $row.attr('data-subcategory-id') || '',
          item_name_id: $row.attr('data-item-name-id') || $row.attr('data-item-id') || '',
          removed_file_attachment: $row.attr('data-removed-files') || '',

          item_description: $row.find('td:eq(0)').text().trim(),
          specifications: specification,
          quantity: quantity,

          mode_of_procurement_id: $row.attr('data-proc-mode-id') || '',
          mode_of_procurement: $row.find('td:eq(3)').text().trim(),
          pre_procurement_conference: $row.find('td:eq(4)').text().trim(),
          procurement_start_date: $row.find('td:eq(5)').text().trim(),
          bidding_date: $row.find('td:eq(6)').text().trim(),
          contract_signing_date: $row.find('td:eq(7)').text().trim(),
          source_of_funds: $row.find('td:eq(8)').text().trim(),

          estimated_budget: estimated_budget,
          total_cost: total_cost,
          remarks: $row.find('td:eq(11)').text().trim(),

          file_attachment: existingFileAttachment,

          temp_item_id: tempItemId,
          is_new: isNew
        };

        items.push(itemObject);

        const filesToUpload = tempAttachments[tempItemId] || [];

        if (tempItemId && filesToUpload.length > 0) {
          filesToUpload.forEach(function (file, index) {
            fileFormData.append(`file_${tempItemId}_${index}`, file);
          });
        }
      });

      const formData = new FormData();
      formData.append('action', 'UpdatePPMPForm');
      formData.append('user_id', $('#user_id').val());
      formData.append('remaining_budget', $('#remaining_budget').val());
      formData.append('ppmp_code', $('#ppmp_code').val());
      formData.append('ppmp_id', $('#ppmp_id').val());
      formData.append('is_final', actionType === 'Final' ? 1 : 0);
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
          $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');
        },
        dataType: 'json',
        success: function (response) {
          $('#submit_button').prop('disabled', false).html('<i class="fa fa-save"></i> Update');
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "ppmp.php");
          } else {
            showSweetAlert("Error", response.message || "Error updating PPMP.", "error");
          }
        },
        error: function (xhr) {
          $('#submit_button').prop('disabled', false).html('<i class="fa fa-save"></i> Update');
          console.error(xhr.responseText);
          showSweetAlert("Error", "Something went wrong during submission. Check console for details.", "error");
        }
      });
    });
  });
</script>