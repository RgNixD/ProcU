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
            // END USER MANAGEMENT PROCESSES**********************************************************

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
