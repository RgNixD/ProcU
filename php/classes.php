<?php

// Load environment variables and configurations
require_once __DIR__ . '/functions.php';
// To prevent direct access to this file
preventDirectAccess();

require_once 'db_config.php';
require_once 'init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!class_exists('PHPMailer\PHPMailer\Exception')) {
    require __DIR__ . '/../PHPMailer/src/Exception.php';
}
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
}
if (!class_exists('PHPMailer\PHPMailer\SMTP')) {
    require __DIR__ . '/../PHPMailer/src/SMTP.php';
}

class db_class extends db_connect
{

    public function __construct()
    {
        $this->connect();
    }
    public function getConnection()
    {
        return $this->conn;
    }

    public function getBaseURL()
    {
        return "http://localhost/Procurement%20System";
    }

    public function checkExistingUsername($username, $userId = null)
    {
        $query = "SELECT user_id FROM users WHERE username = ?";
        if ($userId !== null) {
            $query .= " AND user_id != ?";
        }

        $stmt = $this->conn->prepare($query);
        if ($userId !== null) {
            $stmt->bind_param("si", $username, $userId);
        } else {
            $stmt->bind_param("s", $username);
        }

        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function checkExistingPhone($phone, $userId = null)
    {
        $query = "SELECT user_id FROM users WHERE phone = ?";
        if ($userId !== null) {
            $query .= " AND user_id != ?";
        }

        $stmt = $this->conn->prepare($query);
        if ($userId !== null) {
            $stmt->bind_param("si", $phone, $userId);
        } else {
            $stmt->bind_param("s", $phone);
        }

        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function checkExistingEmail($email, $userId = null)
    {
        $query = "SELECT user_id FROM users WHERE email = ?";
        if ($userId !== null) {
            $query .= " AND user_id != ?";
        }

        $stmt = $this->conn->prepare($query);
        if ($userId !== null) {
            $stmt->bind_param("si", $email, $userId);
        } else {
            $stmt->bind_param("s", $email);
        }

        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function login($username, $password)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                return null;
            }

            if (!password_verify($password, $user['password'])) {
                return null;
            }

            $updateLogin = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateLogin->bind_param("i", $user['user_id']);
            $updateLogin->execute();
            $updateLogin->close();

            $stmt = $this->conn->prepare("SELECT * FROM user_access WHERE user_id = ? AND is_active = 1");
            $stmt->bind_param("i", $user['user_id']);
            $stmt->execute();
            $accessResult = $stmt->get_result();
            $permissions = $accessResult->fetch_assoc();
            $stmt->close();

            $sessionToken = bin2hex(random_bytes(32));
            $expiresAt = gmdate("Y-m-d H:i:s", strtotime("+2 hours"));


            $insertSession = $this->conn->prepare(
                "INSERT INTO user_sessions (user_id, session_token, expires_at, is_active, created_at) VALUES (?, ?, ?, 1, NOW())"
            );
            $insertSession->bind_param("iss", $user['user_id'], $sessionToken, $expiresAt);
            $insertSession->execute();
            $insertSession->close();

            return [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'email' => $user['email'],
                'is_active' => (int) $user['is_active'],
                'permissions' => $permissions,
                'session_token' => $sessionToken
            ];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return null;
        }
    }

    public function searchEmail($email)
    {
        $response = ['exists' => false];

        $query = $this->conn->prepare("SELECT user_id FROM users WHERE email = ? AND is_active = 1");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response['exists'] = true;
            $response['id'] = $row['user_id'];
        }

        $query->close();
        return $response;
    }

    public function getUserByIDandEmail($user_id, $email)
    {
        $query = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND user_id = ?");
        $query->bind_param("si", $email, $user_id);

        if ($query->execute()) {
            $result = $query->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc(); 
            }
        }
        return false;
    }

    public function sendVerificationCode($email, $id)
    {
        $key = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
        $response = ['success' => false, 'message' => 'User not found'];

        $user = $this->getUserByIDandEmail($id, $email);

        if ($user) {
            $firstname = $user['first_name'];
            $lastname  = $user['last_name'];

            $expiresAt = date("Y-m-d H:i:s", strtotime("+3 minutes"));

            $deleteOld = $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $deleteOld->bind_param("i", $id);
            $deleteOld->execute();
            $deleteOld->close();

            $insert = $this->conn->prepare("INSERT INTO password_resets (user_id, email, code, expires_at) VALUES (?, ?, ?, ?)");
            $insert->bind_param("isss", $id, $email, $key, $expiresAt);

            if ($insert->execute()) {
                $bodyContent = "
                    <p>Dear " . htmlspecialchars($firstname) . " " . htmlspecialchars($lastname) . ",</p>
                    <p>Your verification code is: <b>" . $key . "</b>. It will expire in 3 minutes.</p>
                    <p>To change your password, click 
                    <a href='" . $this->getBaseURL() . "/change-password.php?email=" . urlencode($email) . "&id=" . $id . "'>
                    here</a>.</p>";
                $this->sendEmail('Verification Code', $bodyContent, $email);

                $response = ['success' => true, 'message' => 'Verification code sent successfully'];
            }

            $insert->close();
        }

        return $response;
    }

    public function getVerificationCode($user_id, $email)
    {
        $query = $this->conn->prepare("SELECT * FROM password_resets WHERE user_id = ? AND email = ? ORDER BY id DESC LIMIT 1");
        $query->bind_param("is", $user_id, $email);

        if ($query->execute()) {
            $result = $query->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        return false;
    }

    public function verifyCode($email, $id, $code)
    {
        $now = date("Y-m-d H:i:s");
        $stmt = $this->conn->prepare("SELECT * FROM password_resets WHERE email = ? AND user_id = ? AND code = ? AND expires_at > ?");
        $stmt->bind_param("siss", $email, $id, $code, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            return true;
        }
        return false; 
    }

    public function changePassword($email, $user_id, $password, $cpassword)
    {
        if ($password !== $cpassword) {
            return "Password does not match";
        }

        $user = $this->getUserByIDandEmail($user_id, $email);

        if (!$user) {
            return "User not found";
        }

        if (password_verify($password, $user['password'])) {
            return "New password cannot be the same as the old password";
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $update = $this->conn->prepare("UPDATE users SET password = ? WHERE email = ? AND user_id = ?");
        $update->bind_param("ssi", $hashedPassword, $email, $user_id);

        if ($update->execute()) {
            $update->close();

            $delete = $this->conn->prepare("DELETE FROM password_resets WHERE email = ? AND user_id = ?");
            $delete->bind_param("si", $email, $user_id);
            $delete->execute();
            $delete->close();

            return true;
        } else {
            return "Error updating new password";
        }
    }

    // USER MANAGEMENT FUNCTION**********************************************************
    public function getUserById($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function getAllUsersWithAccess($userId = null)
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.email,
                u.first_name,
                u.last_name,
                u.phone,
                u.profile,
                u.created_at,
                ua.access_name,
                ua.can_create_ppmp,
                ua.can_approve_ppmp,
                ua.can_view_reports,
                ua.can_manage_budget
            FROM users u
            LEFT JOIN user_access ua ON u.user_id = ua.user_id
            WHERE u.is_active = 1
        ";

        if ($userId !== null) {
            $sql .= " AND u.user_id != ?";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if ($userId !== null) {
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    public function getAllSectors()
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.email,
                u.first_name,
                u.last_name,
                u.phone,
                u.profile,
                u.created_at,
                ua.access_name,
                ua.can_create_ppmp,
                ua.can_approve_ppmp,
                ua.can_view_reports,
                ua.can_manage_budget
            FROM users u
            LEFT JOIN user_access ua ON u.user_id = ua.user_id
            WHERE u.is_active = 1 AND ua.access_name = 'Sectors' ORDER BY u.created_at DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    public function getAvailableSectorsForHead()
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.email,
                u.first_name,
                u.last_name,
                u.phone,
                u.profile,
                u.created_at,
                ua.access_name,
                ua.can_create_ppmp,
                ua.can_approve_ppmp,
                ua.can_view_reports,
                ua.can_manage_budget
            FROM users u
            LEFT JOIN user_access ua ON u.user_id = ua.user_id
            WHERE 
                u.is_active = 1
                AND ua.access_name = 'Sectors'
                AND u.user_id NOT IN (
                    SELECT head_id 
                    FROM offices 
                    WHERE head_id IS NOT NULL 
                    AND is_active = 1
                )
            ORDER BY u.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    public function getUserPermissions($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user_access WHERE user_id = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $permissions = $result->fetch_assoc();
        $stmt->close();
        return $permissions ?: null;
    }

    public function validateSession($userId, $sessionToken)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM user_sessions WHERE user_id = ? AND session_token = ? AND is_active = 1 AND expires_at > UTC_TIMESTAMP() LIMIT 1"
        );
        $stmt->bind_param("is", $userId, $sessionToken);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();
        return $session ?: null;
    }

    public function updateProfilePicture($userId, $newFilename, $operatorId)
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return "User not found.";
        }

        $oldValue = $user['profile'] ?? null;

        // Update profile picture
        $stmt = $this->conn->prepare("UPDATE users SET profile = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newFilename, $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            return "Failed to update profile picture: " . $stmt->error;
        }
        $stmt->close();

        // Record audit log
        $this->recordActivityLog(
            $operatorId,
            "Updated profile picture",
            "users",
            $userId
        );

        return true;
    }

    public function updateInformation($userId, $username, $firstname, $lastname, $phone, $email, $operatorId)
    {
        // Get old data
        $oldData = $this->getUserById($userId);
        if (!$oldData) {
            return "User not found.";
        }

        // Check for duplicates
        if ($this->checkExistingUsername($username, $userId)) {
            return "Username already exists.";
        }
        if ($this->checkExistingPhone($phone, $userId)) {
            return "Phone number already exists.";
        }
        if ($this->checkExistingEmail($email, $userId)) {
            return "Email address already exists.";
        }

        // Update query
        $stmt = $this->conn->prepare("
        UPDATE users 
        SET username = ?, first_name = ?, last_name = ?, phone = ?, email = ?
        WHERE user_id = ?
    ");
        if (!$stmt) {
            return "Failed to prepare statement: " . $this->conn->error;
        }

        $stmt->bind_param("sssssi", $username, $firstname, $lastname, $phone, $email, $userId);

        if ($stmt->execute()) {
            $stmt->close();

            // Track changes
            $changes = [];
            $fields = [
                'username' => $username,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'phone' => $phone,
                'email' => $email
            ];

            foreach ($fields as $col => $newValue) {
                if ($oldData[$col] != $newValue) {
                    $changes[$col] = [
                        'old' => $oldData[$col],
                        'new' => $newValue
                    ];
                }
            }

            if (!empty($changes)) {
                $this->recordActivityLog(
                    $operatorId,
                    "Updated profile information",
                    "users",
                    $userId
                );
            }

            return true;
        } else {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update record: " . $error;
        }
    }

    public function updatePassword($userId, $oldPassword, $newPassword, $confirmPassword, $operatorId)
    {
        // Fetch stored password
        $storedPassword = null;
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($storedPassword);
        if (!$stmt->fetch()) {
            $stmt->close();
            return "User not found.";
        }
        $stmt->close();

        // Validate old password
        if (!password_verify($oldPassword, $storedPassword)) {
            return "Old password is incorrect.";
        }

        // Validate new password match
        if ($newPassword !== $confirmPassword) {
            return "New password and Confirm password do not match.";
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $update = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashedPassword, $userId);
        if (!$update->execute()) {
            $error = $update->error;
            $update->close();
            return "Failed to update password: " . $error;
        }
        $update->close();

        // Record audit log (no actual password stored)
        $this->recordActivityLog(
            $operatorId,
            "Updated user password",
            "users",
            $userId
        );

        return true;
    }

    public function AddUserForm($username, $first_name, $last_name, $phone, $email, $access_name, $operator_ID)
    {
        if ($this->checkExistingUsername($username)) {
            return "Username already exists.";
        }

        if ($this->checkExistingEmail($email)) {
            return "Email address already exists.";
        }

        if ($this->checkExistingPhone($phone)) {
            return "Phone number already exists.";
        }

        if ($access_name === 'Procurement Head') {
            $count = 0;
            $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM user_access WHERE access_name = 'Procurement Head' AND is_active = 1 ");
            $check_stmt->execute();
            $count = 0;
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                return "A user with the role 'Procurement Head' already exists.";
            }
        }

        if ($access_name === 'Budget Office') {
            $count = 0;
            $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM user_access WHERE access_name = 'Budget Office' AND is_active = 1 ");
            $check_stmt->execute();
            $count = 0;
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                return "Only 1 Budget Office role is allowed.";
            }
        }

        $defaultPassword = password_hash("PROC-123", PASSWORD_DEFAULT);
        $defaultProfile = 'avatar.png';

        $stmt = $this->conn->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, profile, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssss", $username, $defaultPassword, $email, $first_name, $last_name, $phone, $defaultProfile);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert user: " . $error;
        }

        $user_id = $stmt->insert_id;
        $stmt->close();

        $can_create_ppmp = 0;
        $can_approve_ppmp = 0;
        $can_view_reports = 0;
        $can_manage_budget = 0;

        switch ($access_name) {
        case 'Procurement Head':
            $can_create_ppmp = 0;
            $can_approve_ppmp = 1;
            $can_view_reports = 1;
            $can_manage_budget = 0;
            break;

        case 'Sectors':
            $can_create_ppmp = 1;
            $can_approve_ppmp = 0;
            $can_view_reports = 0;
            $can_manage_budget = 0;
            break;

        case 'Budget Office':
            $can_create_ppmp = 0;
            $can_approve_ppmp = 0;
            $can_view_reports = 0;
            $can_manage_budget = 1;
            break;
        }

        $description = $access_name . " role access";
        $access_stmt = $this->conn->prepare("
            INSERT INTO user_access (user_id, access_name, description, can_create_ppmp, can_approve_ppmp, can_view_reports, can_manage_budget, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $access_stmt->bind_param("issiiii", $user_id, $access_name, $description, $can_create_ppmp, $can_approve_ppmp, $can_view_reports, $can_manage_budget);

        if (!$access_stmt->execute()) {
            $error = $access_stmt->error;
            $access_stmt->close();
            return "Failed to insert user access: " . $error;
        }
        $access_stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new user ($access_name): $first_name $last_name",
            "users",
            $user_id
        );

        global $systemName;

        $bodyContent = "
            <p>Dear " . htmlspecialchars($first_name) . " " . htmlspecialchars($last_name) . ",</p>
            <p>Welcome to the " . htmlspecialchars($systemName) . "!</p>

            <p>Your account has been successfully created with the following details:</p>

            <ul>
                <li><b>Username:</b> " . htmlspecialchars($username) . "</li>
                <li><b>Email:</b> " . htmlspecialchars($email) . "</li>
                <li><b>Role:</b> " . htmlspecialchars($access_name) . "</li>
                <li><b>Default Password:</b> 123456</li>
            </ul>

            <p>You can log in to your account using the link below:</p>
            <p>
                <a href='" . $this->getBaseURL() . "/index.php'>Click here to log in</a>
            </p>

            <p>For security reasons, we recommend changing your password immediately after logging in.</p>

            <p>Best regards,<br>
            " . htmlspecialchars($systemName) . "</p>
        ";

        $this->sendEmail('Your Account Has Been Created', $bodyContent, $email);

        return true;
    }

    public function UpdateUserForm($user_id, $username, $first_name, $last_name, $phone, $email, $access_name, $operator_ID)
    {
        if ($this->checkExistingUsername($username, $user_id)) {
            return "Username already exists.";
        }

        if ($this->checkExistingEmail($email, $user_id)) {
            return "Email address already exists.";
        }

        if ($this->checkExistingPhone($phone, $user_id)) {
            return "Phone number already exists.";
        }

        if ($access_name === 'Procurement Head') {
            $count = 0;
            $check_stmt = $this->conn->prepare("
                SELECT COUNT(*) 
                FROM user_access 
                WHERE access_name = 'Procurement Head' 
                AND is_active = 1 
                AND user_id != ?
            ");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                return "A user with the role 'Procurement Head' already exists.";
            }
        }

        if ($access_name === 'Budget Office') {
            $count = 0;
            $check_stmt = $this->conn->prepare("
                SELECT COUNT(*) 
                FROM user_access 
                WHERE access_name = 'Budget Office' 
                AND is_active = 1 
                AND user_id != ?
            ");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                return "Only 1 Budget Office role is allowed.";
            }
        }

        $stmt = $this->conn->prepare("
            UPDATE users 
            SET username = ?, first_name = ?, last_name = ?, phone = ?, email = ? 
            WHERE user_id = ?
        ");
        $stmt->bind_param("sssssi", $username, $first_name, $last_name, $phone, $email, $user_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update user: " . $error;
        }
        $stmt->close();

        $can_create_ppmp = 0;
        $can_approve_ppmp = 0;
        $can_view_reports = 0;
        $can_manage_budget = 0;

        switch ($access_name) {
            case 'Procurement Head':
                $can_approve_ppmp = 1;
                $can_view_reports = 1;
                break;
            case 'Sectors':
                $can_create_ppmp = 1;
                break;
            case 'Budget Office':
                $can_manage_budget = 1;
                break;
        }

        $access_stmt = $this->conn->prepare("
            UPDATE user_access 
            SET access_name = ?, 
                description = CONCAT(?, ' role access'),
                can_create_ppmp = ?, 
                can_approve_ppmp = ?, 
                can_view_reports = ?, 
                can_manage_budget = ?
            WHERE user_id = ?
        ");
        $access_stmt->bind_param(
            "ssiiiii",
            $access_name,
            $access_name,
            $can_create_ppmp,
            $can_approve_ppmp,
            $can_view_reports,
            $can_manage_budget,
            $user_id
        );

        if (!$access_stmt->execute()) {
            $error = $access_stmt->error;
            $access_stmt->close();
            return "Failed to update user access: " . $error;
        }
        $access_stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated user ($access_name): $first_name $last_name",
            "users",
            $user_id
        );

        return true;
    }

    // END USER MANAGEMENT FUNCTION**********************************************************


    // CATEGORY FUNCTION**********************************************************
    public function getAllCategories()
    {
        $sql = "SELECT * FROM item_categories WHERE is_active = 1 ORDER BY category_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function AddCategoryForm($category_name, $category_code, $description, $operator_ID) {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM item_categories WHERE category_name = ? AND is_active = 1");
        $check_stmt->bind_param("s", $category_name);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Category name already exists.";
        }

        $count2 = 0;
        $check_code_stmt = $this->conn->prepare("SELECT COUNT(*) FROM item_categories WHERE category_code = ? AND is_active = 1 ");
        $check_code_stmt->bind_param("s", $category_code);
        $check_code_stmt->execute();
        $check_code_stmt->bind_result($count2);
        $check_code_stmt->fetch();
        $check_code_stmt->close();

        if ($count2 > 0) {
            return "Category code already exists.";
        }

        $stmt = $this->conn->prepare("INSERT INTO item_categories (category_name, category_code, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $category_name, $category_code, $description);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert category: " . $error;
        }

        $category_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new category: $category_name",
            "item_categories",
            $category_id
        );

        return true;
    }

    public function UpdateCategoryForm($category_id, $category_name, $category_code, $description, $is_active, $operator_ID) {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM item_categories WHERE category_name = ? AND is_active = 1 AND category_id != ?");
        $check_stmt->bind_param("si", $category_name, $category_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Category name already exists.";
        }

        $count2 = 0;
        $check_code_stmt = $this->conn->prepare("SELECT COUNT(*) FROM item_categories WHERE category_code = ? AND is_active = 1 AND category_id != ?");
        $check_code_stmt->bind_param("si", $category_code, $category_id);
        $check_code_stmt->execute();
        $check_code_stmt->bind_result($count2);
        $check_code_stmt->fetch();
        $check_code_stmt->close();

        if ($count2 > 0) {
            return "Category code already exists.";
        }

        $stmt = $this->conn->prepare("UPDATE item_categories SET category_name = ?, category_code = ?, description = ?, is_active = ? WHERE category_id = ?");
        $stmt->bind_param("sssii", $category_name, $category_code, $description, $is_active, $category_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update category: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated category: $category_name",
            "item_categories",
            $category_id
        );

        return true;
    }
    // END CATEGORY FUNCTION**********************************************************

    // SUB CATEGORY FUNCTIONS **********************************************************
    public function getAllSubCategories()
    {
        $sql = "
            SELECT sc.*, c.category_name 
            FROM sub_categories sc
            INNER JOIN item_categories c ON sc.category_id = c.category_id
            ORDER BY c.category_name, sc.sub_cat_name
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function getSubcategoriesByCategory($category_id)
    {
        $sql = "SELECT sub_category_id, sub_cat_name 
                FROM sub_categories 
                WHERE category_id = ? 
                ORDER BY sub_cat_name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $subcategories = [];
        while ($row = $result->fetch_assoc()) {
            $subcategories[] = $row;
        }

        return $subcategories;
    }

    public function AddSubCategoryForm($category_id, $sub_cat_name, $sub_cat_description, $operator_ID)
    {
        // Check for duplicate subcategory name under the same category
        $count = 0;
        $check_stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM sub_categories 
            WHERE sub_cat_name = ? AND category_id = ?
        ");
        $check_stmt->bind_param("si", $sub_cat_name, $category_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Sub-category name already exists for this category.";
        }

        // Insert new subcategory
        $stmt = $this->conn->prepare("
            INSERT INTO sub_categories (category_id, sub_cat_name, sub_cat_description)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $category_id, $sub_cat_name, $sub_cat_description);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert sub-category: " . $error;
        }

        $sub_category_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new sub-category: $sub_cat_name (Category ID: $category_id)",
            "sub_categories",
            $sub_category_id
        );

        return true;
    }

    public function UpdateSubCategoryForm($sub_category_id, $category_id, $sub_cat_name, $sub_cat_description, $operator_ID)
    {
        // Check for duplicate subcategory name under the same category
        $count = 0;
        $check_stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM sub_categories 
            WHERE sub_cat_name = ? AND category_id = ? AND sub_category_id != ?
        ");
        $check_stmt->bind_param("sii", $sub_cat_name, $category_id, $sub_category_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Sub-category name already exists for this category.";
        }

        // Update subcategory
        $stmt = $this->conn->prepare("
            UPDATE sub_categories 
            SET category_id = ?, sub_cat_name = ?, sub_cat_description = ?
            WHERE sub_category_id = ?
        ");
        $stmt->bind_param("issi", $category_id, $sub_cat_name, $sub_cat_description, $sub_category_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update sub-category: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated sub-category: $sub_cat_name (Category ID: $category_id)",
            "sub_categories",
            $sub_category_id
        );

        return true;
    }
    // END SUB CATEGORY FUNCTIONS **********************************************************


    // ITEM NAME FUNCTIONS **********************************************************
    public function getAllItemNames($item_id = null)
    {
        $sql = "
            SELECT 
                i.*, 
                sc.sub_category_id, 
                sc.sub_cat_name, 
                c.category_name
            FROM 
                item_names i
            INNER JOIN 
                sub_categories sc ON i.sub_category_id = sc.sub_category_id
            INNER JOIN 
                item_categories c ON sc.category_id = c.category_id
        ";

        if ($item_id !== null) {
            $sql .= " WHERE i.item_id = ?";
        }

        $sql .= " ORDER BY c.category_name, sc.sub_cat_name, i.item_name";

        $stmt = $this->conn->prepare($sql);

        if ($item_id !== null) {
            $stmt->bind_param("i", $item_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result;
    }

    public function getItemNamesBySubCategory(int $sub_category_id)
    {
        $sql = "
            SELECT 
                i.*, 
                sc.sub_cat_name
            FROM 
                item_names i
            INNER JOIN 
                sub_categories sc ON i.sub_category_id = sc.sub_category_id
            WHERE 
                i.sub_category_id = ?
            ORDER BY 
                i.item_name
        ";
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bind_param("i", $sub_category_id);
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function AddItemNameForm($sub_category_id, $item_name, $operator_ID) {
        $count = 0;
        $check_stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM item_names 
            WHERE item_name = ? AND sub_category_id = ?
        ");
        $check_stmt->bind_param("si", $item_name, $sub_category_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Item name already exists for this sub-category.";
        }

        $stmt = $this->conn->prepare("
            INSERT INTO item_names (sub_category_id, item_name)
            VALUES (?, ?)
        ");
        $stmt->bind_param("is", $sub_category_id, $item_name);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert item name: " . $error;
        }

        $item_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new item name: $item_name (Sub-category ID: $sub_category_id)",
            "item_names",
            $item_id
        );

        return true;
    }

    public function UpdateItemNameForm($item_name_id, $sub_category_id, $item_name, $operator_ID) {
        $count = 0;
        $check_stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM item_names 
            WHERE item_name = ? AND sub_category_id = ? AND item_name_id != ?
        ");
        $check_stmt->bind_param("sii", $item_name, $sub_category_id, $item_name_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Item name already exists for this sub-category.";
        }

        $stmt = $this->conn->prepare("
            UPDATE item_names 
            SET sub_category_id = ?, item_name = ?
            WHERE item_name_id = ?
        ");
        $stmt->bind_param("isi", $sub_category_id, $item_name, $item_name_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update item name: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated item name: $item_name (Sub-category ID: $sub_category_id)",
            "item_names",
            $item_name_id
        );

        return true;
    }
    // END ITEM NAME FUNCTIONS **********************************************************


    // FISCAL YEAR FUNCTIONS **********************************************************
    public function getAllFiscalYears()
    {
        $sql = "SELECT * FROM fiscal_years ORDER BY year DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function getCurrentFiscalYear()
    {
        $sql = "SELECT fiscal_year_id, year, start_date, end_date 
                FROM fiscal_years 
                WHERE is_current = 1 AND status = 1 
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function getCurrentFiscalYearId()
    {
        $fiscal_year_id = null;
        $stmt = $this->conn->prepare("SELECT fiscal_year_id FROM fiscal_years WHERE is_current = 1 AND status = 1 LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($fiscal_year_id);
        $stmt->fetch();
        $stmt->close();
        return $fiscal_year_id;
    }

    public function AddFiscalYearForm($year, $start_date, $end_date, $operator_ID)
    {
        $current_year = date('Y');
        if ($year < $current_year) {
            return "Fiscal Year cannot be in the past.";
        }

        if (strtotime($start_date) >= strtotime($end_date)) {
            return "Start Date must be earlier than End Date.";
        }
        
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM fiscal_years WHERE year = ?");
        $check_stmt->bind_param("i", $year);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Fiscal Year $year already exists.";
        }

        $stmt = $this->conn->prepare("
            INSERT INTO fiscal_years (year, start_date, end_date)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $year, $start_date, $end_date);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to add Fiscal Year: " . $error;
        }

        $fiscal_year_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new Fiscal Year: $year",
            "fiscal_years",
            $fiscal_year_id
        );

        return true;
    }

    public function UpdateFiscalYearForm($fiscal_year_id, $year, $start_date, $end_date, $is_current, $status, $operator_ID)
    {   
        $current_year = date('Y');
        if ($year < $current_year) {
            return "Fiscal Year cannot be in the past.";
        }

        if (strtotime($start_date) >= strtotime($end_date)) {
            return "Start Date must be earlier than End Date.";
        }

        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM fiscal_years WHERE year = ? AND fiscal_year_id != ?");
        $check_stmt->bind_param("ii", $year, $fiscal_year_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Fiscal Year $year already exists.";
        }

        $stmt = $this->conn->prepare("UPDATE fiscal_years SET year = ?, start_date = ?, end_date = ?, is_current = ?, status = ? WHERE fiscal_year_id = ?");
        $stmt->bind_param("issiii", $year, $start_date, $end_date, $is_current, $status, $fiscal_year_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update Fiscal Year: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated Fiscal Year: $year",
            "fiscal_years",
            $fiscal_year_id
        );

        return true;
    }
    // END FISCAL YEAR FUNCTIONS **********************************************************


    // OFFICES FUNCTIONS **********************************************************
    public function getAllOffices()
    {
        $sql = "
            SELECT 
                o.office_id,
                o.office_name,
                o.office_code,
                o.description,
                o.is_active,
                o.created_at,
                CONCAT(u.first_name, ' ', u.last_name) AS head_name,
                u.email AS head_email,
                u.phone AS head_phone,
                ua.access_name AS head_role
            FROM offices o
            LEFT JOIN users u ON o.head_id = u.user_id
            LEFT JOIN user_access ua ON u.user_id = ua.user_id
            WHERE o.is_active = 1
            ORDER BY o.office_name ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function getOfficeIdByHead($user_id)
    {
        $office_id = null;
        $stmt = $this->conn->prepare("SELECT office_id FROM offices WHERE head_id = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($office_id);
        $stmt->fetch();
        $stmt->close();
        return $office_id;
    }

    public function AddOfficeForm($office_name, $office_code, $head_id, $description, $operator_ID)
    {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM offices WHERE office_name = ? AND is_active = 1");
        $check_stmt->bind_param("s", $office_name);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Office name already exists.";
        }

        if (!empty($head_id)) {
            $existing_office = null;
            $check_head = $this->conn->prepare("SELECT office_name FROM offices WHERE head_id = ? AND is_active = 1");
            $check_head->bind_param("i", $head_id);
            $check_head->execute();
            $check_head->bind_result($existing_office);
            $head_exists = $check_head->fetch();
            $check_head->close();

            if ($head_exists) {
                return "This user is already assigned as the head of office: " . htmlspecialchars($existing_office);
            }
        }

        $stmt = $this->conn->prepare("INSERT INTO offices (office_name, office_code, head_id, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $office_name, $office_code, $head_id, $description);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert office: " . $error;
        }

        $office_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new office: $office_name",
            "offices",
            $office_id
        );

        return true;
    }

    public function UpdateOfficeForm($office_id, $office_name, $office_code, $head_id, $description, $operator_ID)
    {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM offices WHERE office_name = ? AND is_active = 1 AND office_id != ?");
        $check_stmt->bind_param("si", $office_name, $office_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Office name already exists.";
        }

        if (!empty($head_id)) {
            $existing_office = null;
            $check_head = $this->conn->prepare("SELECT office_name FROM offices WHERE head_id = ? AND is_active = 1 AND office_id != ? ");
            $check_head->bind_param("ii", $head_id, $office_id);
            $check_head->execute();
            $check_head->bind_result($existing_office);
            $head_exists = $check_head->fetch();
            $check_head->close();

            if ($head_exists) {
                return "This user is already assigned as the head of office: " . htmlspecialchars($existing_office);
            }
        }

        $stmt = $this->conn->prepare("UPDATE offices SET office_name = ?, office_code = ?, head_id = ?, description = ? WHERE office_id = ?");
        $stmt->bind_param("ssisi", $office_name, $office_code, $head_id, $description, $office_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update office: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated office details: $office_name",
            "offices",
            $office_id
        );

        return true;
    }
    // END OFFICES FUNCTIONS **********************************************************


    // BUDGET ALLOCATIONS FUNCTIONS **********************************************************
    public function getAllBudgetAllocations()
    {
        $sql = "SELECT 
                    ba.allocation_id,
                    o.office_id,
                    o.office_name,
                    o.office_code,
                    fy.fiscal_year_id,
                    fy.year AS fiscal_year,
                    fy.start_date,
                    fy.end_date,
                    ba.allocated_amount,
                    ba.remaining_amount,
                    ba.status,
                    ba.created_at,
                    CONCAT(u.first_name, ' ', u.last_name) AS head_name
                FROM budget_allocation AS ba
                LEFT JOIN offices AS o ON ba.office_id = o.office_id
                LEFT JOIN fiscal_years AS fy ON ba.fiscal_year_id = fy.fiscal_year_id
                LEFT JOIN users AS u ON o.head_id = u.user_id
                ORDER BY ba.created_at DESC";
        
        return $this->conn->query($sql);
    }

    public function getRemainingBudgetByUser($user_id)
    {
        $office_id = $this->getOfficeIdByHead($user_id);
        if (!$office_id) {
            return "No office found for this user."; 
        }

        $sqlFiscal = "SELECT fiscal_year_id FROM fiscal_years WHERE is_current = 1 AND status = 1 LIMIT 1";
        $resultFiscal = $this->conn->query($sqlFiscal);

        if (!$resultFiscal || $resultFiscal->num_rows === 0) {
            return "No active fiscal year found."; 
        }

        $fiscal_year = $resultFiscal->fetch_assoc();
        $fiscal_year_id = $fiscal_year['fiscal_year_id'];

        $stmt = $this->conn->prepare("
            SELECT remaining_amount 
            FROM budget_allocation 
            WHERE office_id = ? AND fiscal_year_id = ? 
            AND status = 'Approved'
            LIMIT 1
        ");
        $stmt->bind_param("ii", $office_id, $fiscal_year_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['remaining_amount'];
        } else {
            return "No approved budget allocation found for this office in the current fiscal year.";
        }
    }

    public function AddBudgetAllocation($office_id, $amount, $operator_ID)
    {
        $fiscal_year = $this->getCurrentFiscalYear();
        if (!$fiscal_year) {
            return "No active fiscal year found. Please set a current fiscal year first.";
        }

        $fiscal_year_id = $fiscal_year['fiscal_year_id'];

        $check_stmt = $this->conn->prepare("
            SELECT status FROM budget_allocation 
            WHERE office_id = ? AND fiscal_year_id = ? 
            ORDER BY allocation_id DESC LIMIT 1
        ");
        $check_stmt->bind_param("ii", $office_id, $fiscal_year_id);
        $check_stmt->execute();
        $existing_status = null;
        $check_stmt->bind_result($existing_status);
        $exists = $check_stmt->fetch();
        $check_stmt->close();

        if ($exists) {
            if (strtolower($existing_status) === 'approved') {
                return "This office already has an approved budget allocation for the current fiscal year.";
            } else {
                return "A budget allocation already exists for this office and fiscal year (Status: " . ucfirst($existing_status) . ").";
            }
        }

        $stmt = $this->conn->prepare("INSERT INTO budget_allocation (office_id, fiscal_year_id, allocated_amount, remaining_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidd", $office_id, $fiscal_year_id, $amount, $amount);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert budget allocation: " . $error;
        }

        $allocation_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added budget allocation (₱" . number_format($amount, 2) . ") for Office ID: $office_id (Fiscal Year: " . $fiscal_year['year'] . ")",
            "budget_allocation",
            $allocation_id
        );

        return true;
    }
    public function UpdateBudgetAllocation($allocation_id, $office_id, $amount, $status, $operator_ID)
    {
        $fiscal_year = $this->getCurrentFiscalYear();
        if (!$fiscal_year) {
            return "No active fiscal year found. Please set a current fiscal year first.";
        }

        $fiscal_year_id = $fiscal_year['fiscal_year_id'];

        $check_stmt = $this->conn->prepare("
        SELECT status 
        FROM budget_allocation 
        WHERE office_id = ? AND fiscal_year_id = ? AND allocation_id != ? 
        ORDER BY allocation_id DESC LIMIT 1
        ");
        $check_stmt->bind_param("iii", $office_id, $fiscal_year_id, $allocation_id);
        $check_stmt->execute();
        $existing_status = null;
        $check_stmt->bind_result($existing_status);
        $exists = $check_stmt->fetch();
        $check_stmt->close();

        if ($exists) {
            if (strtolower($existing_status) === 'approved') {
                return "This office already has an approved budget allocation for the current fiscal year.";
            } else {
                return "A budget allocation already exists for this office and fiscal year (Status: " . ucfirst($existing_status) . ").";
            }
        }

        $stmt = $this->conn->prepare("
            UPDATE budget_allocation 
            SET office_id = ?, allocated_amount = ?, remaining_amount = ?, status = ? 
            WHERE allocation_id = ?
        ");
        $stmt->bind_param("iddsi", $office_id, $amount, $amount, $status, $allocation_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update budget allocation: " . htmlspecialchars($error);
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated budget allocation (₱" . number_format($amount, 2) . ") for Office ID: $office_id (Fiscal Year: " . $fiscal_year['year'] . ")",
            "budget_allocation",
            $allocation_id
        );

        return true;
    }
    // END BUDGET ALLOCATIONS FUNCTIONS **********************************************************


    // PPMP FUNCTIONS **********************************************************
    public function getAllPPMPRecords($ppmp_id = null)
    {
        $sql = "
            SELECT 
                p.ppmp_id,
                p.ppmp_code,
                p.status,
                p.approval_date,
                p.total_amount,
                p.created_at,
                
                o.office_name,
                o.office_code,
                
                fy.year AS fiscal_year,
                
                u.first_name,
                u.last_name,
                u.email,
                u.phone
                
            FROM ppmp p
            INNER JOIN offices o ON p.office_id = o.office_id
            INNER JOIN fiscal_years fy ON p.fiscal_year_id = fy.fiscal_year_id
            INNER JOIN users u ON p.submitted_by = u.user_id
        ";

        if (!empty($ppmp_id)) {
            $sql .= " WHERE p.ppmp_id = ?";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        if (!empty($ppmp_id)) {
            $stmt->bind_param("i", $ppmp_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("Query execution failed: " . $this->conn->error);
        }

        return $result;
    }

    public function getPPMPHeaderDetails($ppmp_id)
    {
        $result = $this->getAllPPMPRecords($ppmp_id);

        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data;
        }
        
        return null;
    }

    public function getAllPPMPRecordsBySector($userId = null)
    {
        $sql = "
            SELECT 
                p.ppmp_id,
                p.ppmp_code,
                p.status,
                p.total_amount,
                p.approval_date,
                p.created_at,
                
                o.office_name,
                
                fy.year AS fiscal_year,
                
                u.first_name,
                u.last_name
                
            FROM ppmp p
            INNER JOIN offices o ON p.office_id = o.office_id
            INNER JOIN fiscal_years fy ON p.fiscal_year_id = fy.fiscal_year_id
            INNER JOIN users u ON p.submitted_by = u.user_id
        ";

        if (!empty($userId)) {
            $sql .= " WHERE p.submitted_by = ?"; 
        }
        

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        if (!empty($userId)) {
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("Query execution failed: " . $this->conn->error);
        }

        return $result;
    }

    public function getPPMPItemsById($ppmp_id)
    {
        $sql = "
                SELECT 
                    i.item_id,
                    i.category_id,
                    i.sub_category_id,
                    i.item_name_id,
                    inames.item_name,
                    i.item_description,
                    i.specifications,
                    i.mode_of_procurement,
                    i.quantity,
                    i.estimated_budget AS unit_cost,  
                    i.total_cost,
                    i.file_attachment,

                    i.procurement_start_date,
                    
                    i.procurement_method,
                    i.remarks,

                    i.bidding_date,
                    i.contract_signing_date,
                    i.source_of_funds,
                    i.pre_procurement_conference,
                    
                    c.category_name,
                    s.sub_cat_name
                    
                FROM ppmp_items i
                INNER JOIN item_categories c ON i.category_id = c.category_id
                LEFT JOIN sub_categories s ON i.sub_category_id = s.sub_category_id
                LEFT JOIN item_names inames ON i.item_name_id = inames.item_name_id
                WHERE i.ppmp_id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $ppmp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function AddPPMPForm($user_id, $items, $uploaded_files_map = [])
    {
        $this->conn->begin_transaction();

        try {

            $office_id = $this->getOfficeIdByHead($user_id);
            $fiscal_year_id = $this->getCurrentFiscalYearId();

            if (!$office_id) {
                return "Office not found or inactive for user ID: $user_id.";
            }

            if (!$fiscal_year_id) {
                return "No active fiscal year found. Please set a current fiscal year first.";
            }

            $total_amount = 0;
            foreach ($items as $it) {
                $total_amount += floatval($it['total_cost']);
            }

            $alloc_stmt = $this->conn->prepare("
                SELECT allocation_id, remaining_amount, status 
                FROM budget_allocation 
                WHERE office_id = ? AND fiscal_year_id = ? 
                ORDER BY allocation_id DESC 
                LIMIT 1
            ");
            $alloc_stmt->bind_param("ii", $office_id, $fiscal_year_id);
            $alloc_stmt->execute();
            $alloc_stmt->store_result();

            if ($alloc_stmt->num_rows === 0) {
                throw new Exception("No budget allocation found for this office in the current fiscal year.");
            }

            $allocation_id = null;
            $remaining_amount = null;
            $status = null;
            $alloc_stmt->bind_result($allocation_id, $remaining_amount, $status);
            $alloc_stmt->fetch();
            $alloc_stmt->close();

            if (strtolower($status) !== 'approved') {
                throw new Exception("Budget allocation exists but is not approved yet (Status: " . ucfirst($status) . ").");
            }

            if ($total_amount > $remaining_amount) {
                $remaining_display = is_numeric($remaining_amount) ? number_format((float)$remaining_amount, 2) : '0.00';
                throw new Exception("Insufficient budget. Remaining balance is ₱" . $remaining_display . 
                    " but PPMP total is ₱" . number_format($total_amount, 2) . ".");
            }

            $ppmp_code = "PPMP-" . date("Y") . "-" . strtoupper(uniqid());
            $stmt = $this->conn->prepare("
                INSERT INTO ppmp (office_id, fiscal_year_id, ppmp_code, total_amount, submitted_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("iisdi", $office_id, $fiscal_year_id, $ppmp_code, $total_amount, $user_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert PPMP: " . $stmt->error);
            }

            $ppmp_id = $stmt->insert_id;
            $stmt->close();

            $item_stmt = $this->conn->prepare("
                INSERT INTO ppmp_items (
                    ppmp_id, category_id, sub_category_id, item_name_id, item_description, specifications, quantity, 
                    mode_of_procurement, pre_procurement_conference, procurement_start_date, bidding_date, 
                    contract_signing_date, source_of_funds, estimated_budget, total_cost, remarks, file_attachment
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $temp_item_id = 0;

            foreach ($items as $it) {
        
                $item_files = $uploaded_files_map[$temp_item_id] ?? [];
                
                $file_attachment_string = !empty($item_files) ? implode(',', $item_files) : NULL;
                $category_id = intval($it['category_id'] ?? 0); 
                $sub_category_id = intval($it['sub_category_id'] ?? 0); 
                $item_name_id = intval($it['item_name_id']);

                $item_description = $it['item_description'] ?? '';
                $specifications = $it['specifications'];
                $quantity = intval($it['quantity']);
                $mode_of_procurement = $it['mode_of_procurement'];
                $pre_procurement_conference = $it['pre_procurement_conference'];
                
                $procurement_start_date = $it['procurement_start_date'] ?? '0000-00-00'; 
                $bidding_date = $it['bidding_date'] ?? '0000-00-00';
                $contract_signing_date = $it['contract_signing_date'] ?? '0000-00-00';
                
                $source_of_funds = $it['source_of_funds'];
                $estimated_budget = floatval($it['estimated_budget']); 
                $total_cost = floatval($it['total_cost']);
                $remarks = $it['remarks'];

                $item_stmt->bind_param(
                    "iiiississssssddss", 
                    $ppmp_id,
                    $category_id,
                    $sub_category_id,
                    $item_name_id,
                    $item_description,
                    $specifications,
                    $quantity,
                    $mode_of_procurement,
                    $pre_procurement_conference,
                    $procurement_start_date,
                    $bidding_date,
                    $contract_signing_date,
                    $source_of_funds,
                    $estimated_budget,
                    $total_cost, 
                    $remarks,
                    $file_attachment_string
                );

                if (!$item_stmt->execute()) {
                    throw new Exception("Failed to insert PPMP item: " . $item_stmt->error);
                }
                
                $temp_item_id++;
            }

            $item_stmt->close();

            $this->conn->commit();

            $this->recordActivityLog(
                $user_id,
                "Added new PPMP ($ppmp_code) with " . count($items) . " items.",
                "ppmp",
                $ppmp_id
            );

            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function UpdatePPMPForm($ppmp_id, $user_id, $items, $uploaded_files_map = [])
    {
        $this->conn->begin_transaction();

        try {

            $ppmp_stmt = $this->conn->prepare("
                SELECT p.office_id, p.fiscal_year_id, p.total_amount, b.remaining_amount, b.status, b.allocation_id
                FROM ppmp p
                INNER JOIN budget_allocation b ON p.office_id = b.office_id AND p.fiscal_year_id = b.fiscal_year_id
                WHERE p.ppmp_id = ?
                ORDER BY b.allocation_id DESC
                LIMIT 1
            ");
            $ppmp_stmt->bind_param("i", $ppmp_id);
            $ppmp_stmt->execute();
            $ppmp_stmt->store_result();

            if ($ppmp_stmt->num_rows === 0) {
                throw new Exception("PPMP record not found for update.");
            }

            $office_id = $fiscal_year_id = $current_total = $remaining_amount = $status = $allocation_id = null;
            $ppmp_stmt->bind_result($office_id, $fiscal_year_id, $current_total, $remaining_amount, $status, $allocation_id);
            $ppmp_stmt->fetch();
            $ppmp_stmt->close();

            if (strtolower($status) !== 'approved') {
                throw new Exception("Budget allocation exists but is not approved yet (Status: " . ucfirst($status) . ").");
            }
            
            $new_total = 0;
            foreach ($items as $it) {
                $new_total += floatval($it['total_cost']);
            }

            if ($new_total > $remaining_amount + $current_total) {
                $available_display = number_format((float)$remaining_amount + (float)$current_total, 2);
                throw new Exception("Insufficient budget. Available budget is ₱" . $available_display .
                    ", but new PPMP total is ₱" . number_format($new_total, 2) . ".");
            }

            $update_stmt = $this->conn->prepare("
                UPDATE ppmp 
                SET total_amount = ?
                WHERE submitted_by = ? AND ppmp_id = ?
            ");
            $update_stmt->bind_param("dii", $new_total, $user_id, $ppmp_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update PPMP record: " . $update_stmt->error);
            }
            $update_stmt->close();

            $delete_stmt = $this->conn->prepare("DELETE FROM ppmp_items WHERE ppmp_id = ?");
            $delete_stmt->bind_param("i", $ppmp_id);
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to delete old PPMP items: " . $delete_stmt->error);
            }
            $delete_stmt->close();

            $item_stmt = $this->conn->prepare("
                INSERT INTO ppmp_items (
                    ppmp_id, category_id, sub_category_id, item_name_id, item_description, specifications, quantity, 
                    mode_of_procurement, pre_procurement_conference, procurement_start_date, bidding_date, 
                    contract_signing_date, source_of_funds, estimated_budget, total_cost, remarks, file_attachment
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $temp_item_id = 0; 

            foreach ($items as $it) {
                $file_attachment_string = ''; 

                $is_new = isset($it['is_new']) && $it['is_new'] === true; 

                if ($is_new) {
                    $item_files = $uploaded_files_map[$temp_item_id] ?? [];
                    $file_attachment_string = !empty($item_files) ? implode(',', $item_files) : ''; 
                    $temp_item_id++;
                } else {
                    $file_attachment_string = $it['file_attachment'] ?? ''; 
                }

                $category_id = intval($it['category_id'] ?? 0); 
                $sub_category_id = intval($it['sub_category_id'] ?? 0); 
                $item_name_id = intval($it['item_name_id']);

                $item_description = $it['item_description'] ?? '';
                $specifications = $it['specifications'] ?? '';
                $quantity = intval($it['quantity']);
                $mode_of_procurement = $it['mode_of_procurement'] ?? '';
                $pre_procurement_conference = $it['pre_procurement_conference'] ?? '';
                
                $procurement_start_date = $it['procurement_start_date'] ?? '0000-00-00'; 
                $bidding_date = $it['bidding_date'] ?? '0000-00-00';
                $contract_signing_date = $it['contract_signing_date'] ?? '0000-00-00';
                
                $source_of_funds = $it['source_of_funds'] ?? '';
                $estimated_budget = floatval($it['estimated_budget']); 
                $total_cost = floatval($it['total_cost']);
                $remarks = $it['remarks'] ?? '';

                $item_stmt->bind_param(
                    "iiiississssssddss", 
                    $ppmp_id,
                    $category_id,
                    $sub_category_id,
                    $item_name_id,
                    $item_description,
                    $specifications,
                    $quantity,
                    $mode_of_procurement,
                    $pre_procurement_conference,
                    $procurement_start_date,
                    $bidding_date,
                    $contract_signing_date,
                    $source_of_funds,
                    $estimated_budget,
                    $total_cost, 
                    $remarks,
                    $file_attachment_string
                );

                if (!$item_stmt->execute()) {
                    throw new Exception("Failed to insert updated PPMP item: " . $item_stmt->error);
                }
            }

            $item_stmt->close();

            $new_remaining = $remaining_amount + $current_total - $new_total;
            $budget_update_stmt = $this->conn->prepare("
                UPDATE budget_allocation 
                SET remaining_amount = ? 
                WHERE allocation_id = ?
            ");
            $budget_update_stmt->bind_param("di", $new_remaining, $allocation_id);
            if (!$budget_update_stmt->execute()) {
                throw new Exception("Failed to update remaining budget: " . $budget_update_stmt->error);
            }
            $budget_update_stmt->close();
            
            $this->conn->commit();

            $this->recordActivityLog(
                $user_id,
                "Updated PPMP (#$ppmp_id) with " . count($items) . " total items.",
                "ppmp",
                $ppmp_id
            );

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function deletePPMPItem($item_id)
    {
        $sql = "DELETE FROM ppmp_items WHERE item_id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("Prepare failed in deletePPMPItem: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $item_id);
        
        $result = $stmt->execute();
        
        $success = $result && $stmt->affected_rows > 0;
        
        $stmt->close();
        
        return $success;
    }
    // END PPMP FUNCTIONS **********************************************************


    // DELETE RECORD FUNCTION**********************************************************
    public function DeleteRecords($table, $delete_column, $delete_IDs, $operator_ID, $loggedInUserType, $audit_ID = "")
    {
        $placeholders = implode(',', array_fill(0, count($delete_IDs), '?'));
        $query = $this->conn->prepare("DELETE FROM $table WHERE $delete_column IN ($placeholders)");

        $types = str_repeat('i', count($delete_IDs));
        $query->bind_param($types, ...$delete_IDs);

        if ($query->execute()) {
            $query->close();
            return true;
        } else {
            error_log("Error deleting records: " . $query->error);
            return false;
        }
    }
    public function DeleteRecordForm($table, $delete_column, $delete_IDs, $operator_ID, $loggedInUserType, $audit_ID = "")
    {
        if (is_array($delete_IDs)) {
            $placeholders = implode(',', array_fill(0, count($delete_IDs), '?'));
            $query = $this->conn->prepare("DELETE FROM $table WHERE $delete_column IN ($placeholders)");
            $types = str_repeat('i', count($delete_IDs));
            $query->bind_param($types, ...$delete_IDs);
        } else {
            $query = $this->conn->prepare("DELETE FROM $table WHERE $delete_column = ?");
            $query->bind_param("i", $delete_IDs);
        }

        $this->conn->begin_transaction();

        try {
            if (!$query->execute()) {
                throw new Exception("Error executing query: " . $query->error);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error deleting records: " . $e->getMessage());
            return false;
        }
    }
    // END DELETE RECORD FUNCTION**********************************************************

    // ACTIVITY LOGS FUNCTION**********************************************************
    public function getActivityLogs()
    {
        $query = "SELECT * FROM `activity_logs` ORDER BY created_at DESC";
        return $this->conn->query($query);
    }
    public function recordActivityLog($userId, $action, $tableName, $recordId)
    {
        $stmt = $this->conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, ?, ?, ?) ");
        $stmt->bind_param("issi", $userId, $action, $tableName, $recordId);
        $stmt->execute();
        $stmt->close();
    }
    // END ACTIVITY LOGS FUNCTION**********************************************************

    // EMAIL NOTIFICATION**********************************************************
    private function getEmailTemplate($bodyContent, $system_name)
    {
        return '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email Notification</title>
                <style>
                    body {
                        font-family: \'Arial\', sans-serif;
                        background-color: #f4f5f6;
                        color: #333333;
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    .container {
                        max-width: 600px;
                        margin: 30px auto;
                        background-color: #ffffff;
                        border: 3px solid #a83232;
                        border-radius: 12px;
                        overflow: hidden;
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
                    }
                    .header {
                        background-color: #a83232;
                        color: #ffffff;
                        text-align: center;
                        padding: 25px 20px;
                        font-size: 28px;
                        font-weight: bold;
                        letter-spacing: 1px;
                    }
                    .content {
                        padding: 30px 25px;
                        background-color: #ffffff;
                        color: #333333;
                        line-height: 1.8;
                    }
                    .highlight {
                        color: #CB3635;
                        font-weight: bold;
                    }
                    .footer {
                        background-color: #a83232;
                        color: #ffffff;
                        text-align: center;
                        padding: 15px;
                        font-size: 14px;
                        border-top: 3px solid black;
                    }
                    .footer a {
                        color: #ffffff;
                        text-decoration: none;
                        font-weight: bold;
                    }
                    .footer a:hover {
                        text-decoration: underline;
                    }
                    @media (max-width: 600px) {
                        .container {
                            width: 100%;
                            margin: 10px;
                        }
                        .header {
                            font-size: 24px;
                            padding: 20px;
                        }
                        .content {
                            padding: 20px;
                            font-size: 15px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">' . $system_name . '</div>
                    <div class="content">
                        ' . $bodyContent . '
                        <p class="highlight">If you have any questions, feel free to contact us.</p>
                    </div>
                    <div class="footer">
                        <p><strong>NOTE:</strong> This is a system-generated email. Please do not reply directly.</p>
                        <p>Need help? <a href="mailto:support@yourdomain.com">Contact Support</a></p>
                    </div>
                </div>
            </body>
            </html>';
    }

    public function sendEmail($subject, $messageContent, $recipientEmail)
    {

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'thesisprojects2025@gmail.com';
            $mail->Password = 'gpsjdlzwfkgqbqay';
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Recipients
            $mail->setFrom('thesisprojects2025@gmail.com', 'PROCUREMENT PLANNING AND CONSOLIDATION SYSTEM');
            $mail->addAddress($recipientEmail);
            $mail->addReplyTo('thesisprojects2025@gmail.com');

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $this->getEmailTemplate($messageContent, 'PROCUREMENT PLANNING AND CONSOLIDATION SYSTEM');

            $mail->send();
        } catch (Exception $e) {
            $mail->ErrorInfo;
        }
    }
    // END EMAIL NOTIFICATION**********************************************************

    public function logoutUser(int $userId, string $sessionToken): bool
    {
        $stmt = $this->conn->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_token = ?");
        $stmt->bind_param("is", $userId, $sessionToken);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

}
