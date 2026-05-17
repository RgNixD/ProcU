<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    include 'access_guard.php';
    exit;
}
require_once 'classes.php';
header('Content-Type: application/json');
$operator_ID = $_SESSION['user_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new db_class();
    $response = ['success' => false, 'message' => 'Unknown error'];

    $action = $_POST['action'] ?? null;
    if (!$action || !is_string($action)) {
        echo json_encode(['success' => false, 'message' => 'Missing or invalid action']);
        exit;
    }

    try {
        switch ($action) {
            case "login":
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                $result = $db->login($username, $password);

                if (is_array($result) && $result['user_id'] != 0) {
                    if ($result['is_active'] == 0) {
                        $response = ['success' => false, 'message' => "Your account is inactive. Please contact the administrator."];
                        break;
                    }

                    $_SESSION['user_id'] = $result['user_id'];
                    $_SESSION['username'] = $result['username'];
                    $_SESSION['full_name'] = $result['full_name'];
                    $_SESSION['permissions'] = $result['permissions'];
                    $_SESSION['session_token'] = $result['session_token'];
                    $_SESSION['login_time'] = date('Y-m-d H:i:s');

                    $response = ['success' => true, 'message' => "Redirecting...", 'redirect' => determineRedirectURL($result['permissions'])];
                } elseif (is_string($result)) {
                    $response = ['success' => false, 'message' => $result];
                } else {
                    $response = ['success' => false, 'message' => "Invalid username or password!"];
                }
                break;

            case "searchEmail":
                $email = $_POST['email'];

                $result = $db->searchEmail($email);
                if ($result['exists']) {
                    $response = ['exists' => true, 'id' => $result['id']];
                } else {
                    $response = ['exists' => false];
                }
                break;

            case "sendVerificationCode":
                $email = $_POST['email'];
                $user_id = $_POST['user_id'];

                $result = $db->sendVerificationCode($email, $user_id);
                if ($result) {
                    $response = ['success' => true, 'message' => "A verification code has been sent to your email", 'redirect' => "verify-code.php?email=" . $email . "&&id=" . $user_id];
                } else {
                    $response = ['success' => false, 'message' => "Failed to send verification code"];
                }
                break;

            case "verifyCode":
                $email = $_POST['email'];
                $user_id = $_POST['user_id'];
                $code = $_POST['code'];

                $result = $db->verifyCode($email, $user_id, $code);
                if ($result) {
                    $response = ['success' => true, 'redirect' => "change-password.php?email=" . $email . "&&id=" . $user_id];
                } else {
                    $response = ['success' => false, 'message' => "Invalid code"];
                }
                break;

            case "changePassword":
                $email = $_POST['email'];
                $user_id = $_POST['user_id'];
                $password = $_POST['password'];
                $cpassword = $_POST['cpassword'];

                $result = $db->changePassword($email, $user_id, $password, $cpassword);

                if (is_string($result)) {
                    $response = ['success' => false, 'message' => $result];
                } else if ($result) {
                    $response = ['success' => true, 'message' => "Password has been successfully changed. Please login.", 'redirect' => "index.php"];
                } else {
                    $response = ['success' => false, 'message' => "Error updating new password"];
                }
                break;

            // USER MANAGEMENT PROCESSES**********************************************************
            case "updateProfilePicture":
                if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    $response['message'] = "No file uploaded or file upload error.";
                    break;
                }

                $user_id = $_POST['user_id'] ?? null;
                $image = $_FILES['image'];

                $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($image['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
                    $response['message'] = "Invalid file type or extension.";
                    break;
                }

                if ($image['size'] > 500000) {
                    $response = ['message' => "File size exceeds 500 KB."];
                    break;
                }

                $user = $db->getUserById($user_id);
                if (!$user) {
                    $response = ['message' => "User not found."];
                    break;
                }

                $lastname = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $user['last_name']));
                $datetime = date("Ymd_His");
                $new_filename = "{$lastname}_{$datetime}.{$file_extension}";
                $destination = "../assets/img/user-profiles/" . $new_filename;

                if (move_uploaded_file($image['tmp_name'], $destination)) {
                    if (!empty($user['profile']) && $user['profile'] !== 'avatar.png') {
                        $oldPath = "../assets/img/user-profiles/" . $user['profile'];
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $result = $db->updateProfilePicture($user_id, $new_filename, $operator_ID);
                    if ($result === true) {
                        $response = ['success' => true, 'message' => "Profile has been updated!"];
                    } else {
                        $response = ['message' => "Unknown error during DB update."];
                    }
                } else {
                    $response = ['message' => "Failed to move uploaded file."];
                }
                break;

            case "UpdateInformation":
                $user_id = $_POST['user_id'] ?? null;
                $username = $_POST['username'] ?? '';
                $firstname = $_POST['firstname'] ?? '';
                $lastname = $_POST['lastname'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $email = $_POST['email'] ?? '';

                $result = $db->UpdateInformation($user_id, $username, $firstname, $lastname, $phone, $email, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Updating information successful!"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Updating information failed!"];
                }
                break;

            case "updatePassword":
                $user_id = $_POST['user_id'] ?? null;
                $OldPassword = $_POST['OldPassword'] ?? '';
                $password = $_POST['password'] ?? '';
                $cpassword = $_POST['cpassword'] ?? '';

                $result = $db->updatePassword($user_id, $OldPassword, $password, $cpassword, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Password successfully changed"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error changing password"];
                }
                break;

            case "AddUserForm":

                $username = $_POST['username'] ?? '';
                $first_name = $_POST['first_name'] ?? '';
                $last_name = $_POST['last_name'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $email = $_POST['email'] ?? '';
                $access_name = $_POST['access_name'] ?? '';

                $result = $db->AddUserForm($username, $first_name, $last_name, $phone, $email, $access_name, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => $access_name . " user successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding user"];
                }
                break;

            case "UpdateUserForm":

                $user_id = $_POST['user_id'] ?? '';
                $username = $_POST['username'] ?? '';
                $first_name = $_POST['first_name'] ?? '';
                $last_name = $_POST['last_name'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $email = $_POST['email'] ?? '';
                $access_name = $_POST['access_name'] ?? '';

                $result = $db->UpdateUserForm($user_id, $username, $first_name, $last_name, $phone, $email, $access_name, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => $access_name . " user successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding user"];
                }
                break;
            // END USER MANAGEMENT PROCESSES**********************************************************


            // CATEGORY PROCESSES**********************************************************
            case "AddCategoryForm":

                $category_name = $_POST['category_name'] ?? '';
                $category_code = $_POST['category_code'] ?? '';
                $description = $_POST['description'] ?? '';

                $result = $db->AddCategoryForm($category_name, $category_code, $description, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Category successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding category"];
                }
                break;

            case "UpdateCategoryForm":

                $category_id = $_POST['category_id'] ?? '';
                $category_name = $_POST['category_name'] ?? '';
                $category_code = $_POST['category_code'] ?? '';
                $description = $_POST['description'] ?? '';
                $is_active = $_POST['is_active'] ?? '';

                $result = $db->UpdateCategoryForm($category_id, $category_name, $category_code, $description, $is_active, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Category successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding category"];
                }
                break;
            // END CATEGORY PROCESSES**********************************************************

            // SUB-CATEGORY PROCESSES **********************************************************
            case "AddSubCategoryForm":

                $category_id = $_POST['category_id'] ?? '';
                $sub_cat_name = $_POST['sub_cat_name'] ?? '';
                $sub_cat_description = $_POST['sub_cat_description'] ?? '';

                $result = $db->AddSubCategoryForm($category_id, $sub_cat_name, $sub_cat_description, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Sub-category successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding sub-category"];
                }
                break;

            case "UpdateSubCategoryForm":

                $sub_category_id = $_POST['sub_category_id'] ?? '';
                $category_id = $_POST['category_id'] ?? '';
                $sub_cat_name = $_POST['sub_cat_name'] ?? '';
                $sub_cat_description = $_POST['sub_cat_description'] ?? '';

                $result = $db->UpdateSubCategoryForm($sub_category_id, $category_id, $sub_cat_name, $sub_cat_description, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Sub-category successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error updating sub-category"];
                }
                break;

            case "GetSubcategoriesByCategory":
                $category_id = $_POST['category_id'] ?? 0;

                if (!$category_id) {
                    $response = ['success' => false, 'message' => 'Invalid category ID.'];
                    break;
                }

                $subcategories = $db->getSubcategoriesByCategory($category_id);

                if ($subcategories) {
                    $response = ['success' => true, 'data' => $subcategories];
                } else {
                    $response = ['success' => false, 'message' => 'No subcategories found for this category.'];
                }
                break;
            // END SUB-CATEGORY PROCESSES **********************************************************


            // ITEM NAME PROCESSES **********************************************************
            case "AddItemNameForm":

                $sub_category_id = $_POST['sub_category_id'] ?? '';
                $item_name = $_POST['item_name'] ?? '';

                $result = $db->AddItemNameForm($sub_category_id, $item_name, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Item name successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding item name"];
                }
                break;

            case "UpdateItemNameForm":

                $item_name_id = $_POST['item_name_id'] ?? '';
                $sub_category_id = $_POST['sub_category_id'] ?? '';
                $item_name = $_POST['item_name'] ?? '';

                $result = $db->UpdateItemNameForm($item_name_id, $sub_category_id, $item_name, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Item name successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error updating item name"];
                }
                break;

            case "GetItemNamesBySubCategory":
                $sub_category_id = $_POST['sub_category_id'] ?? 0;

                if (!$sub_category_id) {
                    $response = ['success' => false, 'message' => 'Invalid subcategory ID.'];
                    break;
                }

                $result = $db->getItemNamesBySubCategory($sub_category_id);
                $item_names = [];
                while ($row = $result->fetch_assoc()) {
                    $item_names[] = ['item_name_id' => $row['item_name_id'], 'item_name' => $row['item_name']];
                }

                if (!empty($item_names)) {
                    $response = ['success' => true, 'data' => $item_names];
                } else {
                    $response = ['success' => false, 'message' => 'No item names found for this subcategory.'];
                }
                break;
            // END ITEM NAME PROCESSES **********************************************************


            // FISCAL YEAR PROCESSES**********************************************************
            case "createFiscalYear":

                $year = $_POST['year'] ?? '';
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';

                $result = $db->createFiscalYear($year, $start_date, $end_date, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Fiscal Year successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding Fiscal Year"];
                }
                break;

            case "updateFiscalYear":

                $fiscal_year_id = $_POST['fiscal_year_id'] ?? '';
                $year = $_POST['year'] ?? '';
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                $status = $_POST['status'] ?? '';
                $is_lock = $_POST['is_lock'] ?? 0;

                $result = $db->updateFiscalYear($fiscal_year_id, $year, $start_date, $end_date, $status, $is_lock, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Fiscal Year successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding Fiscal Year"];
                }
                break;
            // END FISCAL YEAR PROCESSES**********************************************************


            // ANNUAL BUDGET PROCESSES**********************************************************
            case "AddAnnualBudget":

                $fiscal_year_id = $_POST['fiscal_year_id'] ?? '';
                $total_budget = $_POST['total_budget'] ?? '';

                $result = $db->AddAnnualBudget($fiscal_year_id, $total_budget, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Annual Budget successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding annual budget"];
                }
                break;

            case "UpdateAnnualBudget":

                $annual_budget_id = $_POST['annual_budget_id'] ?? '';
                $fiscal_year_id = $_POST['fiscal_year_id'] ?? '';
                $total_budget = $_POST['total_budget'] ?? '';

                $result = $db->UpdateAnnualBudget($annual_budget_id, $fiscal_year_id, $total_budget, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Annual Budget successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error updating annual budget"];
                }
                break;

            case 'get_annual_budget_allocations':
                if (!isset($_POST['annual_budget_id']) || !is_numeric($_POST['annual_budget_id'])) {
                    echo json_encode(['error' => 'Invalid Annual Budget ID provided.']);
                    exit;
                }

                $annual_budget_id = (int) $_POST['annual_budget_id'];

                $details = $db->getDepartmentAllocationsByAnnualBudget($annual_budget_id);

                if (isset($details['error'])) {
                    echo json_encode(['error' => $details['error']]);
                    exit;
                }

                echo json_encode($details);
                exit;
            // END ANNUAL BUDGET PROCESSES**********************************************************


            // BUDGET ALLOCATION PROCESSES**********************************************************
            case "AddBudgetAllocation":

                $office_id = $_POST['office_id'] ?? '';
                $amount = $_POST['amount'] ?? '';

                $result = $db->AddBudgetAllocation($office_id, $amount, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Budget Allocation successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding budget allocation"];
                }
                break;

            case "UpdateBudgetAllocation":

                $allocation_id = $_POST['allocation_id'] ?? '';
                $office_id = $_POST['office_id'] ?? '';
                $amount = $_POST['amount'] ?? '';
                $status = $_POST['status'] ?? '';

                $result = $db->UpdateBudgetAllocation($allocation_id, $office_id, $amount, $status, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Budget Allocation successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error updating budget allocation"];
                }
                break;

            case "GetBudgetSummaryByFiscalYear":

                $fiscal_year_id = $_POST['fiscal_year_id'] ?? '';

                $summary = $db->getBudgetSummaryByFiscalYear($fiscal_year_id);

                if ($summary) {
                    $response = [
                        'success' => true,
                        'annual_budget' => (float) $summary['annual_budget'],
                        'total_allocated' => (float) $summary['total_allocated'],
                        'available_balance' => (float) $summary['available_balance']
                    ];
                } else {
                    $response = [
                        'success' => true,
                        'annual_budget' => 0,
                        'total_allocated' => 0,
                        'available_balance' => 0
                    ];
                }
                break;
            // END BUDGET ALLOCATION PROCESSES**********************************************************


            // OFFICE PROCESSES**********************************************************
            case "AddOfficeForm":

                $office_name = $_POST['office_name'] ?? '';
                $office_code = $_POST['office_code'] ?? '';
                $head_id = $_POST['head_id'] ?? '';
                $description = $_POST['description'] ?? '';

                $result = $db->AddOfficeForm($office_name, $office_code, $head_id, $description, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Office successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding office"];
                }
                break;

            case "UpdateOfficeForm":

                $office_id = $_POST['office_id'] ?? '';
                $office_name = $_POST['office_name'] ?? '';
                $office_code = $_POST['office_code'] ?? '';
                $head_id = $_POST['head_id'] ?? '';
                $description = $_POST['description'] ?? '';

                $result = $db->UpdateOfficeForm($office_id, $office_name, $office_code, $head_id, $description, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Office successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding office"];
                }
                break;
            // END OFFICE PROCESSES**********************************************************


            // PPMP PROCESSES**********************************************************

            case "getPPMPItemsById":
                $ppmp_id = $_POST['ppmp_id'] ?? 0;
                if (empty($ppmp_id)) {
                    $response = ['success' => false, 'message' => 'Missing PPMP ID.'];
                    break;
                }
                $result = $db->getPPMPItemsById($ppmp_id);
                $items = [];
                while ($row = $result->fetch_assoc()) {
                    $items[] = $row;
                }
                $headerData = $db->getPPMPHeaderDetails($ppmp_id);
                $response = [
                    'success' => true,
                    'items' => $items,
                    'header' => $headerData
                ];
                break;

            case "RequestPPMPRevision":
                $ppmp_id = intval($_POST['ppmp_id'] ?? 0);
                $reason = trim($_POST['reason'] ?? '');

                if (!$ppmp_id || empty($reason)) {
                    $response = ['success' => false, 'message' => 'Invalid data.'];
                    break;
                }

                $result = $db->RequestPPMPRevision($ppmp_id, $reason, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => 'Revision request sent.'];
                } else {
                    $response = ['success' => false, 'message' => $result];
                }

                break;

            case "ApprovePPMPRevisionRequest":

                $ppmp_id = intval($_POST['ppmp_id'] ?? 0);
                $processed_by = $_SESSION['user_id'];

                if ($ppmp_id <= 0) {
                    $response = ['success' => false, 'message' => 'Invalid PPMP ID.'];
                    break;
                }
                $result = $db->approvePPMPRevisionRequest($ppmp_id, $processed_by);
                if ($result === true) {
                    $response = ['success' => true, 'message' => 'Revision request approved successfully.'];
                } else {
                    $response = ['success' => false, 'message' => $result];
                }
                break;


            case "RejectPPMPRevisionRequest":

                $ppmp_id = intval($_POST['ppmp_id'] ?? 0);
                $processed_by = $_SESSION['user_id'];

                if ($ppmp_id <= 0) {
                    $response = ['success' => false, 'message' => 'Invalid PPMP ID.'];
                    break;
                }
                $result = $db->rejectPPMPRevisionRequest($ppmp_id, $processed_by);
                if ($result === true) {
                    $response = ['success' => true, 'message' => 'Revision request rejected successfully.'];
                } else {
                    $response = ['success' => false, 'message' => $result];
                }
                break;



            case "AddPPMPForm":
                $user_id = $_POST['user_id'] ?? '';
                $ppmp_items = $_POST['ppmp_items'] ?? '[]';
                $ppmp_items = json_decode($ppmp_items, true);

                $is_final = isset($_POST['is_final']) ? (int) $_POST['is_final'] : 0;

                if (empty($user_id) || empty($ppmp_items) || !is_array($ppmp_items)) {
                    $response = [
                        'success' => false,
                        'message' => 'Incomplete data provided. Please ensure all required fields (User ID and Items) are filled in before proceeding.'
                    ];
                    break;
                }

                $upload_dir = '../assets/ppmp_attachments/';
                $uploaded_files_map = [];

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES as $input_name => $file) {
                    if (preg_match('/^file_(\d+)_(\d+)$/', $input_name, $matches)) {
                        $temp_item_id = $matches[1];

                        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                            continue;
                        }

                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $unique_filename = time() . '_' . uniqid() . '.' . $extension;
                        $destination = $upload_dir . $unique_filename;

                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $uploaded_files_map[$temp_item_id][] = $unique_filename;
                        }
                    }
                }

                $result = $db->AddPPMPForm($user_id, $ppmp_items, $is_final, $uploaded_files_map);

                if ($result === true) {
                    $response = ['success' => true, 'message' => 'PPMP successfully created.'];
                } elseif (is_string($result)) {
                    $response = ['success' => false, 'message' => $result];
                } else {
                    $response = ['success' => false, 'message' => 'Error adding PPMP.'];
                }
                break;

            case "UpdatePPMPForm":
                $ppmp_id = isset($_POST['ppmp_id']) ? (int) $_POST['ppmp_id'] : 0;
                $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
                $is_final = isset($_POST['is_final']) ? (int) $_POST['is_final'] : 0;

                $ppmp_items = $_POST['ppmp_items'] ?? '[]';
                $ppmp_items = json_decode($ppmp_items, true);

                if ($ppmp_id <= 0 || $user_id <= 0 || empty($ppmp_items) || !is_array($ppmp_items)) {
                    $response = [
                        'success' => false,
                        'message' => 'Incomplete data provided. Please ensure all required fields (PPMP ID, User ID, and Items) are filled in before proceeding.'
                    ];
                    break;
                }

                $upload_dir = '../assets/ppmp_attachments/';
                $uploaded_files_map = [];

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES as $input_name => $file) {
                    if (preg_match('/^file_(.+)_(\d+)$/', $input_name, $matches)) {
                        $temp_item_id = $matches[1];

                        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                            continue;
                        }

                        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $unique_filename = time() . '_' . uniqid() . '.' . $extension;
                        $destination = $upload_dir . $unique_filename;

                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $uploaded_files_map[$temp_item_id][] = $unique_filename;
                        }
                    }
                }

                $result = $db->UpdatePPMPForm($ppmp_id, $user_id, $ppmp_items, $is_final, $uploaded_files_map);

                if ($result === true) {
                    $response = ['success' => true, 'message' => 'PPMP successfully updated.'];
                } elseif (is_string($result)) {
                    $response = ['success' => false, 'message' => $result];
                } else {
                    $response = ['success' => false, 'message' => 'Error updating PPMP.'];
                }
                break;

            case "UpdatePPMPStatus":
                $ppmp_id = isset($_POST['ppmp_id']) ? (int) $_POST['ppmp_id'] : 0;
                $status = trim($_POST['status'] ?? '');
                $notes = trim($_POST['notes'] ?? '');
                $reviewed_by = $_SESSION['user_id'];

                if ($ppmp_id <= 0 || $status === '') {
                    $response = ['success' => false, 'message' => 'Missing required PPMP ID or Status.'];
                    break;
                }

                $result = $db->updatePPMPStatus($ppmp_id, $status, $notes, $reviewed_by);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "PPMP status updated to '{$status}' successfully."];
                } elseif (is_string($result)) {
                    $response = ['success' => false, 'message' => $result];
                } else {
                    $response = ['success' => false, 'message' => "Failed to update PPMP status."];
                }
                break;

            case 'CheckMissingApprovedPPMP':

                $ppmpRecords = $db->getAllPPMPRecordsForProcHead("Approved");

                if ($ppmpRecords->num_rows === 0) {
                    echo json_encode([
                        "success" => false,
                        "type" => "no_ppmp",
                        "message" => "There are no approved PPMPs available for consolidation."
                    ]);
                    exit;
                }

                $warning = [];

                $missingOffices = $db->getOfficesWithoutApprovedPPMP();

                if (!empty($missingOffices)) {
                    $currentFY = $db->getCurrentFiscalYear(true);
                    $fiscalYearText = $currentFY ? $currentFY['year'] : '';
                    $fiscalYearId = $currentFY ? (int) $currentFY['fiscal_year_id'] : 0;

                    foreach ($missingOffices as $office_id => $office_name) {
                        $message = "Please submit your PPMP for Fiscal Year $fiscalYearText. An admin attempted to consolidate approved PPMPs.";

                        if ($fiscalYearId > 0) {
                            $db->insertNotificationIfNotExists($office_id, $fiscalYearId, $message);
                        }
                    }

                    $warning["warning_type"] = "missing_ppmp";
                    $warning["missing_offices"] = array_values($missingOffices);
                }

                $unusedBudget = $db->getRemainingAnnualBudget();
                $threshold = 5000;

                if ($unusedBudget > $threshold) {
                    $warning["unused_budget"] = [
                        "unused" => number_format($unusedBudget, 2),
                        "threshold" => number_format($threshold, 2)
                    ];
                }

                echo json_encode([
                    "success" => true,
                    "warning" => !empty($warning) ? $warning : new stdClass()
                ]);
                exit;

            case 'ConsolidatePPMP':
                $result = $db->ConsolidatePPMP($_SESSION['user_id']);

                if ($result === true) {
                    echo json_encode([
                        'success' => true,
                        'message' => "PPMP items successfully consolidated."
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => $result
                    ]);
                }
                exit();

            case "CheckSubcategoryConsolidation":
                $sub_category_id = $_POST['sub_category_id'] ?? 0;

                if (!$sub_category_id) {
                    echo json_encode(['success' => false, 'message' => 'Invalid subcategory selected.']);
                    exit;
                }

                $hasConsolidated = $db->checkConsolidatedPPMPBySubcategory($sub_category_id);

                if (!$hasConsolidated) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'There is no consolidated PPMP related to the selected subcategory.'
                    ]);
                    exit;
                }

                echo json_encode(['success' => true]);
                exit;

            case "GetConsolidatedItemNamesBySubcategory":
                $sub_category_id = (int) ($_POST['sub_category_id'] ?? 0);
                $fy = $db->getCurrentFiscalYear(true);

                if (!$fy) {
                    echo json_encode(["success" => false, "message" => "Current fiscal year not found."]);
                    exit;
                }
                if ($sub_category_id <= 0) {
                    echo json_encode(["success" => false, "message" => "Invalid subcategory."]);
                    exit;
                }
                $rows = $db->getConsolidatedItemNamesBySubcategory((int) $fy['fiscal_year_id'], $sub_category_id);
                echo json_encode(["success" => true, "data" => $rows]);
                exit;

            case "GetOfficesInvolvedForConsolidatedItem":
                $sub_category_id = (int) ($_POST['sub_category_id'] ?? 0);
                $item_name_id = (int) ($_POST['item_name_id'] ?? 0);
                $fy = $db->getCurrentFiscalYear(true);
                if (!$fy) {
                    echo json_encode(["success" => false, "message" => "Current fiscal year not found."]);
                    exit;
                }
                if ($sub_category_id <= 0 || $item_name_id <= 0) {
                    echo json_encode(["success" => false, "message" => "Invalid inputs."]);
                    exit;
                }
                $offices = $db->getOfficesInvolvedForConsolidatedItem(
                    $fy['fiscal_year_id'],
                    $sub_category_id,
                    $item_name_id
                );

                echo json_encode(["success" => true, "data" => $offices]);
                exit;

            case "SaveAppItemDetails":

                $app_item_id = (int) ($_POST['app_item_id'] ?? 0);
                $item_description = trim($_POST['item_description'] ?? '');
                $mode_of_procurement = (int) ($_POST['mode_of_procurement'] ?? 0);
                $pre_procurement_conference = trim($_POST['pre_procurement_conference'] ?? '');
                $bid_cat_ID = (int) ($_POST['bid_cat_ID'] ?? 0);
                $procurement_start_date = $_POST['procurement_start_date'] ?? '';
                $bidding_date = $_POST['bidding_date'] ?? '';
                $source_of_funds = trim($_POST['source_of_funds'] ?? '');
                $proc_strat_ID = (int) ($_POST['proc_strat_ID'] ?? 0);
                $remarks = trim($_POST['remarks'] ?? '');

                if ($app_item_id <= 0) {
                    $response = ['success' => false, 'message' => 'Invalid APP item.'];
                    break;
                }

                if ($item_description === '') {
                    $response = ['success' => false, 'message' => 'Project Description is required.'];
                    break;
                }

                if ($mode_of_procurement <= 0 || $bid_cat_ID <= 0 || $proc_strat_ID <= 0) {
                    $response = ['success' => false, 'message' => 'Please complete required fields.'];
                    break;
                }

                if (!in_array($pre_procurement_conference, ['Yes', 'No'], true)) {
                    $response = ['success' => false, 'message' => 'Invalid Early Procurement option.'];
                    break;
                }

                if ($procurement_start_date === '' || $bidding_date === '') {
                    $response = ['success' => false, 'message' => 'Please provide timeline dates.'];
                    break;
                }

                if (strtotime($bidding_date) < strtotime($procurement_start_date)) {
                    $response = ['success' => false, 'message' => 'End date must be after start date.'];
                    break;
                }

                if ($source_of_funds === '') {
                    $response = ['success' => false, 'message' => 'Please select source of funds.'];
                    break;
                }

                $conn = $db->getConnection();
                $conn->begin_transaction();

                try {
                    $db->updateDraftAppItemDetails($app_item_id, [
                        'item_description' => $item_description,
                        'mode_of_procurement' => $mode_of_procurement,
                        'pre_procurement_conference' => $pre_procurement_conference,
                        'bid_cat_ID' => $bid_cat_ID,
                        'procurement_start_date' => $procurement_start_date,
                        'bidding_date' => $bidding_date,
                        'source_of_funds' => $source_of_funds,
                        'proc_strat_ID' => $proc_strat_ID,
                        'remarks' => $remarks
                    ]);

                    $conn->commit();

                    $response = [
                        'success' => true,
                        'message' => 'APP item details successfully saved.'
                    ];
                } catch (Exception $e) {
                    $conn->rollback();
                    $response = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
                break;


            case "FinalizeEntireApp":

                $fiscal_year = $db->getCurrentFiscalYear(true);
                if (!$fiscal_year) {
                    $response = ['success' => false, 'message' => 'No active fiscal year.'];
                    break;
                }

                $fiscal_year_id = (int) $fiscal_year['fiscal_year_id'];

                $conn = $db->getConnection();
                $conn->begin_transaction();

                try {
                    $draftApp = $db->getCurrentDraftAppByFiscalYear($fiscal_year_id);

                    if (!$draftApp) {
                        throw new Exception('No draft APP found.');
                    }

                    $app_version_id = (int) $draftApp['app_version_id'];

                    if (!$db->isDraftAppReadyForFinalization($app_version_id)) {
                        throw new Exception('Cannot finalize APP. Please complete all consolidated items first.');
                    }

                    $db->finalizeAppVersionAndApp($app_version_id);

                    $conn->commit();

                    $response = [
                        'success' => true,
                        'message' => 'APP successfully finalized.'
                    ];
                } catch (Exception $e) {
                    $conn->rollback();
                    $response = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }

                break;

            // REPORTS-APP.PHP
            case "GetFinalizedAppsByYear":
                $fyId = $_POST['fiscal_year_id'] ?? 0;
                $data = [];

                $rows = $db->getFinalizedApps($fyId);

                if ($rows) {
                    while ($row = $rows->fetch_assoc()) {
                        $row['office_names'] = $db->getOfficeNamesByIds($row['offices_involved']);
                        $data[] = $row;
                    }
                }
                echo json_encode([
                    "success" => true,
                    "data" => $data
                ]);
                exit();

            case "GetAppVersionsByYear":
                $fyId = $_POST['fiscal_year_id'] ?? 0;
                $data = [];
                $rows = $db->getAppVersionsByFiscalYear($fyId);
                if ($rows) {
                    while ($row = $rows->fetch_assoc()) {
                        $row['created_at'] = !empty($row['created_at'])
                            ? date('M d, Y h:i A', strtotime($row['created_at']))
                            : '-';
                        $row['finalized_date'] = !empty($row['finalized_date'])
                            ? date('M d, Y h:i A', strtotime($row['finalized_date']))
                            : '-';
                        $row['total_budget'] = (float) ($row['total_budget'] ?? 0);
                        $row['total_items'] = (int) ($row['total_items'] ?? 0);

                        $data[] = $row;
                    }
                }
                echo json_encode([
                    "success" => true,
                    "data" => $data
                ]);

                exit();

            case "GetSubmittedPPMPItemsByOffice":

                $office_id = (int) ($_POST['office_id'] ?? 0);

                if ($office_id <= 0) {
                    $response = [
                        'success' => false,
                        'message' => 'Invalid office.'
                    ];
                    break;
                }

                try {
                    $result = $db->getSubmittedPPMPItemsByOffice($office_id);
                    $items = [];

                    while ($row = $result->fetch_assoc()) {
                        $items[] = [
                            'office_id' => (int) $row['office_id'],
                            'office_name' => $row['office_name'],
                            'office_code' => $row['office_code'],
                            'office_display' => $row['office_display'],
                            'ppmp_id' => (int) $row['ppmp_id'],
                            'ppmp_code' => $row['ppmp_code'],
                            'ppmp_version_id' => (int) $row['ppmp_version_id'],
                            'ppmp_version_item_id' => (int) $row['ppmp_version_item_id'],
                            'item_description' => $row['item_description'],
                            'specifications' => $row['specifications'],
                            'quantity' => (int) $row['quantity'],
                            'estimated_budget' => number_format((float) $row['estimated_budget'], 2),
                            'total_cost' => number_format((float) $row['total_cost'], 2),
                            'file_attachment' => $row['file_attachment']
                        ];
                    }

                    $response = [
                        'success' => true,
                        'data' => $items
                    ];
                } catch (Exception $e) {
                    $response = [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }

                break;

            case "AllowPPMPRevision":

                $office_ids = $_POST['office_ids'] ?? [];

                if (!is_array($office_ids)) {
                    $office_ids = [$office_ids];
                }

                $office_ids = array_values(array_filter(array_map('intval', $office_ids)));

                $result = $db->AllowPPMPRevision($office_ids, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "PPMP revision successfully enabled"];
                } elseif (is_string($result)) {
                    $response = ['success' => false, 'message' => $result];
                } else {
                    $response = ['success' => false, 'message' => "Error enabling PPMP revision"];
                }

                break;

            case "GetSectorsWithFinalizedPPMP":

                $result = $db->GetSectorsWithFinalizedPPMP();

                if (is_array($result)) {
                    $response = ['success' => true, 'data' => $result];
                } else {
                    $response = ['message' => $result];
                }

                break;
            // END PPMP PROCESSES**********************************************************


            // BIDDING CATEGORY PROCESSES **********************************************************
            case "AddBiddingCategoryForm":

                $bid_cat_name = $_POST['bid_cat_name'] ?? '';

                $result = $db->AddBiddingCategoryForm($bid_cat_name, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Bidding category successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding Bidding category"];
                }
                break;

            case "UpdateBiddingCategoryForm":

                $bid_cat_ID = $_POST['bid_cat_ID'] ?? '';
                $bid_cat_name = $_POST['bid_cat_name'] ?? '';

                $result = $db->UpdateBiddingCategoryForm($bid_cat_ID, $bid_cat_name, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Bidding category successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error updating Bidding category"];
                }
                break;
            // END BIDDING CATEGORY PROCESSES **********************************************************


            // PROCUREMENT STRATEGY PROCESSES **********************************************************
            case "AddProcStrategyForm":

                $proc_strat_name = $_POST['proc_strat_name'] ?? '';

                $result = $db->AddProcStrategyForm($proc_strat_name, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Procurement strategy successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding Procurement strategy"];
                }
                break;

            case "UpdateProcStrategyForm":

                $proc_strat_ID = $_POST['proc_strat_ID'] ?? '';
                $proc_strat_name = $_POST['proc_strat_name'] ?? '';

                $result = $db->UpdateProcStrategyForm($proc_strat_ID, $proc_strat_name, $operator_ID);

                if ($result === true) {
                    $response = ['success' => true, 'message' => "Procurement strategy successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error updating Procurement strategy"];
                }
                break;
            // END PROCUREMENT STRATEGY PROCESSES **********************************************************

            // PROCUREMENT MODE PROCESSES **********************************************************
            case "AddProcMode":
                $name = $_POST['proc_mode_name'] ?? '';
                $result = $db->AddProcMode($name, $operator_ID);

                $response = $result === true
                    ? ['success' => true, 'message' => 'Added successfully']
                    : ['message' => $result];
                break;

            case "UpdateProcMode":
                $id = $_POST['proc_mode_id'];
                $name = $_POST['proc_mode_name'];

                $result = $db->UpdateProcMode($id, $name, $operator_ID);

                $response = $result === true
                    ? ['success' => true, 'message' => 'Updated successfully']
                    : ['message' => $result];
                break;
            // END PROCUREMENT MODE PROCESSES **********************************************************


            // NOTIFICATIONS PROCESSES **********************************************************
            case 'MarkNotificationRead':
                $notifId = $_POST['notification_id'] ?? 0;
                if ($notifId) {
                    $db->markNotificationAsRead($notifId);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
                }
                exit;
            // END NOTIFICATIONS PROCESSES **********************************************************


            // DELETE RECORD PROCESSES**********************************************************
            case "delete_Record":
                $table = $_POST['table'] ?? '';
                $delete_column = $_POST['delete_column'] ?? '';

                if (isset($_POST['delete_IDs']) && !empty($_POST['delete_IDs'])) {
                    $delete_IDs = json_decode($_POST['delete_IDs'], true);
                    if (is_array($delete_IDs)) {
                        $result = $db->DeleteRecords($table, $delete_column, $delete_IDs, $operator_ID, $loggedInUserType);
                    } else {
                        $response = [
                            'success' => false,
                            'message' => "Invalid delete_IDs format."
                        ];
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $delete_ID = $_POST['delete_ID'] ?? '';
                    $result = $db->DeleteRecordForm($table, $delete_column, $delete_ID, $operator_ID, $loggedInUserType);
                }

                if ($result) {
                    if (isset($delete_IDs) && is_array($delete_IDs)) {
                        $count = count($delete_IDs);
                    } else {
                        $count = 1;
                    }

                    $message = ($count === 1)
                        ? "Record has been deleted!"
                        : "$count records have been deleted!";

                    $response = [
                        'success' => true,
                        'message' => $message
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => "Deleting record(s) failed!"
                    ];
                }
                break;
            // END DELETE RECORD PROCESSES**********************************************************

            case "logout_user":
                $userId = $_SESSION['user_id'] ?? null;
                $sessionToken = $_SESSION['session_token'] ?? null;

                if (!$userId || !$sessionToken) {
                    $response = ['success' => false, 'message' => 'Not logged in'];
                    break;
                }

                $success = $db->logoutUser($userId, $sessionToken);
                if ($success) {
                    session_unset();
                    session_destroy();
                    $response = ['success' => true, 'message' => 'Logged out successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Logout failed'];
                }
                break;

            default:
                $response = ['message' => 'Invalid action.'];
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => "Server error: " . $e->getMessage()];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
