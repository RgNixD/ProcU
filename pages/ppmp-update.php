<?php
  require_once '../php/auth_check.php';
  if (!($canCreatePPMP)) {
    header("Location: 404.php");
    exit();
  }
  require_once 'sidebar.php';

  $ppmp_id = $_GET['ppmp_id'] ?? null;

  if (empty($ppmp_id) || !is_numeric($ppmp_id)) {
      header("Location: ppmp.php"); 
      exit();
  }

  $ppmp_header = $db->getPPMPHeaderDetails($ppmp_id);

  if (!$ppmp_header) {
      header("Location: ppmp.php"); 
      exit();
  }

  $ppmp_items_result = $db->getPPMPItemsById($ppmp_id);
  $ppmp_items = [];
  while ($row = $ppmp_items_result->fetch_assoc()) {
      $ppmp_items[] = $row;
  }

  $ppmp_code = htmlspecialchars($ppmp_header['ppmp_code'] ?? 'N/A');
  $fiscal_year = htmlspecialchars($ppmp_header['fiscal_year'] ?? 'N/A');
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

<!-- page content -->
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
              <input type="hidden" class="form-control" name="remaining_budget" id="remaining_budget" value="<?= $remainingBudget ?>">

              <div class="row">
                <div class="col-md-6 mb-3">
                  <h5 class="fw-bold">Project/Item Identification</h5>
                  <div class="form-group">
                    <label for="item_description" class="form-label fw-bold">Project Description</label>
                    <textarea class="form-control" name="item_description" id="item_description"
                      placeholder="Enter Project Description" rows="2"></textarea>
                  </div>
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
                  <div class="form-group">
                    <label for="sub_category_id" class="form-label fw-bold">Subcategory</label>
                    <select class="form-control form-select" name="sub_category_id" id="sub_category_id" disabled>
                      <option value="" disabled selected>Select Subcategory</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="item_name_id" class="form-label fw-bold">Item name</label>
                    <select class="form-control form-select" name="item_name_id" id="item_name_id" disabled>
                      <option value="" disabled selected>Select Item Name</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <h5 class="fw-bold">Item Details</h5>
                  <div class="form-group">
                    <label for="quantity" class="form-label fw-bold">Quantity</label>
                    <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1">
                  </div>
                  <div class="form-group">
                    <label for="specification" class="form-label fw-bold">Specification</label>
                    <textarea class="form-control" name="specification" id="specification"
                      placeholder="Enter other specification (e.g., color, material, brand requirement, size)"
                      rows="2"></textarea>
                  </div>

                  <h5 class="fw-bold mt-3">Procurement Details</h5>
                  <div class="form-group">
                    <label for="mode_of_procurement" class="form-label fw-bold">Mode of Procurement</label>
                    <select class="form-control form-select select2" name="mode_of_procurement"
                      id="mode_of_procurement">
                      <option value="" disabled selected>Select Mode of Procurement</option>
                      <option value="Competitive Bidding">Competitive Bidding</option>
                      <option value="Limited Source Bidding">Limited Source Bidding</option>
                      <option value="Competitive Dialogue">Competitive Dialogue</option>
                      <option value="Unsolicited Offer with Bid Matching">Unsolicited Offer with Bid Matching
                      </option>
                      <option value="Direct Contracting">Direct Contracting</option>
                      <option value="Direct Acquisition">Direct Acquisition</option>
                      <option value="Repeat Order">Repeat Order</option>
                      <option value="Small Value Procurement">Small Value Procurement</option>
                      <option value="Negotiated Procurement">Negotiated Procurement</option>
                      <option value="Direct Sales">Direct Sales</option>
                      <option value="Direct Procurement for Science, Technology, and Innovation">Direct Procurement
                        for Science, Technology, and Innovation</option>
                    </select>
                  </div>
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

                <div class="col-md-12">
                  <hr>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <h5 class="fw-bold">Activity Schedule</h5>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="procurement_start_date" class="form-label fw-bold">Start of Procurement
                          Activity</label>
                        <input type="date" class="form-control" name="procurement_start_date"
                          id="procurement_start_date" min="<?= date('Y-m-d') ?>">
                        <small id="procurement_start_error" class="text-danger"></small>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="bidding_date" class="form-label fw-bold">Bidding/Negotiation Date</label>
                        <input type="date" class="form-control" name="bidding_date" id="bidding_date"
                          min="<?= date('Y-m-d') ?>">
                        <small id="bidding_date_error" class="text-danger"></small>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="contract_signing_date" class="form-label fw-bold">Contract Signing/Award
                          Date</label>
                        <input type="date" class="form-control" name="contract_signing_date" id="contract_signing_date"
                          min="<?= date('Y-m-d') ?>">
                        <small id="contract_signing_error" class="text-danger"></small>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-12">
                  <hr>
                </div>

                <div class="col-md-6">
                  <h5 class="fw-bold">Financial and Other Details</h5>
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
                  <div class="form-group">
                    <label for="estimated_budget" class="form-label fw-bold">Estimated Budget (per item/project)</label>
                    <input type="number" class="form-control" name="estimated_budget" id="estimated_budget"
                      placeholder="Enter estimated budget (e.g., 150000.00)" min="0" step="0.01">
                  </div>
                </div>

                <div class="col-md-6">
                  <h5 class="fw-bold">Supporting Documentation and Final Remarks</h5>
                  <div class="form-group">
                    <label for="file_attachment" class="form-label fw-bold">Attachment (Image/Documents)</label>
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

                <div class="col-md-12 mt-3 d-flex justify-content-end">
                  <button type="button" class="btn btn-danger btn-sm me-2" id="clearBtn"><i
                      class="fa fa-times me-1"></i> Clear
                  </button>
                  <button type="button" class="btn btn-primary btn-sm" id="addBtn"><i class="fa fa-plus me-1"></i> Add
                    to PPMP
                  </button>
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
                      <th class="text-center align-middle">Type of the Project to be Procured (whether Goods,
                        Infrastructure and
                        Consulting Services)</th>
                      <th class="text-center align-middle">Quantity and Size of the Project to be Procured</th>
                      <th class="text-center align-middle">Recommended Mode of Procurement</th>
                      <th class="text-center align-middle">Pre-Procurement Conference, if applicable (Yes/No)</th>
                      <th class="text-center align-middle">Start of Procurement Activity</th>
                      <th class="text-center align-middle">End of Procurement Activity</th>
                      <th class="text-center align-middle">Expected Delivery/ Implementation Period</th>
                      <th class="text-center align-middle">Source of Funds</th>
                      <th class="text-center align-middle">Estimated Budget / Authorized Budgetary Allocation (PhP)</th>
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
<!-- /page content -->

<?php require_once 'footer.php'; ?>

<script>

  const PPMP_ITEMS_DATA = <?= json_encode($ppmp_items) ?>;
  const PPMP_ID = '<?= $ppmp_id ?>';

  function getQuarterFromDate(dateString) {
    if (!dateString) return 'N/A';
    const month = parseInt(dateString.substring(5, 7));
    if (month >= 1 && month <= 3) return 'Q1';
    if (month >= 4 && month <= 6) return 'Q2';
    if (month >= 7 && month <= 9) return 'Q3';
    if (month >= 10 && month <= 12) return 'Q4';
    return 'N/A';
  }

  function loadExistingItems(items) {
    const tbody = $("#ppmpTable tbody");
    tbody.empty(); 
    let grandTotal = 0;

    if (items.length === 0) {
        tbody.append('<tr><td colspan="12" class="text-center">No existing items found for this PPMP.</td></tr>');
        tbody.append(`
            <tr>
              <td colspan="9" class="text-end fw-bold">TOTAL BUDGET:</td>
              <td></td><td></td><td></td><td></td>
            </tr>
        `);
        return;
    }

    items.forEach((item, index) => {
        const totalCost = parseFloat(item.total_cost || 0);
        grandTotal += totalCost;

        const formattedTotalCost = totalCost.toLocaleString('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
        const formattedUnitCost = parseFloat(item.unit_cost || item.estimated_budget || 0).toLocaleString('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
        
        const itemData = JSON.stringify(item);

        const procStart = item.procurement_start_date || 'N/A';
        const biddingDate = item.bidding_date || 'N/A'; 
        const deliveryDate = item.contract_signing_date || 'N/A';

        const combinedItemDetails = `
            <b>Project:</b> ${item.item_description || 'N/A'}<br>
            <b>Sub Category:</b> ${item.sub_cat_name || 'N/A'}<br>
            <b>Item Name:</b> ${item.item_name || 'N/A'}
        `;
        const combinedQuantitySpec = `
            <b>Quantity</b>: ${item.quantity || 0} unit(s)<br>
            <b>Specs.</b>: ${item.specifications || 'N/A'}
        `;
        const categoryDetail = `
            <b>Category:</b> ${item.category_name || 'N/A'}}
        `;
        const combinedBudgetDetails = `
            <b>Cost price</b>: ${formattedUnitCost}<br>
            <b>Sub-total</b>: ${formattedTotalCost}
        `;
        
        const fileAttachmentDisplay = item.file_attachment ? 
                                      `<span class="badge bg-primary">Attached</span>` : 
                                      `<span class="badge bg-warning">None</span>`;

        const fileAttachmentString = item.file_attachment || '';

        const newRow = `
            <tr 
              data-item-id="${item.item_id}" 
              data-type="existing" 
              data-file-attachment="${fileAttachmentString}" 
              data-is-new="false"
              data-category-id="${item.category_id || ''}"             
              data-subcategory-id="${item.sub_category_id || ''}"       
              data-item-name-id="${item.item_name_id || ''}"
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
                    <button type="button" class="d-none btn btn-info btn-sm edit-item-btn" data-item='${itemData}' data-id="${item.item_id}" title="Edit Item">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm delete-item-btn" data-id="${item.item_id}" title="Delete Item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(newRow);
    });
    
    const formattedGrandTotal = grandTotal.toLocaleString('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 });
    tbody.append(`
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

    loadExistingItems(PPMP_ITEMS_DATA);

    $(document).on('click', '.edit-item-btn', function() {
      const itemData = $(this).data('item'); 
      console.log('Item Data:', itemData); 
      console.log('Specification Data:', itemData.specifications);
      $('#item_id_to_edit').remove(); 
      $('#UpdateForm').prepend(`<input type="hidden" name="item_id_to_edit" id="item_id_to_edit" value="${itemData.item_id}">`);
      
      if($('#addBtn').length){
          $('#addBtn')
              .text('Update Item')
              .removeClass('btn-primary')
              .addClass('btn-success')
              .attr('id', 'updateBtn')
              .prepend('<i class="fa fa-sync-alt me-1"></i> ');
      } else if ($('#updateBtn').length) {
          $('#updateBtn').text('Update Item').addClass('btn-success').prepend('<i class="fa fa-sync-alt me-1"></i> ');
      }

      $('#clearBtn').text('Cancel Edit').removeClass('btn-danger').addClass('btn-secondary');

      populateFormForEdit(itemData);
    });
    
    function convertDateFormat(dateString) {
        if (!dateString || dateString === '0000-00-00') return '';
        return dateString;
    }

    function populateFormForEdit(item) {
      const $attachmentDisplay = $('#existing_attachments_display');
      $attachmentDisplay.empty();

      if (item.file_attachment) {
          const fileNames = item.file_attachment.split(','); 

          fileNames.forEach(fileName => {
              const trimmedFileName = fileName.trim();
              if (trimmedFileName) {
                  const fullPath = `../assets/ppmp_attachments/${trimmedFileName}`; 
                  
                  const displayName = trimmedFileName.substring(trimmedFileName.lastIndexOf('/') + 1);

                  const fileLinkHtml = `
                      <span class="badge bg-success me-2 mb-1 d-inline-flex align-items-center">
                          <i class="fa fa-file me-1"></i> 
                          <a href="${fullPath}" target="_blank" class="text-white text-decoration-none" title="View Attachment">
                              ${displayName}
                          </a>
                      </span>
                  `;
                  $attachmentDisplay.append(fileLinkHtml);
              }
          });

      } else {
          $attachmentDisplay.html('<small class="text-muted">No files currently attached.</small>');
      }
      $('#UpdateForm')[0].reset();
      
      $('#item_description').val(item.item_description);
      $('#quantity').val(item.quantity || '1');
      $('#specification').val(item.specifications || '');
      $('#estimated_budget').val(parseFloat(item.unit_cost || item.estimated_budget || 0).toFixed(2)); 
      $('#remarks').val(item.remarks);
      
      $('#procurement_start_date').val(convertDateFormat(item.procurement_start_date));
      $('#bidding_date').val(convertDateFormat(item.bidding_date)); 
      $('#contract_signing_date').val(convertDateFormat(item.contract_signing_date)); 

      $('#pre_procurement_conference').val(item.pre_procurement_conference).trigger('change');
      
      const mode = item.procurement_method || item.mode_of_procurement;
      $('#mode_of_procurement').val(mode).trigger('change.select2');
      $('#source_of_funds').val(item.source_of_funds).trigger('change.select2');
      
      $('#category_id').val(item.category_id).trigger('change'); 

      const intervalSubCat = setInterval(function() {
          if (item.sub_category_id && $('#sub_category_id option[value="' + item.sub_category_id + '"]').length) {
              clearInterval(intervalSubCat);
              
              $('#sub_category_id').val(item.sub_category_id).trigger('change');

              const intervalItemName = setInterval(function() {
                  if (item.item_name_id && $('#item_name_id option[value="' + item.item_name_id + '"]').length) {
                      clearInterval(intervalItemName); 
                      
                      $('#item_name_id').val(item.item_name_id).trigger('change.select2');
                  }
              }, 50); 
          }
      }, 50); 
    }
    
    $(document).on('click', '.delete-item-btn', function() {
        const itemId = $(this).data('id');
        
        Swal.fire({
            title: "Confirm Deletion",
            text: "Are you sure you want to permanently remove this item from the PPMP? This requires a database action.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#a83232",
            cancelButtonColor: "#555555"
        }).then((result) => {
            if (result.isConfirmed) {
                
              $.ajax({
                  type: 'POST',
                  url: '../php/processes.php',
                  data: { action: 'DeleteItem', item_id: itemId },
                  success: function(response) {
                      if (response.success) {
                          $(`tr[data-item-id="${itemId}"]`).remove();
                          updateTotalDisplay();
                          // Swal.fire('Deleted!', 'The item has been deleted.', 'success');
                      } else {
                          Swal.fire('Error', response.message || 'Failed to delete item.', 'error');
                      }
                  }
              });
            }
        });
    });



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

    function getCurrentTotal() {
      let total = 0;
      $("#ppmpTable tbody tr:not(:last)").each(function () {
        const cellContent = $(this).find("td").eq(9).html();
        const match = cellContent.match(/Sub-total<\/b>:\s*([\S]+)/);

        if (match && match[1]) {
          const budgetText = match[1].replace(/[^0-9.]+/g, "");
          const budget = parseFloat(budgetText || 0);
          total += isNaN(budget) ? 0 : budget;
        }
      });
      return total;
    }

    function validateBudgetInput() {
      const quantity = parseInt($("#quantity").val() || 0);
      const unitPrice = parseFloat($("#estimated_budget").val() || 0);
      const totalItemBudget = quantity * unitPrice;

      const remainingBudget = parseFloat($("#remaining_budget").val() || 999999999);
      const currentTotal = getCurrentTotal();
      const projectedTotal = currentTotal + totalItemBudget;

      if (projectedTotal > remainingBudget) {
        if (typeof Swal !== 'undefined') {
          Swal.fire("Budget Limit Exceeded", `Your total projected PPMP cost (₱${projectedTotal.toLocaleString('en-PH', { minimumFractionDigits: 2 })}) exceeds your remaining budget of ₱${remainingBudget.toLocaleString('en-PH', { minimumFractionDigits: 2 })}.`, "error");
          $("#estimated_budget").val() = '';
        } else {
          Swal.fire("Budget Limit Exceeded", `Budget Limit Exceeded! Projected total: ₱${projectedTotal.toLocaleString()} > Remaining budget: ₱${remainingBudget.toLocaleString()}`, "error");
        }
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

    let tempAttachments = {};
    let nextItemId = 0;

    document.getElementById("addBtn").addEventListener("click", function () {
      const programDescription = document.getElementById("item_description").value.trim();
      const categoryId = document.getElementById("category_id").value;
      const categoryName = $('#category_id option:selected').text();
      const subCategoryId = document.getElementById("sub_category_id").value;
      const subCategoryName = $('#sub_category_id option:selected').text() === 'Select Subcategory' ? '' : $('#sub_category_id option:selected').text();
      const itemName = $('#item_name_id option:selected').text() === 'Select Item Name' ? '' : $('#item_name_id option:selected').text();
      const item_name_id_to_add = document.getElementById("item_name_id").value;

      const specification = document.getElementById("specification").value.trim();
      const quantity = parseInt(document.getElementById("quantity").value.trim() || 0); // Ensure integer parsing
      const modeOfProcurement = $('#mode_of_procurement option:selected').text() === 'Select Mode of Procurement' ? '' : $('#mode_of_procurement option:selected').text();
      const preProcurementConference = document.getElementById("pre_procurement_conference").value.trim();
      const procurementStartDate = document.getElementById("procurement_start_date").value.trim();
      const biddingDate = document.getElementById("bidding_date").value.trim();
      const contractSigningDate = document.getElementById("contract_signing_date").value.trim();
      const sourceOfFunds = $('#source_of_funds option:selected').text() === 'Select Source of Funds' ? '' : $('#source_of_funds option:selected').text();

      const unitPrice = parseFloat(document.getElementById("estimated_budget").value || 0);
      const unitPriceText = unitPrice.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

      const totalItemBudget = quantity * unitPrice;
      const totalItemBudgetText = totalItemBudget.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

      const remarks = document.getElementById("remarks").value.trim();

      const combinedQuantitySpec = `<b>Quantity</b>: ${quantity} unit(s)<br><b>Specs.</b>: ${specification}`;

      const combinedItemDetails = `<b>Project:</b> ${programDescription}<br><b>Sub Category:</b> ${subCategoryName || 'N/A'}<br><b>Item Name:</b> ${itemName || 'N/A'}`;

      const categoryDetail = `<b>Category:</b> ${categoryName}`;

      const combinedBudgetDetails = `<b>Cost price</b>: ${unitPriceText}<br><b>Sub-total</b>: ${totalItemBudgetText}`;

      if (totalItemBudget <= 0 || quantity <= 0 || !programDescription || !categoryName || !itemName || !specification || !modeOfProcurement || !preProcurementConference || !procurementStartDate || !biddingDate || !contractSigningDate || !sourceOfFunds) {
        if (typeof Swal !== 'undefined') {
          Swal.fire("Missing Fields", "Please fill out all required fields (Project, Item, Quantity, Specification, Mode, Dates, Source of Funds, and a valid Unit Price) before adding to the table.", "warning");
        }
        return;
      }

      let isDuplicate = false;
      $("#ppmpTable tbody tr:not(:last)").each(function () {
        const itemTypeCellContent = $(this).find("td").eq(0).html();
        if (itemTypeCellContent.includes(`Item Name:</b> ${itemName}`)) {
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

      const currentItemId = nextItemId++;
      tempAttachments[currentItemId] = files;

      const tableBody = document.querySelector("#ppmpTable tbody");
      const newRow = tableBody.insertRow(tableBody.rows.length - 1);

      newRow.setAttribute('data-temp-item-id', currentItemId);
      newRow.setAttribute('data-category-id', categoryId);
      newRow.setAttribute('data-subcategory-id', subCategoryId);
      newRow.setAttribute('data-item-id', item_name_id_to_add);
      newRow.setAttribute('data-is-new', 'true');
      newRow.setAttribute('data-file-attachment', '');

      newRow.insertCell(0).innerHTML = combinedItemDetails;

      newRow.insertCell(1).innerHTML = categoryDetail;

      newRow.insertCell(2).innerHTML = combinedQuantitySpec;

      newRow.insertCell(3).innerText = modeOfProcurement;

      newRow.insertCell(4).innerText = preProcurementConference;

      newRow.insertCell(5).innerText = procurementStartDate;

      newRow.insertCell(6).innerText = biddingDate;

      newRow.insertCell(7).innerText = contractSigningDate;

      newRow.insertCell(8).innerText = sourceOfFunds;

      newRow.insertCell(9).innerHTML = combinedBudgetDetails;

      const attachmentCell = newRow.insertCell(10);
      if (files.length > 0) {
        attachmentCell.innerHTML = `<span class="badge bg-success">${files.length} File(s) Attached</span>`;
      } else {
        attachmentCell.innerHTML = `<span class="badge bg-warning">None</span>`;
      }

      newRow.insertCell(11).innerText = remarks;

      const actionCell = newRow.insertCell(12);
      actionCell.classList.add("text-center");
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
            delete tempAttachments[currentItemId];
            newRow.remove();
            updateTotalDisplay();
            // Swal.fire("Removed!", "The item has been removed.", "success");
          }
        });
      });
      actionCell.appendChild(removeBtn);

      $('#UpdateForm')[0].reset();
      updateTotalDisplay();

      $('#sub_category_id').empty().append('<option value="" disabled selected>Select Subcategory</option>').prop('disabled', true).trigger('change');
      $('#item_name_id').empty().append('<option value="" disabled selected>Select Item Name</option>').prop('disabled', true).trigger('change');
      $('#mode_of_procurement').append('<option value="" disabled selected>Select Mode of Procurement</option>').trigger('change');
      $('#source_of_funds').append('<option value="" disabled selected>Select Source of Funds</option>').trigger('change');

      // Swal.fire("Item Added", "The procurement item has been added to the list.", "success");
    });

    $('#clearBtn').on('click', function () {
      const form = $('#UpdateForm')[0];
      form.reset();
      $('#sub_category_id').empty().append('<option value="" disabled selected>Select Subcategory</option>').prop('disabled', true).trigger('change');
      $('#item_name_id').empty().append('<option value="" disabled selected>Select Item Name</option>').prop('disabled', true).trigger('change');
      $('#mode_of_procurement').append('<option value="" disabled selected>Select Mode of Procurement</option>').trigger('change');
      $('#source_of_funds').append('<option value="" disabled selected>Select Source of Funds</option>').trigger('change');
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
        error: function (xhr, status, error) {
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

          $itemSelect.select2({
            width: '100%',
          });

          // $('#item_description').val('');
          if ($('#item_id_to_edit').length === 0) {
             $('#specification').val('');
        }
          $('#quantity').val('1');
          $('#unit_cost').val('');
        },
        error: function (xhr, status, error) {
          console.error('Error fetching item names:', xhr.responseText);
          Swal.fire("Error", "Could not load item names. Check console for details.", "error");

          $itemSelect.select2({
            width: '100%',
            placeholder: 'Select Item Name'
          });
        }
      });
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();

      const items = [];
      let allFiles = [];

      const $itemRows = $('#ppmpTable tbody tr:not(:last)');

      if ($itemRows.length === 0) {
        showSweetAlert("No Items", "Please add at least one PPMP item before submitting.", "warning");
        return;
      }

      const fileFormData = new FormData();

      $itemRows.each(function () {
        const row = $(this);

        const existingFileAttachment = row.attr('data-file-attachment') || '';
        const isNew = row.attr('data-is-new') === 'true';

        const tempItemId = row.attr('data-temp-item-id');
        const categoryId = row.attr('data-category-id');
        const subCategoryId = row.attr('data-subcategory-id');
        const itemNameId = row.attr('data-item-name-id') || row.attr('data-item-id');
    

        const itemDetailsHTML = row.find('td:eq(0)').html();

        const programDescription = (itemDetailsHTML.match(/<b>Project:<\/b>([^<]+)/) || [])[1].trim();
        const subCategoryName = (itemDetailsHTML.match(/<b>Sub Category:<\/b>([^<]+)/) || [])[1].trim();
        const itemName = (itemDetailsHTML.match(/<b>Item Name:<\/b>([^<]+)/) || [])[1].trim();

        const categoryDetailHTML = row.find('td:eq(1)').html();
        const categoryName = (categoryDetailHTML.match(/<b>Category:<\/b>([^<]+)/) || [])[1].trim();

        const quantitySpecHTML = row.find('td:eq(2)').html();
        const quantityText = (quantitySpecHTML.match(/<b>Quantity<\/b>:\s*(\d+)/) || [])[1];
        const quantity = parseInt(quantityText || 0);
        const specification = (quantitySpecHTML.match(/<b>Specs\.<\/b>:\s*(.*)/) || [])[1]; // Correct index is 2

        const budgetDetailsHTML = row.find('td:eq(9)').html();
        const unitCostText = (budgetDetailsHTML.match(/<b>Cost price<\/b>:\s*([\S]+)/) || [])[1];
        const totalCostText = (budgetDetailsHTML.match(/<b>Sub-total<\/b>:\s*([\S]+)/) || [])[1];

        const cleanCurrency = (text) => parseFloat(text.replace(/[^0-9.]+/g, "") || 0);

        const itemObject = {
          item_id: row.attr('data-item-id'),
          category_id: categoryId,
          sub_category_id: subCategoryId,
          item_name_id: itemNameId,
          
          item_description: programDescription,
          specifications: specification,
          quantity: quantity,

          mode_of_procurement: row.find('td:eq(3)').text().trim(),
          pre_procurement_conference: row.find('td:eq(4)').text().trim(),
          procurement_start_date: row.find('td:eq(5)').text().trim(),
          bidding_date: row.find('td:eq(6)').text().trim(),
          contract_signing_date: row.find('td:eq(7)').text().trim(),
          source_of_funds: row.find('td:eq(8)').text().trim(),

          estimated_budget: cleanCurrency(unitCostText),
          total_cost: cleanCurrency(totalCostText),
          remarks: row.find('td:eq(11)').text().trim(),

          category_name: categoryName,
          sub_category_name: subCategoryName,
          file_attachment: existingFileAttachment, 
        is_new: isNew,
        };
        items.push(itemObject);

       if (isNew && tempItemId) {
        const filesToUpload = tempAttachments[tempItemId] || [];

        filesToUpload.forEach(function (file, index) {
            fileFormData.append(`file_${tempItemId}_${index}`, file);
        });
    }
      });

      const hasUnsavedInputs =
        $('#category_id').val() ||
        $('#sub_category_id').val() ||
        $('#item_name_id').val() ||
        $('#item_description').val().trim() ||
        $('#specification').val().trim() ||
        (parseInt($('#quantity').val() || 1) !== 1) ||
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
      formData.append('ppmp_code', $('#ppmp_code').val());
      formData.append('ppmp_id', $('#ppmp_id').val());

      formData.append('action', 'UpdatePPMPForm');

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

        success: function (response) {
          $('#submit_button').prop('disabled', false).html('<i class="fa fa-save"></i> Submit');

          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "ppmp.php");
          } else {
            showSweetAlert("Error", response.message, "error");
          }
        },
        error: function (xhr) {
          $('#submit_button').prop('disabled', false).html('<i class="fa fa-save"></i> Submit');
          console.error(xhr.responseText);
          showSweetAlert("Error", "Something went wrong during submission. Check console for details.", "error");
        }
      });
    });

  });

</script>