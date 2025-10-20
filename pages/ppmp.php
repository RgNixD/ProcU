<?php
  require_once '../php/auth_check.php';
  if (!($canCreatePPMP)) {
      header("Location: 404.php");
      exit();
  }
  require_once 'sidebar.php';
?>

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
        <h2>PPMP List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <!-- ADD MODAL -->
        <div class="modal fade" id="AddNew" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <form id="SaveForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">New PPMP</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="user_id" id="user_id" value="<?= $userId ?>">
                  <input type="hidden" class="form-control" name="remaining_budget" id="remaining_budget" value="<?= $remainingBudget ?>">
                    <div class="row">
                      <div class="col-md-6 mb-3">
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
                          <label for="item" class="form-label fw-bold">Item name</label>
                          <input type="text" class="form-control" name="item" id="item" placeholder="Enter item e.g., Laptop, Printer">
                        </div>
                        <div class="form-group">
                          <label for="item_description" class="form-label fw-bold">Description</label>
                          <textarea class="form-control" name="item_description" id="item_description" placeholder="Enter description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                          <label for="specification" class="form-label fw-bold">Specification</label>
                          <input type="text" class="form-control" name="specification" id="specification" placeholder="Enter specification e.g., 16GB RAM, 512GB SSD">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="quantity" class="form-label fw-bold">Quantity</label>
                          <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1">
                        </div>
                        <div class="form-group">
                          <label for="unit_of_measure" class="form-label fw-bold">Unit measure</label>
                          <input type="text" class="form-control" name="unit_of_measure" id="unit_of_measure" placeholder="e.g., pcs, box, set">
                        </div>
                        <div class="form-group">
                          <label for="unit_cost" class="form-label fw-bold">Unit Cost</label>
                          <input type="number" class="form-control" name="unit_cost" id="unit_cost" placeholder="Enter unit cost e.g., 150.00">
                        </div>
                        <div class="form-group">
                          <label for="quarter_needed" class="form-label fw-bold">Quarter needed</label>
                          <select name="quarter_needed" id="quarter_needed" class="form-control">
                            <option value=""> Select Quarter</option>
                            <option value="Q1">Q1</option>
                            <option value="Q2">Q2</option>
                            <option value="Q3">Q3</option>
                            <option value="Q4">Q4</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="justification" class="form-label fw-bold">Justification</label>
                          <textarea class="form-control" name="justification" id="justification" rows="4" placeholder="Enter justification"></textarea>
                        </div>
                       <div class="form-group mt-3 d-flex justify-content-end gap-2">
                          <button type="button" class="btn btn-danger btn-sm me-2" id="clearBtn">
                            <i class="fa fa-times me-1"></i> Clear
                          </button>
                          <button type="button" class="btn btn-primary btn-sm" id="addBtn">
                            <i class="fa fa-plus me-1"></i> Add to PPMP
                          </button>
                        </div>
                      </div>
                    </div>
                   
                    <h6 class="text-white p-2 mb-0" style="background-color: #a83232;">CURRENT ITEMS</h6>
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped" id="ppmpTable">
                        <thead class="table-danger text-center">
                          <tr>
                            <th>CATEGORY</th>
                            <th>SUBCATEGORY</th>
                            <th>ITEM</th>
                            <th>DESCRIPTION</th>
                            <th>SPECIFICATION</th>
                            <th>QUANTITY</th>
                            <th>UNIT</th>
                            <th>UNIT COST</th>
                            <th>TOTAL COST</th>
                            <th>QUARTER NEEDED</th>
                            <th>JUSTIFICATION</th>
                            <th>ACTION</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                  </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                  <i class="fa fa-times me-1"></i>  Cancel
                  </button>
                  <button type="submit" class="btn btn-primary btn-sm" id="submit_button">
                    <i class="fa fa-save"></i> Submit
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- UPDATE MODAL -->
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <form id="UpdateForm" autocomplete="off" enctype="multipart/form-data">
              <div class="modal-content">
                <div class="modal-header bg-light">
                  <h5 class="modal-title" id="exampleModalLabel">Update PPMP</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  </button>
                </div>
                <div class="modal-body">
                  <input type="hidden" class="form-control" name="ppmp_id" id="edit_ppmp_id" required>
                  <input type="hidden" class="form-control" name="user_id" id="edit_user_id" value="<?= $userId ?>">
                  <input type="hidden" class="form-control" name="remaining_budget" id="edit_remaining_budget" value="<?= $remainingBudget ?>">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <div class="form-group">
                          <label for="edit_category_id" class="form-label fw-bold">Category</label>
                          <select class="form-control form-select" name="category_id" id="edit_category_id">
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
                          <label for="edit_sub_category_id" class="form-label fw-bold">Subcategory</label>
                          <select class="form-control form-select" name="sub_category_id" id="edit_sub_category_id" disabled>
                            <option value="" disabled selected>Select Subcategory</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="edit_item" class="form-label fw-bold">Item name</label>
                          <input type="text" class="form-control" name="item" id="edit_item" placeholder="Enter item e.g., Laptop, Printer">
                        </div>
                        <div class="form-group">
                          <label for="edit_item_description" class="form-label fw-bold">Description</label>
                          <textarea class="form-control" name="item_description" id="edit_item_description" placeholder="Enter description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                          <label for="edit_specification" class="form-label fw-bold">Specification</label>
                          <input type="text" class="form-control" name="specification" id="edit_specification" placeholder="Enter specification e.g., 16GB RAM, 512GB SSD">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="edit_quantity" class="form-label fw-bold">Quantity</label>
                          <input type="number" class="form-control" name="quantity" id="edit_quantity" value="1" min="1">
                        </div>
                        <div class="form-group">
                          <label for="edit_unit_of_measure" class="form-label fw-bold">Unit measure</label>
                          <input type="text" class="form-control" name="unit_of_measure" id="edit_unit_of_measure" placeholder="e.g., pcs, box, set">
                        </div>
                        <div class="form-group">
                          <label for="edit_unit_cost" class="form-label fw-bold">Unit Cost</label>
                          <input type="number" class="form-control" name="unit_cost" id="edit_unit_cost" placeholder="Enter unit cost e.g., 150.00">
                        </div>
                        <div class="form-group">
                          <label for="edit_quarter_needed" class="form-label fw-bold">Quarter needed</label>
                          <select name="quarter_needed" id="edit_quarter_needed" class="form-control">
                            <option value=""> Select Quarter</option>
                            <option value="Q1">Q1</option>
                            <option value="Q2">Q2</option>
                            <option value="Q3">Q3</option>
                            <option value="Q4">Q4</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="edit_justification" class="form-label fw-bold">Justification</label>
                          <textarea class="form-control" name="justification" id="edit_justification" rows="4" placeholder="Enter justification"></textarea>
                        </div>
                       <div class="form-group mt-3 d-flex justify-content-end gap-2">
                          <button type="button" class="btn btn-danger btn-sm me-2" id="edit_clearBtn">
                            <i class="fa fa-times me-1"></i> Clear
                          </button>
                          <button type="button" class="btn btn-primary btn-sm" id="edit_addBtn">
                            <i class="fa fa-plus me-1"></i> Add to PPMP
                          </button>
                        </div>
                      </div>
                    </div>
                   
                    <h6 class="text-white p-2 mb-0" style="background-color: #a83232;">CURRENT ITEMS</h6>
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped" id="edit_ppmpTable">
                        <thead class="table-danger text-center">
                          <tr>
                            <th>CATEGORY</th>
                            <th>SUBCATEGORY</th>
                            <th>ITEM</th>
                            <th>DESCRIPTION</th>
                            <th>SPECIFICATION</th>
                            <th>QUANTITY</th>
                            <th>UNIT</th>
                            <th>UNIT COST</th>
                            <th>TOTAL COST</th>
                            <th>QUARTER NEEDED</th>
                            <th>JUSTIFICATION</th>
                            <th>ACTION</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                      class="fa fa-times"></i> Cancel</button>
                  <button type="submit" class="btn btn-success btn-sm" id="edit_submit_button"><i
                      class="fa fa-edit"></i> Update</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        
        <!-- VIEW MODAL -->
        <div class="modal fade" id="ViewPPMPModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">PPMP Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                </button>
              </div>
              <div class="modal-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="ppmpItemsTable">
                    <thead class="table-danger text-center">
                      <tr>
                        <th>CATEGORY</th>
                        <th>SUBCATEGORY</th>
                        <th>ITEM NAME</th>
                        <th>DESCRIPTION</th>
                        <th>SPECIFICATIONS</th>
                        <th>QUANTITY</th>
                        <th>UNIT</th>
                        <th>UNIT COST</th>
                        <th>TOTAL COST</th>
                        <th>QUARTER</th>
                        <th>PROC. METHOD</th>
                        <th>JUSTIFICATION</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-box bg-white table-responsive pb-2">
          <button data-toggle="modal" data-target="#AddNew" class="btn btn-sm btn-primary mb-3 ml-3 mt-1" title="Create PPMP"><i class="fa fa-plus"></i> </button>
          <button id="delete-selected" class="btn btn-sm btn-danger mb-3 mt-1" title="Delete PPMP"><i class="fa fa-trash"></i></button>
          <table id="datatable" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" class="d-block m-auto"></th>
                <th>PPMP CODE</th>
                <th>OFFICE</th>
                <th class="text-center">FISCAL YEAR</th>
                <?php if ($canApprovePPMP && $canViewReports): ?>
                <th>SUBMITTED BY</th>
                <?php endif; ?>
                <th>TOTAL AMOUNT</th>
                <th>SUBMISSION DATE</th>
                <th class="text-center">STATUS</th>
                <th class="text-center">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $ppmpRecords = $db->getAllPPMPRecords($userId);
              while ($row = $ppmpRecords->fetch_assoc()) {
                $fullName = ucwords($row['first_name'] . ' ' . $row['last_name']);
              ?>
                <tr>
                  <td><input type="checkbox" class="select-record d-block m-auto" name="record_<?= $row['ppmp_id'] ?>" id="record_<?= $row['ppmp_id'] ?>" value="<?= $row['ppmp_id'] ?>"></td>
                  <td><?= htmlspecialchars($row['ppmp_code']); ?></td>
                  <td><?= htmlspecialchars($row['office_name']); ?></td>
                  <td class="text-center"><?= htmlspecialchars($row['fiscal_year']); ?></td>
                  <?php if ($canApprovePPMP && $canViewReports): ?>
                  <td><?= htmlspecialchars($fullName); ?></td>
                  <?php endif; ?>
                  <td>₱<?= number_format($row['total_amount'], 2); ?></td>
                  <td><?= date("F d, Y", strtotime($row['submission_date'])); ?></td>
                  <td class="text-center">
                    <?php if ($row['status'] == 'Pending'): ?>
                      <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif ($row['status'] == 'Approved'): ?>
                      <span class="badge bg-success">Approved</span>
                    <?php elseif ($row['status'] == 'Rejected'): ?>
                      <span class="badge bg-danger">Rejected</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($row['status']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <button 
                      class="btn btn-primary btn-sm view-ppmp"
                      data-ppmp-id="<?= $row['ppmp_id']; ?>"
                      title="View Details">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button 
                      class="btn btn-success btn-sm edit-ppmp"
                      data-ppmp-id="<?= $row['ppmp_id']; ?>"
                      title="Edit PPMP">
                      <i class="fa fa-edit"></i>
                    </button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /page content -->

<?php require_once 'footer.php'; ?>

<script>

  $(document).ready(function () {

  // ADD PROCESS
    function getCurrentTotal() {
      let total = 0;
      $("#ppmpTable tbody tr").each(function() {
        const cost = parseFloat($(this).find("td").eq(8).text() || 0);
        total += isNaN(cost) ? 0 : cost;
      });
      return total;
    }

    $("#unit_cost, #quantity").on("input", validateBudgetInput);

    function validateBudgetInput() {
      const quantity = parseFloat($("#quantity").val() || 0);
      const unitCost = parseFloat($("#unit_cost").val() || 0);
      const totalCost = quantity * unitCost;

      const remainingBudget = parseFloat($("#remaining_budget").val() || 0);
      const currentTotal = getCurrentTotal();
      const projectedTotal = currentTotal + totalCost;

      if (projectedTotal > remainingBudget) {
        showSweetAlert("Budget Limit Exceeded", `Your total projected PPMP cost (₱${projectedTotal.toLocaleString()}) exceeds your remaining budget of ₱${remainingBudget.toLocaleString()}.`, "error");

        $("#quantity").val("");
        $("#unit_cost").val("");
        return false;
      }

      return true;
    }

    document.getElementById("addBtn").addEventListener("click", function() {
      const categorySelect = document.getElementById("category_id");
      const categoryId = categorySelect.value;
      const categoryName = categorySelect.options[categorySelect.selectedIndex]?.text || "";

      const subCategorySelect = document.getElementById("sub_category_id");
      const subCategoryId = subCategorySelect.value;
      const subCategoryName = subCategorySelect.options[subCategorySelect.selectedIndex]?.text || "";

      const item = document.getElementById("item").value.trim();
      const itemDescription = document.getElementById("item_description").value.trim();
      const specification = document.getElementById("specification").value.trim();
      const quantity = document.getElementById("quantity").value.trim();
      const unitOfMeasure = document.getElementById("unit_of_measure").value.trim();
      const unitCost = document.getElementById("unit_cost").value.trim();
      const quarterNeeded = document.getElementById("quarter_needed").value.trim();
      const justification = document.getElementById("justification").value.trim();

      if (!categoryId || !subCategoryId || !item || !itemDescription || !specification || !quantity || !unitOfMeasure || !unitCost || !quarterNeeded || !justification) {
        Swal.fire("Missing Fields", "Please fill out all fields before adding to table.", "warning");
        return;
      }

      const totalCost = (parseFloat(unitCost) * parseInt(quantity)).toFixed(2);
      const remainingBudget = parseFloat(document.getElementById("remaining_budget").value || 0);
      const currentTotal = getCurrentTotal();
      const projectedTotal = currentTotal + parseFloat(totalCost);

      if (projectedTotal > remainingBudget) {
        Swal.fire("Budget Limit Exceeded", `Cannot add this item. Total PPMP cost (₱${projectedTotal.toLocaleString()}) exceeds your remaining budget of ₱${remainingBudget.toLocaleString()}.`, "error");
        return;
      }

      const table = document.getElementById("ppmpTable").querySelector("tbody");
      const rows = table.getElementsByTagName("tr");

      for (let i = 0; i < rows.length; i++) {
        const existingCategory = rows[i].cells[0].innerText.trim();
        const existingSubCategory = rows[i].cells[1].innerText.trim();
        const existingItem = rows[i].cells[2].innerText.trim();
        if (existingCategory === categoryName && existingSubCategory === subCategoryName && existingItem === item) {
          Swal.fire("Duplicate Entry", `The combination of "${categoryName}", "${subCategoryName}" and item "${item}" has already been added.`, "error");
          return;
        }
      }

      const row = table.insertRow();
      row.insertCell(0).innerText = categoryName;
      row.setAttribute("data-category-id", categoryId);

      row.insertCell(1).innerText = subCategoryName;
      row.setAttribute("data-subcategory-id", subCategoryId);

      row.insertCell(2).innerText = item;
      row.insertCell(3).innerText = itemDescription;
      row.insertCell(4).innerText = specification;
      row.insertCell(5).innerText = quantity;
      row.insertCell(6).innerText = unitOfMeasure;
      row.insertCell(7).innerText = unitCost;
      row.insertCell(8).innerText = totalCost;
      row.insertCell(9).innerText = quarterNeeded;
      row.insertCell(10).innerText = justification;

      const actionCell = row.insertCell(11);

      actionCell.classList.add("text-center");
      const removeBtn = document.createElement("button");
      removeBtn.className = "btn btn-sm btn-danger";
      removeBtn.innerHTML = '<i class="fa fa-trash"></i>';
      removeBtn.title = "Remove";
      removeBtn.type = "button";
      removeBtn.addEventListener("click", function() {
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
            row.remove();
          }
        });
      });
      actionCell.appendChild(removeBtn);

      const form = document.getElementById("SaveForm");
      form.reset();

      const subCatSelect = $('#sub_category_id');
      subCatSelect.html('<option value="" disabled selected>Select Subcategory</option>');
      subCatSelect.prop('disabled', true);
      subCatSelect.val(null).trigger('change.select2');
    });

    $('#clearBtn').on('click', function() {
      const form = $('#SaveForm')[0];
      form.reset();

      const subCatSelect = $('#sub_category_id');
      subCatSelect.html('<option value="" disabled selected>Select Subcategory</option>');
      subCatSelect.prop('disabled', true);
      subCatSelect.val(null).trigger('change.select2');
    });

    $('#sub_category_id').select2({
      dropdownParent: $('#AddNew'),
      width: '100%',
      placeholder: 'Select Subcategory'
    });
    
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

    $('#SaveForm').submit(function (e) {
      e.preventDefault();

      const items = [];
      $('#ppmpTable tbody tr').each(function() {
        const row = $(this);
        items.push({
          category_id: row.data('category-id'),
          sub_category_id: row.data('subcategory-id'),
          category_name: row.find('td:eq(0)').text().trim(),
          sub_category_name: row.find('td:eq(1)').text().trim(),
          item_name: row.find('td:eq(2)').text().trim(),
          item_description: row.find('td:eq(3)').text().trim(),
          specification: row.find('td:eq(4)').text().trim(),
          quantity: row.find('td:eq(5)').text().trim(),
          unit_of_measure: row.find('td:eq(6)').text().trim(),
          unit_cost: row.find('td:eq(7)').text().trim(),
          total_cost: row.find('td:eq(8)').text().trim(),
          quarter_needed: row.find('td:eq(9)').text().trim(),
          justification: row.find('td:eq(10)').text().trim()
        });
      });

      const hasUnsavedInputs =
        $('#category_id').val() ||
        $('#sub_category_id').val() ||
        $('#item').val().trim() ||
        $('#item_description').val().trim() ||
        $('#specification').val().trim() ||
        $('#quantity').val().trim() !== "" && $('#quantity').val() !== "1" ||
        $('#unit_of_measure').val().trim() ||
        $('#unit_cost').val().trim() ||
        $('#quarter_needed').val() ||
        $('#justification').val().trim();

      if (hasUnsavedInputs) {
        showSweetAlert("Unsaved Item Detected", "You have filled out fields but didn’t add the item to the PPMP list. Please click 'Add to PPMP' first or clear the fields.", "warning"); 
        return;
      }

      if (items.length === 0) {
        showSweetAlert("No Items", "Please add at least one PPMP item before submitting.", "warning"); 
        return;
      }

      const formData = new FormData(this);
      formData.append('action', 'AddPPMPForm');
      formData.append('ppmp_items', JSON.stringify(items));

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "ppmp.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
          
          // $('#AddNew').modal('hide');
          // $('#SaveForm')[0].reset();
          // $('#ppmpTable tbody').empty();
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          showSweetAlert("Error", "Something went wrong during submission.", "error");
        }
      });
    });

  // END ADD PROCESS

  
  // UPDATE PROCESS
    function getEditCurrentTotal() {
      let total = 0;
      $('#edit_ppmpTable tbody tr').each(function () {
        const cost = parseFloat($(this).find('td').eq(8).text() || 0);
        total += isNaN(cost) ? 0 : cost;
      });
      return total;
    }

    $('#edit_unit_cost, #edit_quantity').on('input', validateEditBudgetInput);

    function validateEditBudgetInput() {
      const quantity = parseFloat($('#edit_quantity').val() || 0);
      const unitCost = parseFloat($('#edit_unit_cost').val() || 0);
      const totalCost = quantity * unitCost;
      const remainingBudget = parseFloat($('#edit_remaining_budget').val() || 0);
      const currentTotal = getEditCurrentTotal();

      const projectedTotal = currentTotal + totalCost;

      if (projectedTotal > remainingBudget) {
        showSweetAlert("Budget Limit Exceeded", `Your total projected PPMP cost (₱${projectedTotal.toLocaleString()}) exceeds your remaining budget of ₱${remainingBudget.toLocaleString()}.`, "error");

        $('#edit_quantity').val('');
        $('#edit_unit_cost').val('');
        return false;
      }
      return true;
    }
    
    document.getElementById("edit_addBtn").addEventListener("click", function () {
      const categorySelect = document.getElementById("edit_category_id");
      const categoryId = categorySelect.value;
      const categoryName = categorySelect.options[categorySelect.selectedIndex]?.text || "";

      const subCategorySelect = document.getElementById("edit_sub_category_id");
      const subCategoryId = subCategorySelect.value;
      const subCategoryName = subCategorySelect.options[subCategorySelect.selectedIndex]?.text || "";

      const item = document.getElementById("edit_item").value.trim();
      const itemDescription = document.getElementById("edit_item_description").value.trim();
      const specification = document.getElementById("edit_specification").value.trim();
      const quantity = document.getElementById("edit_quantity").value.trim();
      const unitOfMeasure = document.getElementById("edit_unit_of_measure").value.trim();
      const unitCost = document.getElementById("edit_unit_cost").value.trim();
      const quarterNeeded = document.getElementById("edit_quarter_needed").value.trim();
      const justification = document.getElementById("edit_justification").value.trim();

      if (
        !categoryId || !subCategoryId || !item || !itemDescription || !specification ||
        !quantity || !unitOfMeasure || !unitCost || !quarterNeeded || !justification
      ) {
        showSweetAlert("Missing Fields", "Please fill out all fields before adding to table.", "warning");
        return;
      }

      const totalCost = (parseFloat(unitCost) * parseInt(quantity)).toFixed(2);
      const remainingBudget = parseFloat(document.getElementById("edit_remaining_budget").value || 0);
      const currentTotal = getEditCurrentTotal();
      const projectedTotal = currentTotal + parseFloat(totalCost);

      if (projectedTotal > remainingBudget) {
        showSweetAlert("Budget Limit Exceeded", `Cannot add this item. Total PPMP cost (₱${projectedTotal.toLocaleString()}) exceeds your remaining budget of ₱${remainingBudget.toLocaleString()}.`, "error");
        return;
      }

      const $row = $("<tr></tr>")
        .data("category-id", parseInt(categoryId))
        .data("subcategory-id", parseInt(subCategoryId));

        $row.append(`<td>${categoryName}</td>`);
        $row.append(`<td>${subCategoryName}</td>`);
        $row.append(`<td>${item}</td>`);
        $row.append(`<td>${itemDescription}</td>`);
        $row.append(`<td>${specification}</td>`);
        $row.append(`<td>${quantity}</td>`);
        $row.append(`<td>${unitOfMeasure}</td>`);
        $row.append(`<td class="text-end">${parseFloat(unitCost).toLocaleString(undefined, {minimumFractionDigits:2})}</td>`);
        $row.append(`<td class="text-end">${parseFloat(totalCost).toLocaleString(undefined, {minimumFractionDigits:2})}</td>`);
        $row.append(`<td>${quarterNeeded}</td>`);
        $row.append(`<td>${justification}</td>`);

      const removeBtn = $(`
        <button type="button" class="btn btn-sm btn-danger" title="Remove">
          <i class="fa fa-trash"></i>
        </button>
      `);

      removeBtn.on("click", function () {
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
            $row.remove();
          }
        });
      });

      $row.append($("<td class='text-center'></td>").append(removeBtn));

      $("#edit_ppmpTable tbody").append($row);

      document.getElementById("UpdateForm").reset();

      const subCatSelect = $("#edit_sub_category_id");
      subCatSelect.html('<option value="" disabled selected>Select Subcategory</option>');
      subCatSelect.prop("disabled", true);
      subCatSelect.val(null).trigger("change.select2");
    });

    $('#edit_clearBtn').on('click', function() {
      const form = $('#UpdateForm')[0];
      form.reset();

      const subCatSelect = $('#edit_sub_category_id');
      subCatSelect.html('<option value="" disabled selected>Select Subcategory</option>');
      subCatSelect.prop('disabled', true);
      subCatSelect.val(null).trigger('change.select2');
    });

    $('#edit_sub_category_id').select2({
      dropdownParent: $('#UpdateModal'),
      width: '100%',
      placeholder: 'Select Subcategory'
    });
    
    $('#edit_category_id').change(function () {
      const categoryId = $(this).val();

      if (!categoryId) return;

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: { action: 'GetSubcategoriesByCategory', category_id: categoryId },
        dataType: 'json',
        success: function (response) {
          const $subCatSelect = $('#edit_sub_category_id');
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
    
    $('#datatable').on('click', '.edit-ppmp', function () {
      const ppmp_id = $(this).data('ppmp-id');
      $('#edit_ppmp_id').val(ppmp_id);

      const tbody = $("#edit_ppmpTable tbody");
      tbody.empty();

      $.ajax({
        url: "../php/processes.php",
        type: "POST",
        data: { action: "GetPPMPItems", ppmp_id: ppmp_id },
        dataType: "json",
        success: function(response) {
          if (response.success) {
            const tbody = $("#edit_ppmpTable tbody");
            tbody.empty();

            if (response.data.length === 0) {
              tbody.append(`<tr><td colspan="12" class="text-center">No items found.</td></tr>`);
            } else {
              response.data.forEach(item => {
                const categoryId = item.category_id || 0;
                const subCategoryId = item.sub_category_id || 0;
                const categoryName = item.category_name ?? "-";
                const subCatName = item.sub_cat_name ?? "-";
                const totalCost = parseFloat(item.total_cost || 0).toFixed(2);
                const unitCost = parseFloat(item.unit_cost || 0).toFixed(2);

                const row = `
                  <tr
                    data-item-id="${item.item_id || ''}"
                    data-category-id="${categoryId}"
                    data-subcategory-id="${subCategoryId}"
                  >
                    <td>${categoryName}</td>
                    <td>${subCatName}</td>
                    <td>${item.item_name || ''}</td>
                    <td>${item.item_description || ''}</td>
                    <td>${item.specifications || ''}</td>
                    <td class="text-center">${item.quantity || ''}</td>
                    <td>${item.unit_of_measure || ''}</td>
                    <td class="text-end">${unitCost}</td>
                    <td class="text-end">${totalCost}</td>
                    <td class="text-center">${item.quarter_needed || ''}</td>
                    <td>${item.justification || ''}</td>
                    <td class="text-center">
                      <button type="button" class="btn btn-danger btn-sm remove-item">
                        <i class="fa fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                `;
                tbody.append(row);
              });
            }

            $('#UpdateModal').modal('show');
          } else {
            Swal.fire("Error", response.message || "Unable to load PPMP items.", "error");
          }
        },error: function(xhr, status, error) {
          Swal.fire("Server Error", error, "error");
        }
      });
    });

    $(document).on('click', '#edit_ppmpTable .remove-item', function() {
      const row = $(this).closest('tr');
      Swal.fire({
        title: "Are you sure?",
        text: "This item will be removed from the update list.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, remove it",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#a83232",
        cancelButtonColor: "#555555"
      }).then(result => {
        if (result.isConfirmed) {
          row.remove();
        }
      });
    });

    $('#UpdateForm').submit(function (e) {
      e.preventDefault();

      const items = [];
      $('#edit_ppmpTable tbody tr').each(function() {
        const row = $(this);
        items.push({
          category_id: row.data('category-id'),
          sub_category_id: row.data('subcategory-id'),
          category_name: row.find('td:eq(0)').text().trim(),
          sub_category_name: row.find('td:eq(1)').text().trim(),
          item_name: row.find('td:eq(2)').text().trim(),
          item_description: row.find('td:eq(3)').text().trim(),
          specification: row.find('td:eq(4)').text().trim(),
          quantity: row.find('td:eq(5)').text().trim(),
          unit_of_measure: row.find('td:eq(6)').text().trim(),
          unit_cost: row.find('td:eq(7)').text().trim(),
          total_cost: row.find('td:eq(8)').text().trim(),
          quarter_needed: row.find('td:eq(9)').text().trim(),
          justification: row.find('td:eq(10)').text().trim()
        });
      });

      const hasUnsavedInputs =
        $('#edit_category_id').val() ||
        $('#edit_sub_category_id').val() ||
        $('#edit_item').val().trim() ||
        $('#edit_item_description').val().trim() ||
        $('#edit_specification').val().trim() ||
        $('#edit_quantity').val().trim() !== "" && $('#edit_quantity').val() !== "1" ||
        $('#edit_unit_of_measure').val().trim() ||
        $('#edit_unit_cost').val().trim() ||
        $('#edit_quarter_needed').val() ||
        $('#edit_justification').val().trim();

      if (hasUnsavedInputs) {
        showSweetAlert("Unsaved Item Detected", "You have filled out fields but didn’t add the item to the PPMP list. Please click 'Add to PPMP' first or clear the fields.", "warning"); 
        return;
      }

      if (items.length === 0) {
        showSweetAlert("No Items", "Please add at least one PPMP item before submitting.", "warning"); 
        return;
      }

      const formData = new FormData(this);
      formData.append('action', 'UpdatePPMPForm');
      formData.append('ppmp_items', JSON.stringify(items));

      console.log(JSON.stringify(items, null, 2));

      $.ajax({
        type: 'POST',
        url: '../php/processes.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
          if (response.success) {
            showSweetAlert("Success!", response.message, "success", "ppmp.php"); //FORMAT: TITLE, TEXT, ICON, URL
          } else {
            showSweetAlert("Error", response.message, "error"); //FORMAT: TITLE, TEXT, ICON, URL
          }
          
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          showSweetAlert("Error", "Something went wrong during submission.", "error");
        }
      });
    });
    // END UPDATE PROCESS

    
    // VIEW PPMP DETAILS
    $('#datatable').on('click', '.view-ppmp', function () {
      const ppmpId = $(this).data("ppmp-id");

      $.ajax({
        url: "../php/processes.php", 
        type: "POST",
        data: { action: "GetPPMPItems", ppmp_id: ppmpId },
        dataType: "json",
        success: function(response) {
          if (response.success) {
            const tbody = $("#ppmpItemsTable tbody");
            tbody.empty();

            if (response.data.length === 0) {
              tbody.append(`<tr><td colspan="12" class="text-center">No items found.</td></tr>`);
            } else {
              response.data.forEach(item => {
                tbody.append(`
                  <tr>
                    <td>${item.category_name}</td>
                    <td>${item.sub_cat_name || '-'}</td>
                    <td>${item.item_name}</td>
                    <td>${item.item_description}</td>
                    <td>${item.specifications}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td>${item.unit_of_measure}</td>
                    <td class="text-end">₱${parseFloat(item.unit_cost).toFixed(2)}</td>
                    <td class="text-end">₱${parseFloat(item.total_cost).toFixed(2)}</td>
                    <td class="text-center">${item.quarter_needed}</td>
                    <td>${item.procurement_method || '-'}</td>
                    <td>${item.justification}</td>
                  </tr>
                `);
              });
            }

            $("#ViewPPMPModal").modal("show");
          } else {
            showSweetAlert("Error", response.message || "Unable to load PPMP items.", "error"); 
          }
        },
        error: function(xhr, status, error) {
          showSweetAlert("Server Error", error, "error"); 
        }
      });
    });
    // END VIEW PPMP DETAILS

    // Multiple Deletion
    $('#select-all').on('click', function () {
      var isChecked = $(this).is(':checked');
      $('.select-record').prop('checked', isChecked);
    });

    $('#datatable').on('change', '.select-record', function () {
      if (!$(this).is(':checked')) {
        $('#select-all').prop('checked', false);
      } else {
        var allChecked = $('.select-record').length === $('.select-record:checked').length;
        $('#select-all').prop('checked', allChecked);
      }
    });

    $('#delete-selected').on('click', function () {
      var selectedIDs = [];
      $('.select-record:checked').each(function () {
        selectedIDs.push($(this).val());
      });

      if (selectedIDs.length === 0) {
        Swal.fire("No selection", "Please select at least one record to delete.", "info");
        return;
      }

      confirmMultipleDeletion(selectedIDs.length, function () {
        deleteMultipleRecords("ppmp", "ppmp_id", selectedIDs, "ppmp.php");
      });
    });

  });

</script>