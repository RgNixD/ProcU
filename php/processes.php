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
                $user_id    = $_POST['user_id'];

                $result = $db->sendVerificationCode($email, $user_id);
                if ($result) {
                    $response = ['success' => true, 'message' => "A verification code has been sent to your email", 'redirect' => "verify-code.php?email=".$email."&&id=".$user_id];
                } else {
                    $response = ['success' => false, 'message' => "Failed to send verification code"];
                }
                break;
            
            case "verifyCode":
                $email = $_POST['email'];
                $user_id    = $_POST['user_id'];
                $code  = $_POST['code'];

                $result = $db->verifyCode($email, $user_id, $code);
                if ($result) {
                    $response = ['success' => true, 'redirect' => "change-password.php?email=".$email."&&id=".$user_id];
                } else {
                    $response = ['success' => false, 'message' => "Invalid code"];
                }
                break;

            case "changePassword":
                $email     = $_POST['email'];
                $user_id        = $_POST['user_id'];
                $password  = $_POST['password'];
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
                    $response = ['success' => true, 'message' => $access_name." user successfully added"];
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
                    $response = ['success' => true, 'message' => $access_name." user successfully added"];
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


            // FISCAL YEAR PROCESSES**********************************************************
            case "AddFiscalYearForm":

                $year = $_POST['year'] ?? '';
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';

                $result = $db->AddFiscalYearForm($year, $start_date, $end_date, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Fiscal Year successfully added"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding Fiscal Year"];
                }
                break;

            case "UpdateFiscalYearForm":

                $fiscal_year_id = $_POST['fiscal_year_id'] ?? '';
                $year = $_POST['year'] ?? '';
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                $is_current = $_POST['is_current'] ?? '';
                $status = $_POST['status'] ?? '';

                $result = $db->UpdateFiscalYearForm($fiscal_year_id, $year, $start_date, $end_date, $is_current, $status, $operator_ID);
                if ($result === true) {
                    $response = ['success' => true, 'message' => "Fiscal Year successfully updated"];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => "Error adding Fiscal Year"];
                }
                break;
            // END FISCAL YEAR PROCESSES**********************************************************


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
            // END BUDGET ALLOCATION PROCESSES**********************************************************


            // PPMP PROCESSES**********************************************************
            case "AddPPMPForm":
                $user_id = $_POST['user_id'] ?? '';
                $ppmp_items = $_POST['ppmp_items'] ?? '[]';
                $ppmp_items = json_decode($ppmp_items, true);

                if (empty($user_id) || empty($ppmp_items)) {
                    $response = ['message' => 'Incomplete data provided. Please ensure all required fields (User ID, and Items) are filled in before proceeding.'];
                    break;
                }

                $result = $db->AddPPMPForm($user_id, $ppmp_items);

                if ($result === true) {
                    $response = ['success' => true, 'message' => 'PPMP and items successfully added.'];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => 'Error adding PPMP.'];
                }
                break;

            case "UpdatePPMPForm":
                $ppmp_id = $_POST['ppmp_id'] ?? '';
                $user_id = $_POST['user_id'] ?? '';
                $ppmp_items = $_POST['ppmp_items'] ?? '[]';
                $ppmp_items = json_decode($ppmp_items, true);

                if (empty($ppmp_id) || empty($user_id) || empty($ppmp_items)) {
                    $response = ['message' => 'Incomplete data provided. Please ensure all required fields (PPMP ID, User ID, and Items) are filled in before proceeding.'];
                    break;
                }

                $result = $db->UpdatePPMPForm($ppmp_id, $user_id, $ppmp_items);

                if ($result === true) {
                    $response = ['success' => true, 'message' => 'PPMP and items successfully updated.'];
                } elseif (is_string($result)) {
                    $response = ['message' => $result];
                } else {
                    $response = ['message' => 'Error updating PPMP.'];
                }
                break;

            case "GetPPMPItems":
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

                $response = ['success' => true, 'data' => $items];
                break;
            // END PPMP PROCESSES**********************************************************


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
