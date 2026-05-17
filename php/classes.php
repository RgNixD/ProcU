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
            $lastname = $user['last_name'];

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
    public function getUserById($userId)
    {
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
            // "SELECT * FROM user_sessions WHERE user_id = ? AND session_token = ? AND is_active = 1 AND expires_at > UTC_TIMESTAMP() LIMIT 1"
            "SELECT * FROM user_sessions WHERE user_id = ? AND session_token = ? AND is_active = 1 LIMIT 1"
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

        if (!password_verify($oldPassword, $storedPassword)) {
            return "Old password is incorrect.";
        }

        if ($newPassword !== $confirmPassword) {
            return "New password and Confirm password do not match.";
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashedPassword, $userId);
        if (!$update->execute()) {
            $error = $update->error;
            $update->close();
            return "Failed to update password: " . $error;
        }
        $update->close();

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

        $defaultPassword = password_hash("PROC123", PASSWORD_DEFAULT);
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

    public function AddCategoryForm($category_name, $category_code, $description, $operator_ID)
    {
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

    public function UpdateCategoryForm($category_id, $category_name, $category_code, $description, $is_active, $operator_ID)
    {
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

    public function AddItemNameForm($sub_category_id, $item_name, $operator_ID)
    {
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

    public function UpdateItemNameForm($item_name_id, $sub_category_id, $item_name, $operator_ID)
    {
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

    public function getFiscalYears($fiscalYearId = null)
    {
        $sql = "
            SELECT fiscal_year_id, year, start_date, end_date, status, is_lock, created_at
            FROM fiscal_years
        ";

        if (!empty($fiscalYearId)) {
            $sql .= " WHERE fiscal_year_id = ? ";
        }

        $sql .= " ORDER BY year ASC ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        if (!empty($fiscalYearId)) {
            $stmt->bind_param("i", $fiscalYearId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!empty($fiscalYearId)) {
            return $result->num_rows > 0 ? $result->fetch_assoc() : null;
        }

        return $result;
    }

    public function getFiscalYearById($fiscalYearId)
    {
        return $this->getFiscalYears($fiscalYearId);
    }

    public function getCurrentFiscalYear($requireUnlocked = false)
    {
        $sql = "
            SELECT fiscal_year_id, year, start_date, end_date, status, is_lock, created_at
            FROM fiscal_years
            WHERE status = 1
        ";

        if ($requireUnlocked) {
            $sql .= " AND is_lock = 0 ";
        }

        $sql .= " ORDER BY year DESC LIMIT 1 ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function getCurrentFiscalYearId($requireUnlocked = false)
    {
        $currentFY = $this->getCurrentFiscalYear($requireUnlocked);

        return $currentFY ? (int) $currentFY['fiscal_year_id'] : null;
    }

    public function getFiscalYearName($fiscalYearId)
    {
        $fiscalYear = $this->getFiscalYearById($fiscalYearId);

        return $fiscalYear ? (string) $fiscalYear['year'] : '';
    }

    public function validateFiscalYearForSubmission()
    {
        $currentFY = $this->getCurrentFiscalYear();

        if (!$currentFY) {
            throw new Exception("No active fiscal year found.");
        }

        if ((int) $currentFY['is_lock'] === 1) {
            throw new Exception("Fiscal Year {$currentFY['year']} is currently locked.");
        }

        return $currentFY;
    }

    public function validateFiscalYearForConsolidation()
    {
        return $this->validateFiscalYearForSubmission();
    }

    public function createFiscalYear($year, $startDate, $endDate, $operatorId)
    {
        $currentYear = (int) date('Y');

        if ((int) $year < $currentYear) {
            return "Fiscal Year cannot be in the past.";
        }

        if (strtotime($startDate) >= strtotime($endDate)) {
            return "Start Date must be earlier than End Date.";
        }

        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total
            FROM fiscal_years
            WHERE year = ?
        ");

        $stmt->bind_param("i", $year);
        $stmt->execute();

        $count = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        if ($count > 0) {
            return "Fiscal Year {$year} already exists.";
        }

        $stmt = $this->conn->prepare("
            INSERT INTO fiscal_years (
                year,
                start_date,
                end_date,
                status,
                is_lock
            )
            VALUES (?, ?, ?, 0, 0)
        ");

        $stmt->bind_param("iss", $year, $startDate, $endDate);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            return "Failed to create Fiscal Year: {$error}";
        }

        $fiscalYearId = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operatorId,
            "Created Fiscal Year {$year}",
            "fiscal_years",
            $fiscalYearId
        );

        return true;
    }

    public function updateFiscalYear($fiscalYearId, $year, $startDate, $endDate, $status, $isLock, $operatorId)
    {
        $currentYear = (int) date('Y');

        if ((int) $year < $currentYear) {
            return "Fiscal Year cannot be in the past.";
        }

        if (strtotime($startDate) >= strtotime($endDate)) {
            return "Start Date must be earlier than End Date.";
        }

        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total
            FROM fiscal_years
            WHERE year = ? AND fiscal_year_id != ?
        ");

        if (!$stmt) {
            return "Database error: " . $this->conn->error;
        }

        $stmt->bind_param("ii", $year, $fiscalYearId);
        $stmt->execute();

        $count = (int) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        if ($count > 0) {
            return "Fiscal Year {$year} already exists.";
        }

        $this->conn->begin_transaction();

        try {

            if ((int) $status === 1) {
                $deactivateStmt = $this->conn->prepare("
                    UPDATE fiscal_years
                    SET status = 0
                    WHERE fiscal_year_id != ?
                ");

                if (!$deactivateStmt) {
                    throw new Exception($this->conn->error);
                }

                $deactivateStmt->bind_param("i", $fiscalYearId);
                $deactivateStmt->execute();
                $deactivateStmt->close();
            }

            $stmt = $this->conn->prepare("
                UPDATE fiscal_years
                SET year = ?, start_date = ?, end_date = ?, status = ?, is_lock = ?
                WHERE fiscal_year_id = ?
            ");

            if (!$stmt) {
                throw new Exception($this->conn->error);
            }

            $stmt->bind_param("issiii", $year, $startDate, $endDate, $status, $isLock, $fiscalYearId);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $stmt->close();
            $this->conn->commit();

            $this->recordActivityLog(
                $operatorId,
                "Updated Fiscal Year {$year}",
                "fiscal_years",
                $fiscalYearId
            );

            return true;

        } catch (Exception $e) {

            $this->conn->rollback();

            return "Failed to update Fiscal Year: " . $e->getMessage();
        }
    }
    // END FISCAL YEAR FUNCTIONS **********************************************************


    // ANNUAL BUDGET FUNCTIONS **********************************************************

    public function getAnnualBudgets()
    {
        $sql = "
            SELECT 
                ab.annual_budget_id,
                ab.total_budget_amount,
                ab.remaining_budget_amount,
                ab.created_at AS date_added,
                ab.updated_at AS last_updated,
                fy.fiscal_year_id,
                fy.year AS fiscal_year,
                CONCAT(submit_user.first_name, ' ', submit_user.last_name) AS submitted_by_name,
                CONCAT(update_user.first_name, ' ', update_user.last_name) AS updated_by_name
            FROM annual_budget ab
            LEFT JOIN fiscal_years fy ON ab.fiscal_year_id = fy.fiscal_year_id
            LEFT JOIN users submit_user ON ab.submitted_by_user_id = submit_user.user_id
            LEFT JOIN users update_user ON ab.updated_by_user_id = update_user.user_id
            ORDER BY fy.year DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function getAnnualBudgetByFiscalYear($fiscalYearId)
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM annual_budget
            WHERE fiscal_year_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $fiscalYearId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function getTotalAllocatedAmountByFiscalYear($fiscal_year_id)
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(allocated_amount), 0) AS total_allocated 
            FROM budget_allocation 
            WHERE fiscal_year_id = ?
            AND status = 'Approved'
        ");

        if (!$stmt) {
            error_log("getTotalAllocatedAmountByFiscalYear prepare failed: " . $this->conn->error);
            return ['total_allocated' => 0.00];
        }

        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $allocated_sum = $result->fetch_assoc();
        $stmt->close();

        $raw_value = $allocated_sum['total_allocated'] ?? '0.00';

        $total_allocated = floatval($raw_value);


        return [
            'total_allocated' => $total_allocated
        ];
    }

    private function getAnnualBudgetDetails($fiscal_year_id)
    {
        $stmt = $this->conn->prepare("
            SELECT total_budget_amount 
            FROM annual_budget 
            WHERE fiscal_year_id = ?
        ");
        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $annual_budget = $result->fetch_assoc();
        $stmt->close();

        if (!$annual_budget) {
            return null;
        }

        $total_annual_budget = $annual_budget['total_budget_amount'];

        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(allocated_amount), 0) AS total_allocated 
            FROM budget_allocation 
            WHERE fiscal_year_id = ?
            AND status = 'Approved'
        ");
        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $allocated_sum = $result->fetch_assoc();
        $stmt->close();

        $total_allocated = $allocated_sum['total_allocated'];

        return [
            'total_annual_budget' => floatval($total_annual_budget),
            'total_allocated_so_far' => floatval($total_allocated)
        ];
    }

    public function AddAnnualBudget($fiscal_year_id, $total_budget, $operator_ID)
    {

        if (empty($total_budget) || !is_numeric($total_budget)) {
            return "Budget amount is required and must be a valid number.";
        }

        $total_budget = floatval($total_budget);

        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM annual_budget WHERE fiscal_year_id = ?");
        $check_stmt->bind_param("i", $fiscal_year_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "An annual budget for the selected fiscal year already exists.";
        }

        $stmt = $this->conn->prepare("
            INSERT INTO annual_budget (fiscal_year_id, total_budget_amount, remaining_budget_amount, submitted_by_user_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iddi", $fiscal_year_id, $total_budget, $total_budget, $operator_ID);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to add Annual Budget: " . $error;
        }

        $annual_budget_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new Annual Budget for Fiscal Year ID: $fiscal_year_id",
            "annual_budget",
            $annual_budget_id
        );

        return true;
    }

    public function UpdateAnnualBudget($annual_budget_id, $fiscal_year_id, $total_budget, $operator_ID)
    {

        if (empty($total_budget) || !is_numeric($total_budget)) {
            return "Budget amount is required and must be a valid number.";
        }

        $total_budget = floatval($total_budget);

        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM annual_budget WHERE fiscal_year_id = ? AND annual_budget_id != ?");
        $check_stmt->bind_param("ii", $fiscal_year_id, $annual_budget_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "An annual budget for the selected fiscal year already exists.";
        }

        $stmt = $this->conn->prepare("
            UPDATE annual_budget 
            SET fiscal_year_id = ?, total_budget_amount = ?, remaining_budget_amount = ?, updated_by_user_id = ?
            WHERE annual_budget_id = ?
        ");
        $stmt->bind_param("iddii", $fiscal_year_id, $total_budget, $total_budget, $operator_ID, $annual_budget_id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update Annual Budget: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated Annual Budget ID: $annual_budget_id for Fiscal Year ID: $fiscal_year_id",
            "annual_budget",
            $annual_budget_id
        );

        return true;
    }

    public function getDepartmentAllocationsByAnnualBudget($annual_budget_id)
    {
        $stmt = $this->conn->prepare("SELECT fiscal_year_id, total_budget_amount FROM annual_budget WHERE annual_budget_id = ?");
        $stmt->bind_param("i", $annual_budget_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $annual_budget_data = $result->fetch_assoc();
        $stmt->close();

        if (!$annual_budget_data) {
            return ['error' => 'Annual Budget not found.'];
        }

        $fiscal_year_id = $annual_budget_data['fiscal_year_id'];
        $total_annual_budget = floatval($annual_budget_data['total_budget_amount']);

        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(allocated_amount), 0) AS total_allocated_sum
            FROM budget_allocation 
            WHERE fiscal_year_id = ?
            AND status = 'Approved'
        ");
        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();
        $sum_result = $stmt->get_result();
        $sum_data = $sum_result->fetch_assoc();
        $stmt->close();

        $total_allocated_sum = floatval($sum_data['total_allocated_sum']);

        $accurate_remaining_balance = $total_annual_budget - $total_allocated_sum;

        $sql = "
            SELECT 
                ba.allocated_amount,
                ba.remaining_amount AS office_remaining_amount,
                ba.status,
                ba.created_at AS date_allocated,
                o.office_name,
                o.office_code,
                CONCAT(u.first_name, ' ', u.last_name) AS office_head_name,
                u.email AS office_head_email
            FROM budget_allocation ba
            LEFT JOIN offices o ON ba.office_id = o.office_id
            LEFT JOIN users u ON o.head_id = u.user_id 
            WHERE ba.fiscal_year_id = ? AND ba.status = 'Approved'
            ORDER BY ba.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();
        $allocations_result = $stmt->get_result();

        $allocations = [];
        while ($row = $allocations_result->fetch_assoc()) {
            $allocations[] = $row;
        }
        $stmt->close();

        return [
            'total_budget_amount' => $total_annual_budget,
            'remaining_budget_amount' => $accurate_remaining_balance,
            'total_allocated_sum' => $total_allocated_sum,
            'fiscal_year_id' => $fiscal_year_id,
            'allocations' => $allocations
        ];
    }
    // END ANNUAL BUDGET FUNCTIONS **********************************************************


    // BUDGET ALLOCATIONS FUNCTIONS **********************************************************

    public function getAllBudgetAllocations($fiscal_year_id = null)
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
                LEFT JOIN users AS u ON o.head_id = u.user_id";

        if ($fiscal_year_id !== null && $fiscal_year_id !== '') {
            $sql .= " WHERE ba.fiscal_year_id = ?";
        }

        $sql .= " ORDER BY ba.created_at DESC";

        if ($fiscal_year_id !== null && $fiscal_year_id !== '') {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $fiscal_year_id);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $this->conn->query($sql);
    }

    public function getAllBudgetAllocationsForFiltering($office_id = null, $fiscal_year_id = null)
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
                WHERE 1";

        $params = [];
        $types = "";

        if (!empty($office_id)) {
            $sql .= " AND ba.office_id = ? ";
            $types .= "i";
            $params[] = $office_id;
        }

        if (!empty($fiscal_year_id)) {
            $sql .= " AND ba.fiscal_year_id = ? ";
            $types .= "i";
            $params[] = $fiscal_year_id;
        }

        $sql .= " ORDER BY ba.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public function getRemainingBudgetByUser($user_id)
    {
        $office_id = $this->getOfficeIdByHead($user_id);
        if (!$office_id) {
            return "No office found for this user.";
        }

        $sqlFiscal = "SELECT fiscal_year_id FROM fiscal_years WHERE status = 1 LIMIT 1";
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

    public function getLatestPPMPStatusByUser($user_id)
    {
        $office_id = $this->getOfficeIdByHead($user_id);
        if (!$office_id) {
            return "No office found for this user.";
        }

        $sqlFiscal = "SELECT fiscal_year_id FROM fiscal_years WHERE status = 1 LIMIT 1";
        $resultFiscal = $this->conn->query($sqlFiscal);

        if (!$resultFiscal || $resultFiscal->num_rows === 0) {
            return null;
        }

        $fiscal_year = $resultFiscal->fetch_assoc();
        $fiscal_year_id = $fiscal_year['fiscal_year_id'];

        $stmt = $this->conn->prepare("SELECT status FROM ppmp WHERE office_id = ? AND fiscal_year_id = ? ORDER BY ppmp_id DESC LIMIT 1");
        $stmt->bind_param("ii", $office_id, $fiscal_year_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['status'];
        }

        return null;
    }

    public function getOfficeBudgetOverview($userId)
    {
        $officeId = $this->getOfficeIdByHead($userId);

        if (!$officeId) {
            return null;
        }

        $currentFY = $this->getCurrentFiscalYear(true);
        if (!$currentFY) {
            return null;
        }

        $fiscal_year_id = (int) $currentFY['fiscal_year_id'];

        $sqlAlloc = "
            SELECT allocated_amount, remaining_amount
            FROM budget_allocation
            WHERE office_id = ?
            AND fiscal_year_id = ?
            ORDER BY allocation_id DESC
            LIMIT 1
        ";

        $allocatedAmount = 0;
        $remainingAmount = 0;

        $stmt2 = $this->conn->prepare($sqlAlloc);
        $stmt2->bind_param("ii", $officeId, $fiscal_year_id);
        $stmt2->execute();
        $stmt2->bind_result($allocatedAmount, $remainingAmount);
        $stmt2->fetch();
        $stmt2->close();

        $sqlPPMP = "
            SELECT 
                COALESCE(pv.total_amount, 0) AS total_amount,
                pv.approved_at
            FROM ppmp p
            INNER JOIN ppmp_versions pv 
                ON pv.ppmp_version_id = p.current_version_id
            WHERE p.office_id = ?
            AND p.fiscal_year_id = ?
            AND pv.status != 'Archived'
            ORDER BY p.ppmp_id DESC
            LIMIT 1
        ";

        $ppmpTotal = 0;
        $ppmpApprovalDate = null;

        $stmt3 = $this->conn->prepare($sqlPPMP);
        $stmt3->bind_param("ii", $officeId, $fiscal_year_id);
        $stmt3->execute();
        $stmt3->bind_result($ppmpTotal, $ppmpApprovalDate);
        $stmt3->fetch();
        $stmt3->close();

        return [
            "office_id" => $officeId,
            "office_name" => $this->getOfficeName($officeId),
            "fiscal_year" => $currentFY['year'],
            "allocated_amount" => (float) $allocatedAmount,
            "remaining_amount" => (float) $remainingAmount,
            "ppmp_total_amount" => (float) $ppmpTotal,
            "ppmp_approval_date" => $ppmpApprovalDate
        ];
    }

    public function AddBudgetAllocation($office_id, $amount, $operator_ID)
    {
        if (empty($amount) || !is_numeric($amount)) {
            return "Budget amount is required and must be a valid number.";
        }
        $amount = floatval($amount);

        $fiscal_year = $this->getCurrentFiscalYear(true);
        if (!$fiscal_year) {
            return "No active fiscal year found. Please set a current fiscal year first.";
        }
        $fiscal_year_id = $fiscal_year['fiscal_year_id'];

        $budget_details = $this->getAnnualBudgetDetails($fiscal_year_id);
        if (!$budget_details) {
            return "Cannot allocate budget: Annual Budget for the current fiscal year is not set.";
        }

        $total_annual_budget = $budget_details['total_annual_budget'];
        $total_allocated_so_far = $budget_details['total_allocated_so_far'];
        $remaining_budget = $total_annual_budget - $total_allocated_so_far;

        if ($amount > $remaining_budget) {
            return "
                Allocation failed.<br>
                The requested amount <b class='text-danger'>(₱" . number_format($amount, 2) . ")</b>
                exceeds the available balance <b class='text-danger'>(₱" . number_format($remaining_budget, 2) . ")</b>.<br><br>
                Annual Budget: <b>₱" . number_format($total_annual_budget, 2) . "</b><br>
                Already Allocated: <b>₱" . number_format($total_allocated_so_far, 2) . "</b>
            ";
        }

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

        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("INSERT INTO budget_allocation (office_id, fiscal_year_id, allocated_amount, remaining_amount, status) VALUES (?, ?, ?, ?, 'Approved')");
            $stmt->bind_param("iidd", $office_id, $fiscal_year_id, $amount, $amount);

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert budget allocation: " . $stmt->error);
            }

            $allocation_id = $stmt->insert_id;
            $stmt->close();

            $sqlFindAnnual = "SELECT annual_budget_id FROM annual_budget WHERE fiscal_year_id = ? LIMIT 1";
            $stmtFindAnnual = $this->conn->prepare($sqlFindAnnual);
            $stmtFindAnnual->bind_param("i", $fiscal_year_id);
            $stmtFindAnnual->execute();
            $resultAnnualId = $stmtFindAnnual->get_result();
            $annualBudgetRow = $resultAnnualId->fetch_assoc();
            $stmtFindAnnual->close();

            if ($annualBudgetRow) {
                $annual_budget_id = $annualBudgetRow['annual_budget_id'];

                $sqlAnnual = "UPDATE annual_budget SET remaining_budget_amount = remaining_budget_amount - ? WHERE annual_budget_id = ?";
                $stmtAnnual = $this->conn->prepare($sqlAnnual);

                if (!$stmtAnnual) {
                    throw new Exception("Annual budget update prepare failed: " . $this->conn->error);
                }

                $stmtAnnual->bind_param("di", $amount, $annual_budget_id);

                if (!$stmtAnnual->execute()) {
                    throw new Exception("Annual budget update execute failed: " . $stmtAnnual->error);
                }
                $stmtAnnual->close();
            }

            $this->conn->commit();

            $this->recordActivityLog(
                $operator_ID,
                "Added budget allocation (₱" . number_format($amount, 2) . ") for Office ID: $office_id (Fiscal Year: " . $fiscal_year['year'] . ")",
                "budget_allocation",
                $allocation_id
            );

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error in AddBudgetAllocation: " . $e->getMessage());
            return "Allocation failed: " . $e->getMessage();
        }
    }

    public function UpdateBudgetAllocation($allocation_id, $office_id, $amount, $status, $operator_ID)
    {
        if (empty($amount) || !is_numeric($amount)) {
            return "Budget amount is required and must be a valid number.";
        }
        $amount = floatval($amount);

        $fiscal_year = $this->getCurrentFiscalYear(true);
        if (!$fiscal_year) {
            return "No active fiscal year found. Please set a current fiscal year first.";
        }
        $fiscal_year_id = $fiscal_year['fiscal_year_id'];

        $original_amount_stmt = $this->conn->prepare("
            SELECT allocated_amount, status, remaining_amount
            FROM budget_allocation 
            WHERE allocation_id = ?
        ");
        $original_amount_stmt->bind_param("i", $allocation_id);
        $original_amount_stmt->execute();
        $original_result = $original_amount_stmt->get_result();
        $original_data = $original_result->fetch_assoc();
        $original_amount_stmt->close();

        if (!$original_data) {
            return "Allocation record not found.";
        }

        $original_amount = floatval($original_data['allocated_amount']);
        $original_status = $original_data['status'];

        $amount_difference = $amount - $original_amount;

        $budget_details = $this->getAnnualBudgetDetails($fiscal_year_id);
        if (!$budget_details) {
            return "Cannot update allocation: Annual Budget for the current fiscal year is not set or not Approved.";
        }

        $total_annual_budget = $budget_details['total_annual_budget'];
        $total_allocated_by_others = $budget_details['total_allocated_so_far'] - $original_amount;
        $remaining_budget_limit = $total_annual_budget - $total_allocated_by_others;

        if ($amount > $remaining_budget_limit) {
            return "
                Update failed.<br>
                The new requested amount <b class='text-danger'>(₱" . number_format($amount, 2) . ")</b>
                exceeds the available balance <b class='text-danger'>(₱" . number_format($remaining_budget_limit, 2) . ")</b>.<br><br>
                Annual Budget: <b>₱" . number_format($total_annual_budget, 2) . "</b><br>
                Allocated by Other Offices: <b>₱" . number_format($total_allocated_by_others, 2) . "</b>
            ";
        }

        $check_stmt = $this->conn->prepare("
            SELECT status 
            FROM budget_allocation 
            WHERE office_id = ? AND fiscal_year_id = ? AND allocation_id != ? AND status = 'Approved'
            ORDER BY allocation_id DESC LIMIT 1
        ");
        $check_stmt->bind_param("iii", $office_id, $fiscal_year_id, $allocation_id);
        $check_stmt->execute();
        $existing_status = null;
        $check_stmt->bind_result($existing_status);
        $exists = $check_stmt->fetch();
        $check_stmt->close();

        if ($exists) {
            return "This office already has another approved budget allocation for the current fiscal year.";
        }

        $this->conn->begin_transaction();

        try {
            $new_remaining_amount = $amount;

            $stmt = $this->conn->prepare("
                UPDATE budget_allocation 
                SET office_id = ?, allocated_amount = ?, remaining_amount = ?, status = ? 
                WHERE allocation_id = ?
            ");
            $stmt->bind_param("iddsi", $office_id, $amount, $new_remaining_amount, $status, $allocation_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update budget allocation: " . $stmt->error);
            }
            $stmt->close();

            // $sqlFindAnnual = "SELECT annual_budget_id FROM annual_budget WHERE fiscal_year_id = ? AND status = 'Approved' LIMIT 1";
            $sqlFindAnnual = "SELECT annual_budget_id FROM annual_budget WHERE fiscal_year_id = ? LIMIT 1";
            $stmtFindAnnual = $this->conn->prepare($sqlFindAnnual);
            $stmtFindAnnual->bind_param("i", $fiscal_year_id);
            $stmtFindAnnual->execute();
            $resultAnnualId = $stmtFindAnnual->get_result();
            $annualBudgetRow = $resultAnnualId->fetch_assoc();
            $stmtFindAnnual->close();

            if ($annualBudgetRow) {
                $annual_budget_id = $annualBudgetRow['annual_budget_id'];

                $adjustment = 0;

                $was_approved = strtolower($original_status) === 'approved';
                $is_approved = strtolower($status) === 'approved';

                if ($was_approved && $is_approved) {
                    $adjustment = $amount_difference;
                } elseif (!$was_approved && $is_approved) {
                    $adjustment = $amount;
                } elseif ($was_approved && !$is_approved) {
                    $adjustment = -$original_amount;
                }

                if ($adjustment !== 0) {
                    error_log("DEBUG: Annual Budget ID: " . $annual_budget_id);
                    error_log("DEBUG: Calculated Adjustment: " . $adjustment);
                    error_log("DEBUG: Attempting to update annual_budget...");
                    $sqlAnnual = "UPDATE annual_budget 
                                SET remaining_budget_amount = remaining_budget_amount - ? 
                                WHERE annual_budget_id = ?";
                    $stmtAnnual = $this->conn->prepare($sqlAnnual);
                    if (!$stmtAnnual) {
                        throw new Exception("Annual budget update prepare failed: " . $this->conn->error);
                    }
                    $stmtAnnual->bind_param("di", $adjustment, $annual_budget_id);
                    if (!$stmtAnnual->execute()) {
                        throw new Exception("Annual budget update execute failed: " . $stmtAnnual->error);
                    }
                    $stmtAnnual->close();
                }
            }

            $this->conn->commit();

            $formatted_amount = number_format($amount, 2);

            $this->recordActivityLog(
                $operator_ID,
                "Updated budget allocation (₱" . $formatted_amount . ") for Office ID: $office_id (Fiscal Year: " . $fiscal_year['year'] . ") to status: " . $status,
                "budget_allocation",
                $allocation_id
            );

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error in UpdateBudgetAllocation: " . $e->getMessage());
            return "Update failed: " . $e->getMessage();
        }
    }

    public function getBudgetSummaryByFiscalYear($fiscal_year_id)
    {
        $sql = "
            SELECT
                COALESCE(total_budget_amount, 0) AS annual_budget,
                COALESCE(total_budget_amount - remaining_budget_amount, 0) AS total_allocated,
                COALESCE(remaining_budget_amount, 0) AS available_balance
            FROM annual_budget
            WHERE fiscal_year_id = ?
            ORDER BY annual_budget_id DESC
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
    // END BUDGET ALLOCATIONS FUNCTIONS **********************************************************


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

    public function getOfficeName($officeId)
    {
        $sql = "SELECT office_name FROM offices WHERE office_id = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $officeId);
        $stmt->execute();
        $officeName = null;
        $stmt->bind_result($officeName);
        if ($stmt->fetch()) {
            $stmt->close();
            return $officeName ?? "";
        }
        $stmt->close();
        return "";
    }

    public function getOfficeNamesByIds($idString)
    {
        $cleanIdString = preg_replace('/[^0-9,]+/', '', $idString);
        if (empty($cleanIdString)) {
            return "";
        }

        $ids = array_filter(array_unique(explode(',', $cleanIdString)));
        if (empty($ids)) {
            return "";
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT office_name 
                FROM offices 
                WHERE office_id IN ($placeholders) AND is_active = 1";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("SQL Prepare Error (getOfficeNamesByIds): " . $this->conn->error);
            return "";
        }

        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $officeAcronyms = [
            'Office of the President' => 'OP',
            'Office of the Vice President for Academic Affairs' => 'OVPAA',
            'Office of the Vice President for Administration and Finance' => 'OVPAF',
            'Office of the Vice President for Research and Extension' => 'OVPRE',
            'Office of the Vice President for Planning, Development and Special Concerns' => 'OVPPDSC',
        ];

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $name = $row['office_name'];

            $names[] = $officeAcronyms[$name] ?? $name;
        }

        return implode(', ', $names);
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


    // PPMP FUNCTIONS **********************************************************

    public function getAllPPMPRecordsBySector($userId = null, $draftOnly = false)
    {
        $sql = "
            SELECT
                p.ppmp_id,
                p.ppmp_code,
                p.office_id,
                p.fiscal_year_id,
                p.current_version_id,

                pv.ppmp_version_id,
                pv.version_no,
                pv.status,
                pv.lifecycle_source,
                pv.is_editable,
                pv.total_amount,
                pv.submitted_at,
                pv.approved_at,
                pv.approve_reason,
                pv.rejected_at,
                pv.reject_reason,
                pv.returned_at,
                pv.consolidated_at,
                pv.finalized_at,
                pv.created_at,

                o.office_name,
                fy.year AS fiscal_year,
                fy.is_lock,

                u.first_name,
                u.last_name,

                rr.revision_request_id,
                rr.status AS revision_request_status,
                rr.reason AS revision_request_reason,
                rr.revision_phase,
                rr.created_at AS revision_requested_at,
                rr.processed_at AS revision_processed_at,

                CASE
                    WHEN rr.status = 'Requested'
                        THEN 'Revision Requested'

                    WHEN rr.status = 'Approved' 
                        AND pv.is_editable = 1
                        THEN 'Revision Approved'

                    WHEN pv.status = 'Pending'
                        AND pv.lifecycle_source = 'Initial Submission'
                        THEN 'Pending - New Submission'

                    WHEN pv.status = 'Pending'
                        AND pv.lifecycle_source = 'Pre-Consolidation Revision'
                        THEN 'Pending - Revised PPMP'

                    WHEN pv.status = 'Pending'
                        AND pv.lifecycle_source = 'Post-APP Revision'
                        THEN 'Pending - APP Revision'

                    WHEN pv.status = 'Approved'
                        AND pv.consolidated_at IS NULL
                        THEN 'Approved - Waiting for Consolidation'

                    WHEN pv.status = 'Approved'
                        AND pv.consolidated_at IS NOT NULL
                        THEN 'Approved - Consolidated'

                    WHEN pv.status = 'Consolidated'
                        THEN 'Consolidated'

                    WHEN pv.status = 'Finalized'
                        THEN 'Finalized APP'

                    WHEN pv.status = 'Rejected'
                        THEN 'Rejected - Editable'

                    WHEN pv.status = 'Returned'
                        THEN 'Returned - Editable'

                    WHEN pv.status = 'Draft'
                        THEN 'Draft'

                    ELSE pv.status
                END AS display_status,

                CASE
                    WHEN rr.status = 'Requested'
                        THEN 'primary'

                    WHEN rr.status = 'Approved' 
                        AND pv.is_editable = 1
                        THEN 'success'

                    WHEN pv.status = 'Pending'
                        THEN 'warning'

                    WHEN pv.status = 'Approved'
                        THEN 'success'

                    WHEN pv.status = 'Consolidated'
                        THEN 'info'

                    WHEN pv.status = 'Finalized'
                        THEN 'dark'

                    WHEN pv.status = 'Rejected'
                        THEN 'danger'

                    WHEN pv.status = 'Returned'
                        THEN 'info'

                    ELSE 'secondary'
                END AS status_badge,

                CASE
                    WHEN rr.status = 'Requested'
                        THEN 0

                    WHEN pv.status IN ('Pending', 'Approved')
                        AND pv.consolidated_at IS NULL
                        AND pv.is_editable = 0
                        THEN 1

                    WHEN pv.status = 'Finalized'
                        AND fy.is_lock = 1
                        AND pv.is_editable = 0
                        THEN 1

                    ELSE 0
                END AS can_request_revision,

                CASE
                    WHEN pv.is_editable = 1
                        AND pv.status IN ('Draft', 'Rejected', 'Returned')
                        THEN 1

                    WHEN rr.status = 'Approved'
                        AND pv.is_editable = 1
                        THEN 1

                    ELSE 0
                END AS can_edit

            FROM ppmp p

            INNER JOIN ppmp_versions pv
                ON pv.ppmp_version_id = p.current_version_id

            INNER JOIN offices o
                ON o.office_id = p.office_id

            INNER JOIN fiscal_years fy
                ON fy.fiscal_year_id = p.fiscal_year_id

            INNER JOIN users u
                ON u.user_id = p.created_by

            LEFT JOIN (
                SELECT r1.*
                FROM ppmp_revision_requests r1
                INNER JOIN (
                    SELECT 
                        ppmp_version_id,
                        MAX(revision_request_id) AS latest_revision_id
                    FROM ppmp_revision_requests
                    GROUP BY ppmp_version_id
                ) latest
                    ON latest.latest_revision_id = r1.revision_request_id
            ) rr
                ON rr.ppmp_version_id = pv.ppmp_version_id

            WHERE 1=1
        ";

        if ($draftOnly) {
            $sql .= " AND pv.status = 'Draft' ";
        } else {
            $sql .= " AND pv.status != 'Draft' ";
        }

        if (!empty($userId)) {
            $sql .= " AND p.created_by = ? ";
        }

        $sql .= " ORDER BY pv.created_at DESC ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Database error: ' . $this->conn->error);
        }

        if (!empty($userId)) {
            $stmt->bind_param('i', $userId);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception('Query execution failed: ' . $this->conn->error);
        }

        return $result;
    }

    public function RequestPPMPRevision($ppmp_id, $reason, $user_id)
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("
                SELECT
                    p.ppmp_id,
                    p.current_version_id,
                    p.office_id,
                    p.fiscal_year_id,
                    pv.ppmp_version_id,
                    pv.status,
                    pv.consolidated_at,
                    pv.finalized_at,
                    pv.is_editable
                FROM ppmp p
                INNER JOIN ppmp_versions pv ON pv.ppmp_version_id = p.current_version_id
                WHERE p.ppmp_id = ?
                LIMIT 1
            ");

            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("i", $ppmp_id);
            $stmt->execute();
            $ppmp = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$ppmp)
                throw new Exception("PPMP not found.");

            $ppmp_version_id = (int) $ppmp['ppmp_version_id'];
            $fiscal_year_id = (int) $ppmp['fiscal_year_id'];
            $status = $ppmp['status'];

            if ($ppmp['is_editable'] == 1) {
                throw new Exception("This PPMP is already editable. Revision request is not needed.");
            }

            $allowed = false;
            $revision_phase = 'Pre-Consolidation';

            if (in_array($status, ['Pending', 'Approved'], true) && empty($ppmp['consolidated_at'])) {
                $allowed = true;
                $revision_phase = 'Pre-Consolidation';
            }

            if ($status === 'Finalized' || !empty($ppmp['finalized_at'])) {
                $allowed = true;
                $revision_phase = 'Post-Finalization';
            }

            if (!$allowed) {
                throw new Exception("Revision request is not allowed for the current PPMP status.");
            }

            $check = $this->conn->prepare("
                SELECT revision_request_id
                FROM ppmp_revision_requests
                WHERE ppmp_id = ?
                AND status = 'Requested'
                LIMIT 1
            ");

            if (!$check)
                throw new Exception($this->conn->error);

            $check->bind_param("i", $ppmp_id);
            $check->execute();
            $existing = $check->get_result();
            $check->close();

            if ($existing->num_rows > 0) {
                throw new Exception("Revision request already exists.");
            }

            $insert = $this->conn->prepare("
                INSERT INTO ppmp_revision_requests (
                    ppmp_id,
                    ppmp_version_id,
                    requested_by,
                    reason,
                    revision_phase,
                    status,
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, 'Requested', NOW())
            ");

            if (!$insert)
                throw new Exception($this->conn->error);

            $insert->bind_param("iiiss", $ppmp_id, $ppmp_version_id, $user_id, $reason, $revision_phase);

            if (!$insert->execute()) {
                throw new Exception($insert->error);
            }

            $insert->close();
            $this->conn->commit();

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function getPPMPItemsById($ppmp_id)
    {
        $sql = "
            SELECT
                pvi.ppmp_version_item_id,
                pvi.ppmp_version_id,
                pv.version_no,
                pvi.category_id,
                pvi.sub_category_id,
                pvi.item_name_id,
                ic.category_name,
                sc.sub_cat_name,
                inames.item_name,
                pvi.item_description,
                pvi.specifications,
                pvi.quantity,
                pvi.mode_of_procurement AS mode_of_procurement_id,
                pm.proc_mode_name AS mode_of_procurement,
                pvi.pre_procurement_conference,
                pvi.procurement_start_date,
                pvi.bidding_date,
                pvi.contract_signing_date,
                pvi.source_of_funds,
                pvi.estimated_budget,
                pvi.total_cost,
                pvi.file_attachment,
                pvi.remarks
            FROM ppmp p
            INNER JOIN ppmp_versions pv ON pv.ppmp_version_id = p.current_version_id
            INNER JOIN ppmp_version_items pvi ON pvi.ppmp_version_id = pv.ppmp_version_id
            INNER JOIN item_categories ic ON pvi.category_id = ic.category_id
            LEFT JOIN sub_categories sc ON pvi.sub_category_id = sc.sub_category_id
            LEFT JOIN item_names inames ON pvi.item_name_id = inames.item_name_id
            LEFT JOIN procurement_modes pm ON pvi.mode_of_procurement = pm.proc_mode_id
            WHERE p.ppmp_id = ?
            ORDER BY pvi.ppmp_version_item_id ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt)
            throw new Exception("Database error: " . $this->conn->error);

        $stmt->bind_param("i", $ppmp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getPPMPHeaderDetails($ppmp_id)
    {
        $sql = "
            SELECT
                p.ppmp_id,
                p.ppmp_code,
                p.current_version_id,
                o.office_name,
                fy.year AS fiscal_year,
                pv.ppmp_version_id,
                pv.version_no,
                pv.total_amount,
                pv.status,
                pv.lifecycle_source,
                pv.is_editable,
                pv.created_at,
                pv.submitted_at
            FROM ppmp p
            INNER JOIN offices o ON p.office_id = o.office_id
            INNER JOIN fiscal_years fy ON p.fiscal_year_id = fy.fiscal_year_id
            LEFT JOIN ppmp_versions pv ON pv.ppmp_version_id = p.current_version_id
            WHERE p.ppmp_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        $stmt->bind_param("i", $ppmp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ?: null;
    }

    public function checkOfficeBudgetStatus($user_id)
    {
        $office_id = $this->getOfficeIdByHead($user_id);
        $currentFY = $this->getCurrentFiscalYear();

        if (!$office_id) {
            return [
                'status' => 'error',
                'message' => 'Office not found or inactive.'
            ];
        }

        if (!$currentFY) {
            return [
                'status' => 'error',
                'message' => 'No active fiscal year found available.'
            ];
        }

        if ((int) $currentFY['is_lock'] === 1) {
            return [
                'status' => 'error',
                'message' => "Fiscal Year {$currentFY['year']} is locked. PPMP submission is not allowed."
            ];
        }

        $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
        $fiscal_year_name = $currentFY['year'];

        $stmt = $this->conn->prepare("
            SELECT 
                p.ppmp_id,
                pv.ppmp_version_id,
                pv.status,
                pv.lifecycle_source,
                pv.is_editable
            FROM ppmp p
            INNER JOIN ppmp_versions pv
                ON pv.ppmp_version_id = p.current_version_id
            WHERE p.office_id = ?
            AND p.fiscal_year_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $this->conn->error
            ];
        }

        $stmt->bind_param("ii", $office_id, $fiscal_year_id);
        $stmt->execute();
        $ppmpResult = $stmt->get_result();

        if ($ppmpResult->num_rows > 0) {
            $ppmp = $ppmpResult->fetch_assoc();
            $status = $ppmp['status'];
            $isEditable = (int) $ppmp['is_editable'];

            if ($status === 'Draft') {
                $stmt->close();

                return [
                    'status' => 'draft_exists',
                    'message' => "You already have a draft PPMP for Fiscal Year {$fiscal_year_name}.",
                    'ppmp_id' => (int) $ppmp['ppmp_id'],
                    'ppmp_version_id' => (int) $ppmp['ppmp_version_id']
                ];
            }

            if ($isEditable === 1 && in_array($status, ['Rejected', 'Returned', 'Draft'], true)) {
                $stmt->close();

                return [
                    'status' => 'editable_exists',
                    'message' => "You already have an editable PPMP for Fiscal Year {$fiscal_year_name}.",
                    'ppmp_id' => (int) $ppmp['ppmp_id'],
                    'ppmp_version_id' => (int) $ppmp['ppmp_version_id']
                ];
            }

            if (in_array($status, ['Pending', 'Approved', 'Consolidated', 'Finalized'], true)) {
                $stmt->close();

                return [
                    'status' => 'error',
                    'message' => "You already submitted a PPMP for Fiscal Year {$fiscal_year_name}. Please use Request Revision if changes are needed."
                ];
            }
        }

        $stmt->close();

        $allocStmt = $this->conn->prepare("
            SELECT allocated_amount, remaining_amount, status
            FROM budget_allocation
            WHERE office_id = ?
            AND fiscal_year_id = ?
            ORDER BY allocation_id DESC
            LIMIT 1
        ");

        if (!$allocStmt) {
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $this->conn->error
            ];
        }

        $allocStmt->bind_param("ii", $office_id, $fiscal_year_id);
        $allocStmt->execute();
        $allocResult = $allocStmt->get_result();

        if ($allocResult->num_rows === 0) {
            $allocStmt->close();

            return [
                'status' => 'error',
                'message' => "No budget allocation found for your office in Fiscal Year {$fiscal_year_name}."
            ];
        }

        $allocation = $allocResult->fetch_assoc();
        $allocStmt->close();

        if ($allocation['status'] !== 'Approved') {
            return [
                'status' => 'error',
                'message' => "Budget allocation exists but is not approved yet. Status: {$allocation['status']}."
            ];
        }

        if ((float) $allocation['remaining_amount'] <= 0) {
            return [
                'status' => 'error',
                'message' => 'Your office has no remaining budget allocation.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Budget is approved and ready for PPMP submission.',
            'office_id' => $office_id,
            'fiscal_year_id' => $fiscal_year_id,
            'fiscal_year' => $fiscal_year_name
        ];
    }

    public function AddPPMPForm($user_id, $items, $is_final, $uploaded_files_map = [])
    {
        $this->conn->begin_transaction();

        try {
            $office_id = $this->getOfficeIdByHead($user_id);
            $currentFY = $this->getCurrentFiscalYear(true);

            if (!$office_id) {
                throw new Exception("Office not found or inactive.");
            }

            if (!$currentFY) {
                throw new Exception("No active unlocked fiscal year found.");
            }

            if (empty($items) || !is_array($items)) {
                throw new Exception("No PPMP items provided.");
            }

            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
            $total_amount = 0;

            foreach ($items as $item) {
                $quantity = (int) ($item['quantity'] ?? 0);
                $estimated_budget = (float) ($item['estimated_budget'] ?? 0);

                if ($quantity <= 0 || $estimated_budget <= 0) {
                    throw new Exception("Invalid item quantity or estimated budget.");
                }

                $total_amount += $quantity * $estimated_budget;
            }

            $allocStmt = $this->conn->prepare("
                SELECT allocation_id, allocated_amount, remaining_amount, status
                FROM budget_allocation
                WHERE office_id = ? 
                AND fiscal_year_id = ?
                ORDER BY allocation_id DESC
                LIMIT 1
            ");

            $allocStmt->bind_param("ii", $office_id, $fiscal_year_id);
            $allocStmt->execute();
            $alloc = $allocStmt->get_result()->fetch_assoc();
            $allocStmt->close();

            if (!$alloc) {
                throw new Exception("No approved budget allocation found.");
            }

            if ($alloc['status'] !== 'Approved') {
                throw new Exception("Budget allocation is not approved.");
            }

            $allocated_amount = (float) $alloc['allocated_amount'];

            if ($total_amount > $allocated_amount) {
                throw new Exception(
                    "Total PPMP amount exceeds allocated budget. Allocated budget: ₱" .
                    number_format($allocated_amount, 2) .
                    ". PPMP total: ₱" .
                    number_format($total_amount, 2)
                );
            }

            $checkStmt = $this->conn->prepare("
                SELECT ppmp_id, current_version_id
                FROM ppmp
                WHERE office_id = ? 
                AND fiscal_year_id = ?
                LIMIT 1
            ");

            $checkStmt->bind_param("ii", $office_id, $fiscal_year_id);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            $ppmp_id = null;
            $version_no = 1;

            if ($existing) {
                $ppmp_id = (int) $existing['ppmp_id'];

                $versionStmt = $this->conn->prepare("
                    SELECT MAX(version_no) AS latest_version
                    FROM ppmp_versions
                    WHERE ppmp_id = ?
                ");

                $versionStmt->bind_param("i", $ppmp_id);
                $versionStmt->execute();
                $versionResult = $versionStmt->get_result()->fetch_assoc();
                $versionStmt->close();

                $version_no = ((int) $versionResult['latest_version']) + 1;
            } else {
                $ppmp_code = 'PPMP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

                $insertPPMP = $this->conn->prepare("
                    INSERT INTO ppmp (
                        ppmp_code,
                        office_id,
                        fiscal_year_id,
                        current_version_id,
                        created_by
                    )
                    VALUES (?, ?, ?, NULL, ?)
                ");

                $insertPPMP->bind_param(
                    "siii",
                    $ppmp_code,
                    $office_id,
                    $fiscal_year_id,
                    $user_id
                );

                if (!$insertPPMP->execute()) {
                    throw new Exception($insertPPMP->error);
                }

                $ppmp_id = $insertPPMP->insert_id;
                $insertPPMP->close();
            }

            $version_status = ($is_final == 1) ? 'Pending' : 'Draft';
            $is_editable = ($is_final == 1) ? 0 : 1;
            $submitted_at = ($is_final == 1) ? date('Y-m-d H:i:s') : null;

            $versionInsert = $this->conn->prepare("
                INSERT INTO ppmp_versions (
                    ppmp_id,
                    version_no,
                    status,
                    lifecycle_source,
                    is_editable,
                    based_on_version_id,
                    total_amount,
                    submitted_by,
                    submitted_at
                )
                VALUES (?, ?, ?, 'Initial Submission', ?, NULL, ?, ?, ?)
            ");

            $versionInsert->bind_param(
                "iisidss",
                $ppmp_id,
                $version_no,
                $version_status,
                $is_editable,
                $total_amount,
                $user_id,
                $submitted_at
            );

            if (!$versionInsert->execute()) {
                throw new Exception($versionInsert->error);
            }

            $ppmp_version_id = $versionInsert->insert_id;
            $versionInsert->close();

            $itemStmt = $this->conn->prepare("
                INSERT INTO ppmp_version_items (
                    ppmp_version_id,
                    category_id,
                    sub_category_id,
                    item_name_id,
                    item_description,
                    specifications,
                    quantity,
                    estimated_budget,
                    total_cost,
                    file_attachment,
                    mode_of_procurement,
                    pre_procurement_conference,
                    procurement_start_date,
                    bidding_date,
                    contract_signing_date,
                    source_of_funds,
                    remarks
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $category_id = (int) ($it['category_id'] ?? 0);
                $sub_category_id = !empty($it['sub_category_id']) ? (int) $it['sub_category_id'] : null;
                $item_name_id = (int) ($it['item_name_id'] ?? 0);
                $item_description = trim($it['item_description'] ?? '');
                $specifications = $it['specifications'] ?? null;

                $quantity = (int) ($it['quantity'] ?? 0);
                $estimated_budget = (float) ($it['estimated_budget'] ?? 0);
                $total_cost = $quantity * $estimated_budget;

                $mode_of_procurement = !empty($it['mode_of_procurement_id']) ? (int) $it['mode_of_procurement_id'] : null;
                $pre_procurement_conference = $it['pre_procurement_conference'] ?? null;
                $procurement_start_date = !empty($it['procurement_start_date']) ? $it['procurement_start_date'] : null;
                $bidding_date = !empty($it['bidding_date']) ? $it['bidding_date'] : null;
                $contract_signing_date = !empty($it['contract_signing_date']) ? $it['contract_signing_date'] : null;
                $source_of_funds = $it['source_of_funds'] ?? null;
                $remarks = $it['remarks'] ?? null;

                if ($category_id <= 0 || $item_name_id <= 0 || $item_description === '') {
                    throw new Exception("Invalid PPMP item details.");
                }

                if ($quantity <= 0 || $estimated_budget <= 0) {
                    throw new Exception("Invalid item quantity or estimated budget.");
                }

                $temp_item_id = $it['temp_item_id'] ?? '';
                $item_files = $uploaded_files_map[$temp_item_id] ?? [];
                $file_attachment = !empty($item_files) ? implode(',', $item_files) : '';

                $itemStmt->bind_param(
                    "iiiissiddsissssss",
                    $ppmp_version_id,
                    $category_id,
                    $sub_category_id,
                    $item_name_id,
                    $item_description,
                    $specifications,
                    $quantity,
                    $estimated_budget,
                    $total_cost,
                    $file_attachment,
                    $mode_of_procurement,
                    $pre_procurement_conference,
                    $procurement_start_date,
                    $bidding_date,
                    $contract_signing_date,
                    $source_of_funds,
                    $remarks
                );

                if (!$itemStmt->execute()) {
                    throw new Exception($itemStmt->error);
                }
            }

            $itemStmt->close();

            $updatePPMP = $this->conn->prepare("
                UPDATE ppmp
                SET current_version_id = ?, updated_at = NOW()
                WHERE ppmp_id = ?
            ");

            $updatePPMP->bind_param("ii", $ppmp_version_id, $ppmp_id);

            if (!$updatePPMP->execute()) {
                throw new Exception($updatePPMP->error);
            }

            $updatePPMP->close();

            $this->conn->commit();

            $this->recordActivityLog(
                $user_id,
                "Created PPMP Version {$version_no}",
                "ppmp_versions",
                $ppmp_version_id
            );

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function UpdatePPMPForm($ppmp_id, $user_id, $items, $is_final, $uploaded_files_map = [])
    {
        $this->conn->begin_transaction();

        try {
            if ($ppmp_id <= 0 || $user_id <= 0) {
                throw new Exception("Invalid PPMP ID or User ID.");
            }

            if (empty($items) || !is_array($items)) {
                throw new Exception("No PPMP items provided.");
            }

            $ppmp_stmt = $this->conn->prepare("
                SELECT
                    p.ppmp_id,
                    p.ppmp_code,
                    p.office_id,
                    p.fiscal_year_id,
                    p.current_version_id,
                    p.created_by,
                    pv.ppmp_version_id,
                    pv.version_no,
                    pv.status,
                    pv.lifecycle_source,
                    pv.is_editable,
                    pv.total_amount,
                    pv.consolidated_at,
                    pv.finalized_at
                FROM ppmp p
                LEFT JOIN ppmp_versions pv
                    ON pv.ppmp_version_id = p.current_version_id
                WHERE p.ppmp_id = ?
                LIMIT 1
            ");

            if (!$ppmp_stmt) {
                throw new Exception($this->conn->error);
            }

            $ppmp_stmt->bind_param("i", $ppmp_id);
            $ppmp_stmt->execute();
            $result = $ppmp_stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("PPMP record not found.");
            }

            $ppmp = $result->fetch_assoc();
            $ppmp_stmt->close();

            if ((int) $ppmp['created_by'] !== (int) $user_id) {
                throw new Exception("You are not authorized to update this PPMP.");
            }

            $active_version_id = (int) ($ppmp['current_version_id'] ?? 0);
            $office_id = (int) $ppmp['office_id'];
            $fiscal_year_id = (int) $ppmp['fiscal_year_id'];
            $current_version_no = (int) ($ppmp['version_no'] ?? 0);
            $current_status = trim((string) ($ppmp['status'] ?? 'Draft'));
            $is_editable = (int) ($ppmp['is_editable'] ?? 0) === 1;

            if ($active_version_id <= 0) {
                throw new Exception("PPMP current version not found.");
            }

            $allowedStatuses = ['Draft', 'Returned', 'Rejected'];

            if (!in_array($current_status, $allowedStatuses, true) && !$is_editable) {
                throw new Exception("This PPMP can no longer be edited in its current status.");
            }

            $alloc_stmt = $this->conn->prepare("
                SELECT allocation_id, allocated_amount, status
                FROM budget_allocation
                WHERE office_id = ?
                AND fiscal_year_id = ?
                ORDER BY allocation_id DESC
                LIMIT 1
            ");

            if (!$alloc_stmt) {
                throw new Exception($this->conn->error);
            }

            $alloc_stmt->bind_param("ii", $office_id, $fiscal_year_id);
            $alloc_stmt->execute();
            $alloc_stmt->store_result();

            if ($alloc_stmt->num_rows === 0) {
                throw new Exception("No budget allocation found for this office in the current fiscal year.");
            }

            $allocation_id = 0;
            $allocated_amount = 0.0;
            $allocation_status = '';

            $alloc_stmt->bind_result($allocation_id, $allocated_amount, $allocation_status);
            $alloc_stmt->fetch();
            $alloc_stmt->close();

            if (strtolower((string) $allocation_status) !== 'approved') {
                throw new Exception("Budget allocation exists but is not approved yet.");
            }

            $new_total = 0.0;

            foreach ($items as $it) {
                $quantity = (int) ($it['quantity'] ?? 0);
                $estimated_budget = (float) ($it['estimated_budget'] ?? 0);

                if ($quantity <= 0 || $estimated_budget <= 0) {
                    throw new Exception("One or more PPMP items contain invalid quantity or estimated budget.");
                }

                $new_total += $quantity * $estimated_budget;
            }

            if ($new_total <= 0) {
                throw new Exception("Total PPMP amount must be greater than zero.");
            }

            if ($new_total > (float) $allocated_amount) {
                throw new Exception(
                    "Total PPMP amount exceeds allocated budget. Allocated budget is ₱" .
                    number_format((float) $allocated_amount, 2) .
                    " but PPMP total is ₱" .
                    number_format((float) $new_total, 2) . "."
                );
            }

            $isPreviouslyConsolidated = !empty($ppmp['consolidated_at']);
            $new_version_no = $current_version_no + 1;
            $new_status = ((int) $is_final === 1) ? 'Pending' : 'Draft';
            $new_is_editable = ((int) $is_final === 1) ? 0 : 1;
            $submitted_at = ((int) $is_final === 1) ? date('Y-m-d H:i:s') : null;

            $lifecycle_source = $isPreviouslyConsolidated
                ? 'Post-APP Revision'
                : 'Pre-Consolidation Revision';

            $version_stmt = $this->conn->prepare("
                INSERT INTO ppmp_versions (
                    ppmp_id,
                    version_no,
                    status,
                    lifecycle_source,
                    is_editable,
                    based_on_version_id,
                    total_amount,
                    submitted_by,
                    submitted_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$version_stmt) {
                throw new Exception($this->conn->error);
            }

            $version_stmt->bind_param(
                "iissiidis",
                $ppmp_id,
                $new_version_no,
                $new_status,
                $lifecycle_source,
                $new_is_editable,
                $active_version_id,
                $new_total,
                $user_id,
                $submitted_at
            );

            if (!$version_stmt->execute()) {
                throw new Exception("Failed to create new PPMP version: " . $version_stmt->error);
            }

            $new_ppmp_version_id = $version_stmt->insert_id;
            $version_stmt->close();

            $item_stmt = $this->conn->prepare("
                INSERT INTO ppmp_version_items (
                    ppmp_version_id,
                    category_id,
                    sub_category_id,
                    item_name_id,
                    item_description,
                    specifications,
                    quantity,
                    estimated_budget,
                    total_cost,
                    file_attachment,
                    mode_of_procurement,
                    pre_procurement_conference,
                    procurement_start_date,
                    bidding_date,
                    contract_signing_date,
                    source_of_funds,
                    remarks
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$item_stmt) {
                throw new Exception($this->conn->error);
            }

            foreach ($items as $it) {
                $temp_item_id = (string) ($it['temp_item_id'] ?? '');
                $new_files = $uploaded_files_map[$temp_item_id] ?? [];

                $existing_files = trim((string) ($it['file_attachment'] ?? ''));
                $new_files_string = !empty($new_files) ? implode(',', $new_files) : '';

                if ($existing_files !== '' && $new_files_string !== '') {
                    $file_attachment_string = $existing_files . ',' . $new_files_string;
                } else {
                    $file_attachment_string = $existing_files !== '' ? $existing_files : $new_files_string;
                }

                $category_id = (int) ($it['category_id'] ?? 0);
                $sub_category_id = !empty($it['sub_category_id']) ? (int) $it['sub_category_id'] : null;
                $item_name_id = (int) ($it['item_name_id'] ?? 0);
                $item_description = trim((string) ($it['item_description'] ?? ''));
                $specifications = trim((string) ($it['specifications'] ?? ''));
                $quantity = (int) ($it['quantity'] ?? 0);
                $estimated_budget = (float) ($it['estimated_budget'] ?? 0);

                $total_cost = $quantity * $estimated_budget;

                $mode_of_procurement = !empty($it['mode_of_procurement_id'])
                    ? (int) $it['mode_of_procurement_id']
                    : null;

                $pre_procurement_conference = trim((string) ($it['pre_procurement_conference'] ?? ''));
                $procurement_start_date = !empty($it['procurement_start_date']) ? $it['procurement_start_date'] : null;
                $bidding_date = !empty($it['bidding_date']) ? $it['bidding_date'] : null;
                $contract_signing_date = !empty($it['contract_signing_date']) ? $it['contract_signing_date'] : null;
                $source_of_funds = trim((string) ($it['source_of_funds'] ?? ''));
                $remarks = trim((string) ($it['remarks'] ?? ''));

                $specifications = $specifications !== '' ? $specifications : null;
                $pre_procurement_conference = $pre_procurement_conference !== '' ? $pre_procurement_conference : null;
                $source_of_funds = $source_of_funds !== '' ? $source_of_funds : null;
                $remarks = $remarks !== '' ? $remarks : null;
                $file_attachment_string = $file_attachment_string !== '' ? $file_attachment_string : null;

                if (
                    $category_id <= 0 ||
                    $item_name_id <= 0 ||
                    $item_description === '' ||
                    $quantity <= 0 ||
                    $estimated_budget <= 0 ||
                    $total_cost <= 0
                ) {
                    throw new Exception("One or more PPMP items contain invalid or incomplete data.");
                }

                $item_stmt->bind_param(
                    "iiiissiddsissssss",
                    $new_ppmp_version_id,
                    $category_id,
                    $sub_category_id,
                    $item_name_id,
                    $item_description,
                    $specifications,
                    $quantity,
                    $estimated_budget,
                    $total_cost,
                    $file_attachment_string,
                    $mode_of_procurement,
                    $pre_procurement_conference,
                    $procurement_start_date,
                    $bidding_date,
                    $contract_signing_date,
                    $source_of_funds,
                    $remarks
                );

                if (!$item_stmt->execute()) {
                    throw new Exception("Failed to insert updated PPMP version item: " . $item_stmt->error);
                }
            }

            $item_stmt->close();

            $archive_stmt = $this->conn->prepare("
                UPDATE ppmp_versions
                SET status = 'Archived',
                    is_editable = 0,
                    updated_at = NOW()
                WHERE ppmp_version_id = ?
            ");

            if (!$archive_stmt) {
                throw new Exception($this->conn->error);
            }

            $archive_stmt->bind_param("i", $active_version_id);

            if (!$archive_stmt->execute()) {
                throw new Exception("Failed to archive previous PPMP version: " . $archive_stmt->error);
            }

            $archive_stmt->close();

            $update_root_stmt = $this->conn->prepare("
                UPDATE ppmp
                SET current_version_id = ?,
                    updated_at = NOW()
                WHERE ppmp_id = ?
            ");

            if (!$update_root_stmt) {
                throw new Exception($this->conn->error);
            }

            $update_root_stmt->bind_param("ii", $new_ppmp_version_id, $ppmp_id);

            if (!$update_root_stmt->execute()) {
                throw new Exception("Failed to update PPMP root: " . $update_root_stmt->error);
            }

            $update_root_stmt->close();

            $rev_stmt = $this->conn->prepare("
                UPDATE ppmp_revision_requests
                SET status = 'Approved',
                    processed_by = ?,
                    processed_at = NOW()
                WHERE ppmp_id = ?
                AND ppmp_version_id = ?
                AND status = 'Requested'
            ");

            if (!$rev_stmt) {
                throw new Exception($this->conn->error);
            }

            $rev_stmt->bind_param("iii", $user_id, $ppmp_id, $active_version_id);
            $rev_stmt->execute();
            $rev_stmt->close();

            $this->conn->commit();

            $this->recordActivityLog(
                $user_id,
                "Updated PPMP ({$ppmp['ppmp_code']}) to version {$new_version_no} with " . count($items) . " items.",
                "ppmp",
                $ppmp_id
            );

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function getPPMPHeaderDetailsByVersionId($ppmpVersionId)
    {
        $stmt = $this->conn->prepare("
            SELECT p.ppmp_id, p.ppmp_code, p.current_version_id, o.office_name, fy.year AS fiscal_year,
                pv.ppmp_version_id, pv.version_no, pv.total_amount, pv.status, pv.lifecycle_source,
                pv.is_editable, pv.created_at, pv.submitted_at
            FROM ppmp_versions pv
            INNER JOIN ppmp p ON p.ppmp_id = pv.ppmp_id
            INNER JOIN offices o ON o.office_id = p.office_id
            INNER JOIN fiscal_years fy ON fy.fiscal_year_id = p.fiscal_year_id
            WHERE pv.ppmp_version_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $ppmpVersionId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $data ?: null;
    }

    public function getPPMPItemsByVersionId($ppmpVersionId)
    {
        $stmt = $this->conn->prepare("
            SELECT pvi.*, ic.category_name, sc.sub_cat_name, inames.item_name,
                pvi.mode_of_procurement AS mode_of_procurement_id,
                pm.proc_mode_name AS mode_of_procurement
            FROM ppmp_version_items pvi
            INNER JOIN item_categories ic ON ic.category_id = pvi.category_id
            LEFT JOIN sub_categories sc ON sc.sub_category_id = pvi.sub_category_id
            LEFT JOIN item_names inames ON inames.item_name_id = pvi.item_name_id
            LEFT JOIN procurement_modes pm ON pm.proc_mode_id = pvi.mode_of_procurement
            WHERE pvi.ppmp_version_id = ?
            ORDER BY pvi.ppmp_version_item_id ASC
        ");
        $stmt->bind_param("i", $ppmpVersionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function getAllPPMPRecordsForProcHead($status = null, $fiscalYearId = null)
    {
        if (empty($fiscalYearId)) {
            $currentFY = $this->getCurrentFiscalYear();
            if (!$currentFY)
                return $this->conn->query("SELECT 1 WHERE 0");
            $fiscalYearId = (int) $currentFY['fiscal_year_id'];
        }

        $sql = "
            SELECT
                p.ppmp_id, p.ppmp_code, p.office_id, p.fiscal_year_id, p.current_version_id,
                pv.ppmp_version_id, pv.version_no, pv.total_amount, pv.status, pv.lifecycle_source,
                pv.is_editable, pv.submitted_at, pv.approved_at, pv.returned_at, pv.return_reason,
                pv.consolidated_at, pv.finalized_at, pv.created_at,
                o.office_name, o.office_code,
                fy.year AS fiscal_year,
                u.first_name, u.last_name, u.email, u.phone,
                prr.revision_request_id,
                prr.status AS revision_request_status,
                prr.reason AS revision_request_reason,
                prr.revision_phase,

                CASE
                    WHEN prr.status = 'Requested' AND prr.revision_phase = 'Post-Finalization'
                        THEN 'Post-APP Revision Request'
                    WHEN prr.status = 'Requested' AND prr.revision_phase = 'Pre-Consolidation'
                        THEN 'Revision Request'
                    WHEN pv.status = 'Pending' AND pv.lifecycle_source = 'Initial Submission'
                        THEN 'New Submission'
                    WHEN pv.status = 'Pending' AND pv.lifecycle_source = 'Pre-Consolidation Revision'
                        THEN 'Revision Resubmission'
                    WHEN pv.status = 'Pending' AND pv.lifecycle_source = 'Post-APP Revision'
                        THEN 'APP Revision Resubmission'
                    ELSE pv.status
                END AS display_status,

                CASE
                    WHEN prr.status = 'Requested' AND prr.revision_phase = 'Post-Finalization' THEN 'danger'
                    WHEN prr.status = 'Requested' THEN 'primary'
                    WHEN pv.lifecycle_source = 'Initial Submission' THEN 'warning'
                    WHEN pv.lifecycle_source = 'Pre-Consolidation Revision' THEN 'info'
                    WHEN pv.lifecycle_source = 'Post-APP Revision' THEN 'danger'
                    ELSE 'secondary'
                END AS status_badge

            FROM ppmp p
            INNER JOIN ppmp_versions pv ON pv.ppmp_version_id = p.current_version_id
            INNER JOIN offices o ON o.office_id = p.office_id
            INNER JOIN fiscal_years fy ON fy.fiscal_year_id = p.fiscal_year_id
            INNER JOIN users u ON u.user_id = p.created_by

            LEFT JOIN (
                SELECT rr.*
                FROM ppmp_revision_requests rr
                INNER JOIN (
                    SELECT ppmp_version_id, MAX(revision_request_id) AS latest_id
                    FROM ppmp_revision_requests
                    GROUP BY ppmp_version_id
                ) latest ON latest.latest_id = rr.revision_request_id
            ) prr ON prr.ppmp_version_id = pv.ppmp_version_id

            WHERE p.fiscal_year_id = ?
            AND pv.status != 'Draft'
        ";

        if (!empty($status)) {
            if ($status === 'Approved') {
                $sql .= " AND pv.status = 'Approved' AND pv.consolidated_at IS NULL ";
            } elseif ($status === 'Consolidated') {
                $sql .= " AND pv.status IN ('Approved', 'Consolidated') AND pv.consolidated_at IS NOT NULL ";
            } elseif ($status === 'Pending') {
                $sql .= " AND (
                    pv.status = 'Pending'
                    OR prr.status = 'Requested'
                ) ";
            } else {
                $sql .= " AND pv.status = ? ";
            }
        }

        $sql .= " ORDER BY pv.submitted_at DESC, pv.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt)
            throw new Exception("Database error: " . $this->conn->error);

        if (!empty($status) && !in_array($status, ['Approved', 'Consolidated', 'Pending'], true)) {
            $stmt->bind_param("is", $fiscalYearId, $status);
        } else {
            $stmt->bind_param("i", $fiscalYearId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function approvePPMPRevisionRequest($ppmp_id, $processed_by)
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("
                SELECT p.ppmp_id, p.ppmp_code, p.current_version_id, p.fiscal_year_id,
                    pv.ppmp_version_id, pv.version_no, pv.status,
                    rr.revision_request_id, rr.revision_phase, rr.ppmp_version_id AS requested_version_id
                FROM ppmp p
                INNER JOIN ppmp_versions pv ON pv.ppmp_version_id = p.current_version_id
                INNER JOIN ppmp_revision_requests rr ON rr.ppmp_version_id = pv.ppmp_version_id
                WHERE p.ppmp_id = ? AND rr.status = 'Requested'
                ORDER BY rr.revision_request_id DESC
                LIMIT 1
            ");

            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("i", $ppmp_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data)
                throw new Exception("Revision request not found.");

            $ppmp_version_id = (int) $data['requested_version_id'];
            $revision_request_id = (int) $data['revision_request_id'];
            $fiscal_year_id = (int) $data['fiscal_year_id'];
            $revision_phase = (string) $data['revision_phase'];

            $stmt = $this->conn->prepare("
                UPDATE ppmp_revision_requests
                SET status = 'Approved', processed_by = ?, processed_at = NOW()
                WHERE revision_request_id = ?
            ");
            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("ii", $processed_by, $revision_request_id);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            $stmt->close();

            $stmt = $this->conn->prepare("
                UPDATE ppmp_versions
                SET status = 'Returned',
                    is_editable = 1,
                    returned_by = ?,
                    returned_at = NOW(),
                    return_reason = 'Revision request approved',
                    updated_at = NOW()
                WHERE ppmp_version_id = ?
            ");
            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("ii", $processed_by, $ppmp_version_id);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            $stmt->close();

            if ($revision_phase === 'Post-Finalization') {
                $stmt = $this->conn->prepare("
                    UPDATE fiscal_years
                    SET is_lock = 0
                    WHERE fiscal_year_id = ?
                ");
                if (!$stmt)
                    throw new Exception($this->conn->error);

                $stmt->bind_param("i", $fiscal_year_id);
                if (!$stmt->execute())
                    throw new Exception($stmt->error);
                $stmt->close();
            }

            $this->conn->commit();

            $this->recordActivityLog($processed_by, "Approved PPMP revision request", "ppmp", $ppmp_id);

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function rejectPPMPRevisionRequest($ppmp_id, $processed_by)
    {
        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("
                SELECT p.ppmp_id, p.ppmp_code, pv.ppmp_version_id,
                    rr.revision_request_id, rr.revision_phase
                FROM ppmp p
                INNER JOIN ppmp_versions pv ON pv.ppmp_version_id = p.current_version_id
                INNER JOIN ppmp_revision_requests rr ON rr.ppmp_version_id = pv.ppmp_version_id
                WHERE p.ppmp_id = ? AND rr.status = 'Requested'
                ORDER BY rr.revision_request_id DESC
                LIMIT 1
            ");

            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("i", $ppmp_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$data)
                throw new Exception("Revision request not found.");

            $revision_request_id = (int) $data['revision_request_id'];

            $stmt = $this->conn->prepare("
                UPDATE ppmp_revision_requests
                SET status = 'Rejected', processed_by = ?, processed_at = NOW()
                WHERE revision_request_id = ?
            ");

            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("ii", $processed_by, $revision_request_id);
            if (!$stmt->execute())
                throw new Exception($stmt->error);
            $stmt->close();

            $this->conn->commit();

            $this->recordActivityLog($processed_by, "Rejected PPMP revision request", "ppmp", $ppmp_id);

            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function getAllPPMPRecordsForReports($status = null, $fiscal_year_id = null)
    {
        if ($fiscal_year_id === null || $fiscal_year_id === '') {
            $currentFY = $this->getCurrentFiscalYear();
            if (!$currentFY) {
                return false;
            }
            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
        } else {
            $fiscal_year_id = (int) $fiscal_year_id;
        }

        $sql = "
            SELECT
                p.ppmp_id,
                p.ppmp_code,
                p.office_id,
                p.fiscal_year_id,
                p.current_version_id,
                p.created_by,

                pv.ppmp_version_id,
                pv.version_no,
                pv.total_amount,
                pv.status,
                pv.lifecycle_source,
                pv.is_editable,
                pv.submitted_at,
                pv.approved_at,
                pv.approve_reason,
                pv.returned_at,
                pv.return_reason,
                pv.rejected_at,
                pv.reject_reason,
                pv.consolidated_at,
                pv.finalized_at,
                pv.created_at,

                o.office_name,
                o.office_code,

                fy.year AS fiscal_year,

                u.first_name,
                u.last_name,
                u.email,
                u.phone,

                prr.status AS revision_request_status,
                prr.reason AS revision_request_reason,
                prr.created_at AS revision_requested_at,
                prr.processed_by AS revision_processed_by,
                prr.processed_at AS revision_processed_at

            FROM ppmp p
            INNER JOIN ppmp_versions pv
                ON pv.ppmp_version_id = p.current_version_id
            INNER JOIN offices o
                ON p.office_id = o.office_id
            INNER JOIN fiscal_years fy
                ON p.fiscal_year_id = fy.fiscal_year_id
            INNER JOIN users u
                ON p.created_by = u.user_id
            LEFT JOIN ppmp_revision_requests prr
                ON prr.ppmp_version_id = pv.ppmp_version_id

            WHERE p.fiscal_year_id = ?
            AND pv.status != 'Draft'
        ";

        if (!empty($status)) {
            $sql .= " AND pv.status = ?";
        }

        $sql .= " ORDER BY pv.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        if (!empty($status)) {
            $stmt->bind_param("is", $fiscal_year_id, $status);
        } else {
            $stmt->bind_param("i", $fiscal_year_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function updatePPMPStatus($ppmp_id, $status, $notes, $reviewed_by)
    {
        $validStatuses = ['Approved', 'Returned', 'Rejected'];

        if (!in_array($status, $validStatuses, true)) {
            return "Invalid PPMP status.";
        }

        $this->conn->begin_transaction();

        try {

            $stmt = $this->conn->prepare("
                SELECT
                    p.ppmp_id,
                    p.ppmp_code,
                    p.office_id,
                    p.fiscal_year_id,
                    p.current_version_id,

                    pv.ppmp_version_id,
                    pv.version_no,
                    pv.status,
                    pv.total_amount,
                    pv.lifecycle_source,
                    pv.is_editable,
                    pv.consolidated_at,
                    pv.finalized_at

                FROM ppmp p
                INNER JOIN ppmp_versions pv
                    ON pv.ppmp_version_id = p.current_version_id
                WHERE p.ppmp_id = ?
                LIMIT 1
            ");

            if (!$stmt)
                throw new Exception($this->conn->error);

            $stmt->bind_param("i", $ppmp_id);
            $stmt->execute();

            $ppmp = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$ppmp) {
                throw new Exception("PPMP not found.");
            }

            $versionId = (int) $ppmp['ppmp_version_id'];
            $officeId = (int) $ppmp['office_id'];
            $fiscalYearId = (int) $ppmp['fiscal_year_id'];
            $totalAmount = (float) $ppmp['total_amount'];

            if ($ppmp['status'] !== 'Pending') {
                throw new Exception("Only Pending PPMP can be reviewed.");
            }

            if (!empty($ppmp['consolidated_at'])) {
                throw new Exception("PPMP already consolidated.");
            }

            if ($status === 'Approved') {

                $stmt = $this->conn->prepare("
                    UPDATE ppmp_versions
                    SET
                        status = 'Approved',
                        approved_by = ?,
                        approved_at = NOW(),
                        approve_reason = ?,
                        is_editable = 0,
                        updated_at = NOW()
                    WHERE ppmp_version_id = ?
                ");

                if (!$stmt)
                    throw new Exception($this->conn->error);

                $stmt->bind_param("isi", $reviewed_by, $notes, $versionId);

            } elseif ($status === 'Returned') {

                $stmt = $this->conn->prepare("
                    UPDATE ppmp_versions
                    SET status = 'Returned',
                        returned_by = ?,
                        returned_at = NOW(),
                        return_reason = ?,
                        is_editable = 1,
                        updated_at = NOW()
                    WHERE ppmp_version_id = ?
                ");

                if (!$stmt)
                    throw new Exception($this->conn->error);

                $stmt->bind_param("isi", $reviewed_by, $notes, $versionId);

            } else {

                $stmt = $this->conn->prepare("
                    UPDATE ppmp_versions
                    SET status = 'Rejected',
                        rejected_by = ?,
                        rejected_at = NOW(),
                        reject_reason = ?,
                        is_editable = 1,
                        updated_at = NOW()
                    WHERE ppmp_version_id = ?
                ");

                if (!$stmt)
                    throw new Exception($this->conn->error);

                $stmt->bind_param("isi", $reviewed_by, $notes, $versionId);
            }

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $stmt->close();

            if ($status === 'Approved') {

                $allocStmt = $this->conn->prepare("
                    SELECT allocated_amount
                    FROM budget_allocation
                    WHERE office_id = ?
                    AND fiscal_year_id = ?
                    AND status = 'Approved'
                    ORDER BY allocation_id DESC
                    LIMIT 1
                ");

                if (!$allocStmt) {
                    throw new Exception($this->conn->error);
                }

                $allocStmt->bind_param("ii", $officeId, $fiscalYearId);
                $allocStmt->execute();

                $allocRes = $allocStmt->get_result()->fetch_assoc();
                $allocStmt->close();

                if (!$allocRes) {
                    throw new Exception("Budget allocation not found.");
                }

                $allocationAmount = (float) $allocRes['allocated_amount'];

                $usageStmt = $this->conn->prepare("
                    SELECT COALESCE(SUM(pv.total_amount), 0) AS used_budget
                    FROM ppmp p
                    INNER JOIN ppmp_versions pv
                        ON pv.ppmp_version_id = p.current_version_id
                    WHERE p.office_id = ?
                    AND p.fiscal_year_id = ?
                    AND pv.status IN ('Approved','Consolidated','Finalized')
                ");

                if (!$usageStmt) {
                    throw new Exception($this->conn->error);
                }

                $usageStmt->bind_param("ii", $officeId, $fiscalYearId);
                $usageStmt->execute();

                $usageRes = $usageStmt->get_result()->fetch_assoc();
                $usageStmt->close();

                $usedBudget = (float) $usageRes['used_budget'];

                $remainingAmount = $allocationAmount - $usedBudget;

                $updateBudgetStmt = $this->conn->prepare("
                    UPDATE budget_allocation
                    SET remaining_amount = ?
                    WHERE office_id = ?
                    AND fiscal_year_id = ?
                    AND status = 'Approved'
                ");

                if (!$updateBudgetStmt) {
                    throw new Exception($this->conn->error);
                }

                $updateBudgetStmt->bind_param(
                    "dii",
                    $remainingAmount,
                    $officeId,
                    $fiscalYearId
                );

                if (!$updateBudgetStmt->execute()) {
                    throw new Exception($updateBudgetStmt->error);
                }

                $updateBudgetStmt->close();
            }

            $stmt = $this->conn->prepare("
                UPDATE ppmp_revision_requests
                SET
                    processed_by = ?,
                    processed_at = NOW(),
                    status = CASE
                        WHEN ? = 'Returned' THEN 'Approved'
                        WHEN ? = 'Approved' THEN 'Approved'
                        ELSE status
                    END
                WHERE ppmp_version_id = ?
                AND status = 'Requested'
            ");

            if ($stmt) {
                $stmt->bind_param("issi", $reviewed_by, $status, $status, $versionId);
                $stmt->execute();
                $stmt->close();
            }

            $this->conn->commit();

            $this->recordActivityLog(
                $reviewed_by,
                "Reviewed PPMP {$ppmp['ppmp_code']} → {$status}",
                "ppmp",
                $ppmp_id
            );

            return true;

        } catch (Exception $e) {

            $this->conn->rollback();

            return $e->getMessage();
        }
    }

    public function getOfficesWithoutApprovedPPMP()
    {
        $currentFY = $this->getCurrentFiscalYear(true);
        if (!$currentFY) {
            return [];
        }

        $fiscal_year_id = (int) $currentFY['fiscal_year_id'];

        $sqlOffices = "
            SELECT DISTINCT o.office_id, o.office_name
            FROM offices o
            INNER JOIN budget_allocation b 
                ON b.office_id = o.office_id
            WHERE b.fiscal_year_id = ?
            AND o.is_active = 1
            AND LOWER(b.status) = 'approved'
        ";

        $stmt1 = $this->conn->prepare($sqlOffices);
        $stmt1->bind_param("i", $fiscal_year_id);
        $stmt1->execute();
        $officesResult = $stmt1->get_result();

        $offices = [];
        while ($row = $officesResult->fetch_assoc()) {
            $offices[(int) $row['office_id']] = $row['office_name'];
        }

        $stmt1->close();

        $sqlSubmitted = "
            SELECT DISTINCT p.office_id
            FROM ppmp p
            INNER JOIN ppmp_versions pv
                ON pv.ppmp_version_id = p.current_version_id
            WHERE p.fiscal_year_id = ?
            AND pv.status IN ('Approved', 'Consolidated', 'Finalized')
        ";

        $stmt2 = $this->conn->prepare($sqlSubmitted);
        $stmt2->bind_param("i", $fiscal_year_id);
        $stmt2->execute();
        $submittedResult = $stmt2->get_result();

        while ($row = $submittedResult->fetch_assoc()) {
            unset($offices[(int) $row['office_id']]);
        }


        $stmt2->close();

        return $offices;
    }

    public function insertNotificationIfNotExists($office_id, $fiscal_year_id, $message)
    {
        $count = 0;
        $sqlCheck = "SELECT COUNT(*) 
                    FROM notifications 
                    WHERE office_id = ? AND fiscal_year_id = ? AND message = ?";
        $stmt = $this->conn->prepare($sqlCheck);
        $stmt->bind_param("iis", $office_id, $fiscal_year_id, $message);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            $sqlInsert = "INSERT INTO notifications (office_id, fiscal_year_id, message, created_at) 
                        VALUES (?, ?, ?, NOW())";
            $stmt2 = $this->conn->prepare($sqlInsert);
            $stmt2->bind_param("iis", $office_id, $fiscal_year_id, $message);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    public function getRemainingAnnualBudget()
    {
        $currentFY = $this->getCurrentFiscalYear(true);
        if (!$currentFY) {
            return 0;
        }

        $fiscal_year_id = (int) $currentFY['fiscal_year_id'];

        $sql = "
            SELECT IFNULL(SUM(remaining_amount), 0) AS remaining_budget
            FROM budget_allocation
            WHERE fiscal_year_id = ?
            AND LOWER(status) = 'approved'
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();

        $remainingBudget = 0.0;
        $stmt->bind_result($remainingBudget);
        $stmt->fetch();
        $stmt->close();

        return (float) $remainingBudget;
    }

    public function generateAppCode($year)
    {
        $count = 0;

        $sql = "
            SELECT COUNT(*)
            FROM app_versions av
            INNER JOIN fiscal_years fy
                ON av.fiscal_year_id = fy.fiscal_year_id
            WHERE fy.year = ?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $year);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        $count++;
        return 'APP-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function ConsolidatePPMP($operator_ID)
    {
        $this->conn->begin_transaction();

        try {
            $currentFY = $this->validateFiscalYearForSubmission();

            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
            $year = (int) $currentFY['year'];

            // Check if APP already exists for this fiscal year
            $stmtAppCheck = $this->conn->prepare("
                SELECT COUNT(*) AS app_count
                FROM app_versions
                WHERE fiscal_year_id = ?
            ");

            if (!$stmtAppCheck) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmtAppCheck->bind_param("i", $fiscal_year_id);
            $stmtAppCheck->execute();
            $appCheck = $stmtAppCheck->get_result()->fetch_assoc();
            $stmtAppCheck->close();

            $hasExistingApp = ((int) $appCheck['app_count']) > 0;

            // Prevent late first-time PPMP from entering reconsolidation
            if ($hasExistingApp) {
                $stmtLate = $this->conn->prepare("
                    SELECT COUNT(*) AS late_count
                    FROM ppmp_versions pv
                    INNER JOIN ppmp p 
                        ON p.ppmp_id = pv.ppmp_id
                    WHERE p.fiscal_year_id = ?
                    AND pv.status = 'Approved'
                    AND pv.lifecycle_source = 'Initial Submission'
                    AND pv.consolidated_at IS NULL
                ");

                if (!$stmtLate) {
                    throw new Exception("Prepare failed: " . $this->conn->error);
                }

                $stmtLate->bind_param("i", $fiscal_year_id);
                $stmtLate->execute();
                $lateCheck = $stmtLate->get_result()->fetch_assoc();
                $stmtLate->close();

                if ((int) $lateCheck['late_count'] > 0) {
                    throw new Exception("Late PPMP detected. Requires revision approval workflow.");
                }
            }

            $sql = "
                SELECT
                    p.ppmp_id,
                    p.office_id,
                    p.ppmp_code,
                    pv.ppmp_version_id,
                    pv.version_no,
                    pv.status,

                    pvi.ppmp_version_item_id,
                    pvi.category_id,
                    pvi.sub_category_id,
                    pvi.item_name_id,
                    pvi.item_description,
                    pvi.quantity,
                    pvi.total_cost

                FROM ppmp p

                INNER JOIN (
                    SELECT
                        p2.office_id,
                        MAX(pv2.ppmp_version_id) AS latest_version_id
                    FROM ppmp p2
                    INNER JOIN ppmp_versions pv2
                        ON pv2.ppmp_id = p2.ppmp_id
                    WHERE p2.fiscal_year_id = ?
                    AND pv2.status IN ('Approved', 'Consolidated', 'Finalized')
                    GROUP BY p2.office_id
                ) latest
                    ON latest.office_id = p.office_id

                INNER JOIN ppmp_versions pv
                    ON pv.ppmp_version_id = latest.latest_version_id
                AND pv.ppmp_id = p.ppmp_id

                INNER JOIN ppmp_version_items pvi
                    ON pvi.ppmp_version_id = pv.ppmp_version_id

                WHERE p.fiscal_year_id = ?

                ORDER BY
                    pvi.category_id,
                    pvi.sub_category_id,
                    pvi.item_name_id,
                    p.office_id
            ";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param("ii", $fiscal_year_id, $fiscal_year_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            $stmt->close();

            if (empty($rows)) {
                throw new Exception("No approved PPMPs available for consolidation.");
            }

            $stmtFind = $this->conn->prepare("
                SELECT COALESCE(MAX(version_no), 0) AS max_version
                FROM app_versions
                WHERE fiscal_year_id = ?
            ");

            if (!$stmtFind) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmtFind->bind_param("i", $fiscal_year_id);
            $stmtFind->execute();
            $resFind = $stmtFind->get_result()->fetch_assoc();
            $stmtFind->close();

            $version_number = ((int) $resFind['max_version']) + 1;

            $appVersionStatus = 'Draft';
            $appNotes = $hasExistingApp
                ? 'Generated from APP reconsolidation'
                : 'Generated from approved PPMP consolidation';

            $stmtAppVersion = $this->conn->prepare("
                INSERT INTO app_versions (
                    fiscal_year_id,
                    version_no,
                    status,
                    based_on_app_version_id,
                    finalized_at,
                    created_by,
                    notes
                )
                VALUES (?, ?, ?, NULL, NULL, ?, ?)
            ");

            if (!$stmtAppVersion) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmtAppVersion->bind_param(
                "iisis",
                $fiscal_year_id,
                $version_number,
                $appVersionStatus,
                $operator_ID,
                $appNotes
            );

            if (!$stmtAppVersion->execute()) {
                throw new Exception("Failed to create APP version: " . $stmtAppVersion->error);
            }

            $app_version_id = (int) $stmtAppVersion->insert_id;
            $stmtAppVersion->close();

            $grouped = [];

            foreach ($rows as $row) {
                $key = implode('|', [
                    (int) $row['category_id'],
                    (int) ($row['sub_category_id'] ?? 0),
                    (int) $row['item_name_id']
                ]);

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'category_id' => (int) $row['category_id'],
                        'sub_category_id' => !empty($row['sub_category_id']) ? (int) $row['sub_category_id'] : null,
                        'item_name_id' => (int) $row['item_name_id'],
                        'item_description' => !empty(trim($row['item_description'] ?? ''))
                            ? trim($row['item_description'])
                            : 'No description',
                        'total_quantity' => 0,
                        'total_cost' => 0.00,
                        'sources' => []
                    ];
                }

                $grouped[$key]['total_quantity'] += (int) $row['quantity'];
                $grouped[$key]['total_cost'] += (float) $row['total_cost'];

                $grouped[$key]['sources'][] = [
                    'ppmp_version_id' => (int) $row['ppmp_version_id'],
                    'ppmp_version_item_id' => (int) $row['ppmp_version_item_id'],
                    'office_id' => (int) $row['office_id'],
                    'quantity' => (int) $row['quantity'],
                    'cost' => (float) $row['total_cost']
                ];
            }

            $stmtInsertAppItem = $this->conn->prepare("
                INSERT INTO app_items (
                    app_version_id,
                    category_id,
                    sub_category_id,
                    item_name_id,
                    item_description,
                    total_quantity,
                    total_cost
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmtInsertAppItem) {
                throw new Exception("Prepare failed inserting APP item: " . $this->conn->error);
            }

            $stmtInsertSource = $this->conn->prepare("
                INSERT INTO app_item_sources (
                    app_item_id,
                    ppmp_version_id,
                    ppmp_version_item_id,
                    office_id,
                    quantity,
                    cost
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$stmtInsertSource) {
                throw new Exception("Prepare failed inserting APP item source: " . $this->conn->error);
            }

            foreach ($grouped as $item) {
                $stmtInsertAppItem->bind_param(
                    "iiiisid",
                    $app_version_id,
                    $item['category_id'],
                    $item['sub_category_id'],
                    $item['item_name_id'],
                    $item['item_description'],
                    $item['total_quantity'],
                    $item['total_cost']
                );

                if (!$stmtInsertAppItem->execute()) {
                    throw new Exception("Failed to insert APP item: " . $stmtInsertAppItem->error);
                }

                $app_item_id = (int) $stmtInsertAppItem->insert_id;

                foreach ($item['sources'] as $src) {
                    $stmtInsertSource->bind_param(
                        "iiiiid",
                        $app_item_id,
                        $src['ppmp_version_id'],
                        $src['ppmp_version_item_id'],
                        $src['office_id'],
                        $src['quantity'],
                        $src['cost']
                    );

                    if (!$stmtInsertSource->execute()) {
                        throw new Exception("Failed to insert APP item source: " . $stmtInsertSource->error);
                    }
                }
            }

            $stmtInsertAppItem->close();
            $stmtInsertSource->close();

            $stmtMark = $this->conn->prepare("
                UPDATE ppmp_versions pv
                INNER JOIN ppmp p
                    ON p.current_version_id = pv.ppmp_version_id
                SET
                    pv.status = 'Consolidated',
                    pv.consolidated_at = NOW(),
                    pv.updated_at = NOW()
                WHERE p.fiscal_year_id = ?
                AND pv.status = 'Approved'
                AND pv.consolidated_at IS NULL
            ");

            if (!$stmtMark) {
                throw new Exception("Prepare failed marking PPMP as consolidated: " . $this->conn->error);
            }

            $stmtMark->bind_param("i", $fiscal_year_id);

            if (!$stmtMark->execute()) {
                throw new Exception("Failed to mark PPMP versions as consolidated: " . $stmtMark->error);
            }

            $stmtMark->close();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            return $e->getMessage();
        }
    }

    public function getOfficeBudgetOverviewFilter($userId, $fiscal_year_id = null)
    {
        $officeId = $this->getOfficeIdByHead($userId);

        if (!$officeId) {
            return null;
        }

        if ($fiscal_year_id === null || $fiscal_year_id === '') {
            $currentFY = $this->getCurrentFiscalYear(true);
            if (!$currentFY) {
                return null;
            }
            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
            $fiscalYearText = $currentFY['year'];
        } else {
            $fiscal_year_id = (int) $fiscal_year_id;
            $fy = $this->getFiscalYearById($fiscal_year_id);
            $fiscalYearText = $fy['year'] ?? '';
        }

        $sql = "
        SELECT allocated_amount, remaining_amount
        FROM budget_allocation
        WHERE office_id = ?
          AND fiscal_year_id = ?
          AND status = 'Approved'
        ORDER BY allocation_id DESC
        LIMIT 1
    ";

        $allocatedAmount = 0;
        $remainingAmount = 0;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $officeId, $fiscal_year_id);
        $stmt->execute();
        $stmt->bind_result($allocatedAmount, $remainingAmount);
        $stmt->fetch();
        $stmt->close();

        $sqlPPMP = "
        SELECT COALESCE(pv.total_amount, 0) AS total_amount
        FROM ppmp p
        INNER JOIN ppmp_versions pv 
            ON pv.ppmp_version_id = p.current_version_id
        WHERE p.office_id = ?
          AND p.fiscal_year_id = ?
          AND pv.status IN ('Pending', 'Approved', 'Returned', 'Rejected', 'Draft')
        ORDER BY p.ppmp_id DESC
        LIMIT 1
    ";

        $ppmpTotal = 0;

        $stmt = $this->conn->prepare($sqlPPMP);
        $stmt->bind_param("ii", $officeId, $fiscal_year_id);
        $stmt->execute();
        $stmt->bind_result($ppmpTotal);
        $stmt->fetch();
        $stmt->close();

        return [
            "office_id" => $officeId,
            "office_name" => $this->getOfficeName($officeId),
            "fiscal_year" => $fiscalYearText,
            "allocated_amount" => (float) $allocatedAmount,
            "remaining_amount" => (float) $remainingAmount,
            "ppmp_total_amount" => (float) $ppmpTotal
        ];
    }

    public function getOfficeBudgetOverviewByUser($userId, $fiscal_year_id = null)
    {
        $overview = $this->getOfficeBudgetOverviewFilter($userId, $fiscal_year_id);
        if (!$overview) {
            return false;
        }

        return [
            'fiscal_year' => $overview['fiscal_year'],
            'allocated_amount' => (float) $overview['allocated_amount'],
            'ppmp_total_amount' => (float) $overview['ppmp_total_amount'],
            'remaining_amount' => (float) $overview['remaining_amount']
        ];
    }

    public function getPPMPItemsByUser($userId, $fiscal_year_id = null)
    {
        if ($fiscal_year_id === null || $fiscal_year_id === '') {
            $currentFY = $this->getCurrentFiscalYear(true);
            if (!$currentFY) {
                return false;
            }
            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
        } else {
            $fiscal_year_id = (int) $fiscal_year_id;
        }

        $sql = "
        SELECT 
            inames.item_name,
            SUM(pvi.quantity) AS total_quantity,
            SUM(pvi.total_cost) AS total_cost
        FROM ppmp p
        INNER JOIN ppmp_versions pv 
            ON pv.ppmp_version_id = p.current_version_id
        INNER JOIN ppmp_version_items pvi 
            ON pvi.ppmp_version_id = pv.ppmp_version_id
        INNER JOIN item_names inames 
            ON pvi.item_name_id = inames.item_name_id
        WHERE p.created_by = ?
          AND p.fiscal_year_id = ?
          AND pv.status != 'Archived'
        GROUP BY pvi.item_name_id, inames.item_name
        ORDER BY total_cost DESC
    ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $userId, $fiscal_year_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    // used in ppmp-consolidated.php
    public function getConsolidatedPPMPItems($fiscal_year_id = null)
    {
        if ($fiscal_year_id === null) {
            $fiscal_year = $this->getCurrentFiscalYear(true);

            if (!$fiscal_year) {
                return $this->conn->query("SELECT 1 WHERE 0");
            }

            $fiscal_year_id = (int) $fiscal_year['fiscal_year_id'];
        }

        $sql = "
            SELECT 
                ai.app_item_id,
                ai.app_version_id,
                ai.total_quantity,
                ai.total_cost,
                GROUP_CONCAT(DISTINCT ais.office_id ORDER BY ais.office_id SEPARATOR ',') AS offices_involved,

                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,

                av.app_version_id,
                av.version_no,
                av.status AS app_version_status,

                COALESCE(ai.item_description, inn.item_name) AS item_description,
                ai.mode_of_procurement,
                ai.pre_procurement_conference,
                ai.bid_cat_id AS bid_cat_ID,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.proc_strat_id AS proc_strat_ID,
                ai.remarks

            FROM app_items ai
            INNER JOIN app_versions av
                ON ai.app_version_id = av.app_version_id
            LEFT JOIN app_item_sources ais
                ON ai.app_item_id = ais.app_item_id
            INNER JOIN item_categories ic
                ON ai.category_id = ic.category_id
            LEFT JOIN sub_categories sc
                ON ai.sub_category_id = sc.sub_category_id
            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id

            WHERE av.fiscal_year_id = ?
            AND av.status = 'Draft'
            AND av.version_no = (
                SELECT MAX(av2.version_no)
                FROM app_versions av2
                WHERE av2.fiscal_year_id = ?
                    AND av2.status = 'Draft'
            )

            GROUP BY
                ai.app_item_id,
                ai.app_version_id,
                ai.total_quantity,
                ai.total_cost,
                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,
                av.app_version_id,
                av.version_no,
                av.status,
                ai.item_description,
                ai.mode_of_procurement,
                ai.pre_procurement_conference,
                ai.bid_cat_id,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.proc_strat_id,
                ai.remarks

            ORDER BY ic.category_name, sc.sub_cat_name, inn.item_name
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $fiscal_year_id, $fiscal_year_id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getFinalizedAPPItems($fiscal_year_id = null)
    {
        if ($fiscal_year_id === null) {
            $fiscal_year = $this->getCurrentFiscalYear(true);

            if (!$fiscal_year) {
                return $this->conn->query("SELECT 1 WHERE 0");
            }

            $fiscal_year_id = (int)$fiscal_year['fiscal_year_id'];
        }

        $sql = "
            SELECT
                inn.item_name,
                SUM(ai.total_cost) AS total_cost
            FROM app_items ai
            INNER JOIN app_versions av
                ON ai.app_version_id = av.app_version_id
            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id

            WHERE av.fiscal_year_id = ?
            AND av.status = 'Finalized'
            AND av.version_no = (
                SELECT MAX(av2.version_no)
                FROM app_versions av2
                WHERE av2.fiscal_year_id = ?
                AND av2.status = 'Finalized'
            )

            GROUP BY inn.item_name
            ORDER BY total_cost DESC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $fiscal_year_id, $fiscal_year_id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function checkConsolidatedPPMPBySubcategory($sub_category_id)
    {
        $currentFY = $this->getCurrentFiscalYear(true);
        if (!$currentFY)
            return false;

        $fiscal_year_id = (int) $currentFY['fiscal_year_id'];

        $sql = "
        SELECT COUNT(DISTINCT ai.app_item_id) AS total
        FROM app_items ai
        INNER JOIN app a
            ON ai.app_id = a.app_id
        INNER JOIN app_versions av
            ON a.app_version_id = av.app_version_id
        WHERE a.fiscal_year_id = ?
          AND ai.sub_category_id = ?
    ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $fiscal_year_id, $sub_category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0) > 0;
    }

    public function getConsolidatedItemNamesBySubcategory($fiscal_year_id, $sub_category_id)
    {
        $sql = "
            SELECT DISTINCT
                ai.item_name_id,
                inn.item_name
            FROM app_items ai
            INNER JOIN app a
                ON ai.app_id = a.app_id
            INNER JOIN app_versions av
                ON a.app_version_id = av.app_version_id
            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id
            WHERE a.fiscal_year_id = ?
            AND ai.sub_category_id = ?
            AND av.status = 'Draft'
            ORDER BY inn.item_name ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $fiscal_year_id, $sub_category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();

        return $rows;
    }

    public function getOfficesInvolvedForConsolidatedItem($fiscal_year_id, $sub_category_id, $item_name_id)
    {
        $ids = $this->getOfficeIdsInvolvedForConsolidatedItem($fiscal_year_id, $sub_category_id, $item_name_id);
        if (empty($ids)) {
            return [];
        }
        return $this->getOfficesByIds($ids);
    }

    public function getOfficeIdsInvolvedForConsolidatedItem($fiscal_year_id, $sub_category_id, $item_name_id)
    {
        $sql = "
            SELECT DISTINCT ais.office_id
            FROM app_items ai
            INNER JOIN app a
                ON ai.app_id = a.app_id
            INNER JOIN app_versions av
                ON a.app_version_id = av.app_version_id
            INNER JOIN app_item_sources ais
                ON ai.app_item_id = ais.app_item_id
            INNER JOIN ppmp_versions pv
                ON ais.ppmp_version_id = pv.ppmp_version_id
            WHERE a.fiscal_year_id = ?
            AND ai.sub_category_id = ?
            AND ai.item_name_id = ?
            AND av.status = 'Draft'
            AND pv.status = 'Approved'
            AND pv.consolidated_at IS NOT NULL
            ORDER BY ais.office_id ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("iii", $fiscal_year_id, $sub_category_id, $item_name_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $ids = [];
        while ($row = $res->fetch_assoc()) {
            $office_id = (int) ($row['office_id'] ?? 0);
            if ($office_id > 0) {
                $ids[] = $office_id;
            }
        }

        $stmt->close();

        $ids = array_values(array_unique($ids));
        sort($ids);

        return $ids;
    }

    public function getOfficesByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $sql = "SELECT office_id, office_name
            FROM offices
            WHERE office_id IN ($placeholders) AND is_active = 1
            ORDER BY office_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();

        $res = $stmt->get_result();
        $rows = [];

        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    // ALL METHODS HERE ARE TO UPDATE PPMP ITEMS TO BECOME OFFICALLY APP
    public function getCurrentDraftAppByFiscalYear($fiscal_year_id)
    {
        $fiscal_year_id = (int) $fiscal_year_id;

        $sql = "
            SELECT 
                av.app_version_id,
                av.fiscal_year_id,
                av.version_no,
                av.status AS version_status,
                av.created_at
            FROM app_versions av
            WHERE av.fiscal_year_id = ?
            AND av.status = 'Draft'
            ORDER BY av.version_no DESC, av.app_version_id DESC
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();

        return $row ?: false;
    }

    public function updateDraftAppItemDetails($app_item_id, $data)
    {
        $app_item_id = (int) $app_item_id;

        $sql = "
            UPDATE app_items ai
            INNER JOIN app_versions av
                ON ai.app_version_id = av.app_version_id
            SET
                ai.item_description = ?,
                ai.mode_of_procurement = ?,
                ai.pre_procurement_conference = ?,
                ai.bid_cat_id = ?,
                ai.procurement_start_date = ?,
                ai.bidding_date = ?,
                ai.source_of_funds = ?,
                ai.proc_strat_id = ?,
                ai.remarks = ?
            WHERE ai.app_item_id = ?
            AND av.status = 'Draft'
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $item_description = trim($data['item_description'] ?? '');
        $mode_of_procurement = (int) ($data['mode_of_procurement'] ?? 0);
        $pre_procurement_conference = trim($data['pre_procurement_conference'] ?? '');
        $bid_cat_ID = (int) ($data['bid_cat_ID'] ?? 0);
        $procurement_start_date = $data['procurement_start_date'] ?? null;
        $bidding_date = $data['bidding_date'] ?? null;
        $source_of_funds = trim($data['source_of_funds'] ?? '');
        $proc_strat_ID = (int) ($data['proc_strat_ID'] ?? 0);
        $remarks = trim($data['remarks'] ?? '');

        $stmt->bind_param(
            "sisisssisi",
            $item_description,
            $mode_of_procurement,
            $pre_procurement_conference,
            $bid_cat_ID,
            $procurement_start_date,
            $bidding_date,
            $source_of_funds,
            $proc_strat_ID,
            $remarks,
            $app_item_id
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Failed to update APP item: " . $error);
        }

        $stmt->close();
        return true;
    }

    public function isDraftAppReadyForFinalization($app_version_id)
    {
        $app_version_id = (int) $app_version_id;

        $sql = "
            SELECT COUNT(*) AS incomplete_count
            FROM app_items
            WHERE app_version_id = ?
            AND (
                    item_description IS NULL OR item_description = ''
                OR mode_of_procurement IS NULL OR mode_of_procurement = 0
                OR pre_procurement_conference IS NULL OR pre_procurement_conference = ''
                OR bid_cat_id IS NULL OR bid_cat_id = 0
                OR procurement_start_date IS NULL
                OR bidding_date IS NULL
                OR source_of_funds IS NULL OR source_of_funds = ''
                OR proc_strat_id IS NULL OR proc_strat_id = 0
            )
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $app_version_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return ((int) $result['incomplete_count'] === 0);
    }

    public function finalizeAppVersionAndApp($app_version_id)
    {
        $app_version_id = (int) $app_version_id;

        $stmtFetch = $this->conn->prepare("
            SELECT fiscal_year_id
            FROM app_versions
            WHERE app_version_id = ?
            AND status = 'Draft'
            LIMIT 1
        ");

        if (!$stmtFetch) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmtFetch->bind_param("i", $app_version_id);
        $stmtFetch->execute();

        $result = $stmtFetch->get_result();
        $row = $result->fetch_assoc();

        $stmtFetch->close();

        if (!$row) {
            throw new Exception("Draft APP version not found.");
        }

        $fiscal_year_id = (int) $row['fiscal_year_id'];

        $stmtVersion = $this->conn->prepare("
            UPDATE app_versions
            SET status = 'Finalized',
                finalized_at = NOW()
            WHERE app_version_id = ?
        ");

        if (!$stmtVersion) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmtVersion->bind_param("i", $app_version_id);

        if (!$stmtVersion->execute()) {
            throw new Exception($stmtVersion->error);
        }

        $stmtVersion->close();

        $stmtLock = $this->conn->prepare("
            UPDATE fiscal_years
            SET is_lock = 1
            WHERE fiscal_year_id = ?
        ");

        if (!$stmtLock) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmtLock->bind_param("i", $fiscal_year_id);

        if (!$stmtLock->execute()) {
            throw new Exception($stmtLock->error);
        }

        $stmtLock->close();

        $stmtPPMP = $this->conn->prepare("
            UPDATE ppmp_versions pv
            INNER JOIN ppmp p
                ON p.current_version_id = pv.ppmp_version_id
            SET
                pv.status = 'Finalized',
                pv.finalized_at = NOW(),
                pv.updated_at = NOW()
            WHERE p.fiscal_year_id = ?
            AND pv.consolidated_at IS NOT NULL
            AND pv.status = 'Consolidated'
        ");

        if (!$stmtPPMP) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmtPPMP->bind_param("i", $fiscal_year_id);

        if (!$stmtPPMP->execute()) {
            throw new Exception($stmtPPMP->error);
        }

        $stmtPPMP->close();

        return true;
    }
    // END ALL METHODS HERE ARE TO UPDATE PPMP ITEMS TO BECOME OFFICALLY APP

    public function getAllPPMPRecords($ppmp_id = null, $fiscal_year_id = null)
    {
        if ($fiscal_year_id === null) {
            $fiscal_year_id = $this->getCurrentFiscalYearId(true);
        }

        if (!$fiscal_year_id) {
            return $this->conn->query("SELECT 1 WHERE 0");
        }

        $sql = "
        SELECT 
            p.ppmp_id,
            p.ppmp_code,
            p.created_at,

            o.office_name,
            o.office_code,

            fy.year AS fiscal_year,

            u.first_name,
            u.last_name,
            u.email,
            u.phone,

            pv.status,
            COALESCE(pv.total_amount, 0) AS total_amount

        FROM ppmp p

        INNER JOIN offices o 
            ON p.office_id = o.office_id

        INNER JOIN fiscal_years fy 
            ON p.fiscal_year_id = fy.fiscal_year_id

        INNER JOIN users u 
            ON p.created_by = u.user_id

        LEFT JOIN ppmp_versions pv 
            ON p.current_version_id = pv.ppmp_version_id

        WHERE p.fiscal_year_id = ?
    ";

        if (!empty($ppmp_id)) {
            $sql .= " AND p.ppmp_id = ?";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        if (!empty($ppmp_id)) {
            $stmt->bind_param("ii", $fiscal_year_id, $ppmp_id);
        } else {
            $stmt->bind_param("i", $fiscal_year_id);
        }

        $stmt->execute();

        return $stmt->get_result();
    }

    public function getAllPPMPRecordsForProcHeadForExporting($status = null, $office_id = null, $fiscal_year_id = null)
    {
        if ($fiscal_year_id === null || $fiscal_year_id === '') {
            $currentFY = $this->getCurrentFiscalYear();
            if (!$currentFY) {
                return false;
            }
            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
        }

        $sql = "
            SELECT
                p.ppmp_id,
                p.ppmp_code,
                p.office_id,
                p.fiscal_year_id,
                p.current_version_id,

                pv.ppmp_version_id,
                pv.version_no,
                pv.status,
                pv.total_amount,
                pv.submitted_at,
                pv.approved_at,
                pv.consolidated_at,
                pv.finalized_at,
                pv.created_at,

                o.office_name,
                o.office_code,

                fy.year AS fiscal_year,

                u.first_name,
                u.last_name,
                u.email,
                u.phone

            FROM ppmp p
            INNER JOIN ppmp_versions pv
                ON p.current_version_id = pv.ppmp_version_id
            INNER JOIN offices o
                ON p.office_id = o.office_id
            INNER JOIN fiscal_years fy
                ON p.fiscal_year_id = fy.fiscal_year_id
            INNER JOIN users u
                ON p.created_by = u.user_id

            WHERE p.fiscal_year_id = ?
            AND pv.status != 'Draft'
        ";

        $params = [$fiscal_year_id];
        $types = "i";

        if (!empty($status)) {
            $sql .= " AND pv.status = ?";
            $types .= "s";
            $params[] = $status;
        }

        if (!empty($office_id)) {
            $sql .= " AND p.office_id = ?";
            $types .= "i";
            $params[] = (int) $office_id;
        }

        $sql .= " ORDER BY pv.submitted_at DESC, pv.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $this->conn->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getFinalizedApps($fiscal_year_id = null)
    {
        if ($fiscal_year_id === null) {
            $fiscal_year = $this->getCurrentFiscalYear();

            if (!$fiscal_year) {
                return $this->conn->query("SELECT 1 WHERE 0");
            }

            $fiscal_year_id = (int) $fiscal_year['fiscal_year_id'];
        }

        $sql = "
            SELECT 
                ai.app_item_id,
                ai.app_version_id,
                ai.total_quantity,
                ai.total_cost,
                GROUP_CONCAT(DISTINCT ais.office_id ORDER BY ais.office_id SEPARATOR ',') AS offices_involved,

                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,

                av.app_version_id,
                av.version_no,
                av.status AS app_version_status,
                av.finalized_at,

                ai.item_description,
                ai.mode_of_procurement,
                pm.proc_mode_name,
                ai.pre_procurement_conference,
                ai.bid_cat_id AS bid_cat_ID,
                bc.bid_cat_name,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.proc_strat_id AS proc_strat_ID,
                ps.proc_strat_name,
                ai.remarks

            FROM app_items ai

            INNER JOIN app_versions av
                ON ai.app_version_id = av.app_version_id

            LEFT JOIN app_item_sources ais
                ON ai.app_item_id = ais.app_item_id

            INNER JOIN item_categories ic
                ON ai.category_id = ic.category_id

            LEFT JOIN sub_categories sc
                ON ai.sub_category_id = sc.sub_category_id

            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id

            LEFT JOIN procurement_modes pm
                ON ai.mode_of_procurement = pm.proc_mode_id

            LEFT JOIN bidding_category bc
                ON ai.bid_cat_id = bc.bid_cat_ID

            LEFT JOIN procurement_strategy ps
                ON ai.proc_strat_id = ps.proc_strat_ID

            WHERE av.fiscal_year_id = ?
            AND av.status = 'Finalized'
            AND av.app_version_id = (
                SELECT MAX(av2.app_version_id)
                FROM app_versions av2
                WHERE av2.fiscal_year_id = ?
                    AND av2.status = 'Finalized'
            )

            GROUP BY
                ai.app_item_id,
                ai.app_version_id,
                ai.total_quantity,
                ai.total_cost,
                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,
                av.app_version_id,
                av.version_no,
                av.status,
                av.finalized_at,
                ai.item_description,
                ai.mode_of_procurement,
                pm.proc_mode_name,
                ai.pre_procurement_conference,
                ai.bid_cat_id,
                bc.bid_cat_name,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.proc_strat_id,
                ps.proc_strat_name,
                ai.remarks

            ORDER BY ic.category_name, sc.sub_cat_name, inn.item_name
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $fiscal_year_id, $fiscal_year_id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getFinalizedAppsByProcurementType($fiscal_year_id, $procurementKeyword)
    {
        $sql = "
            SELECT
                ai.app_item_id,
                ai.total_cost,
                ai.item_description,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.remarks,
                ai.pre_procurement_conference,

                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,

                pm.proc_mode_name,
                bc.bid_cat_name,
                ps.proc_strat_name,

                GROUP_CONCAT(DISTINCT ais.office_id ORDER BY ais.office_id SEPARATOR ',') AS offices_involved

            FROM app_items ai

            INNER JOIN app_versions av
                ON ai.app_version_id = av.app_version_id

            LEFT JOIN app_item_sources ais
                ON ai.app_item_id = ais.app_item_id

            INNER JOIN item_categories ic
                ON ai.category_id = ic.category_id

            LEFT JOIN sub_categories sc
                ON ai.sub_category_id = sc.sub_category_id

            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id

            LEFT JOIN procurement_modes pm
                ON ai.mode_of_procurement = pm.proc_mode_id

            LEFT JOIN bidding_category bc
                ON ai.bid_cat_id = bc.bid_cat_ID

            LEFT JOIN procurement_strategy ps
                ON ai.proc_strat_id = ps.proc_strat_ID

            WHERE av.fiscal_year_id = ?
            AND av.status = 'Finalized'
            AND pm.proc_mode_name LIKE ?
            AND av.app_version_id = (
                SELECT MAX(av2.app_version_id)
                FROM app_versions av2
                WHERE av2.fiscal_year_id = ?
                AND av2.status = 'Finalized'
            )

            GROUP BY ai.app_item_id
            ORDER BY ic.category_name, sc.sub_cat_name, inn.item_name
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $keyword = '%' . $procurementKeyword . '%';

        $stmt->bind_param("isi", $fiscal_year_id, $keyword, $fiscal_year_id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }
    // REPORTS-APP-VERSIONS.PHP
    public function getFinalizedAppsForReports($fiscal_year_id = null)
    {
        if ($fiscal_year_id === null) {
            $currentFY = $this->getCurrentFiscalYear(true);
            if (!$currentFY) {
                return false;
            }
            $fiscal_year_id = (int) $currentFY['fiscal_year_id'];
        }

        $sql = "
            SELECT 
                ai.app_item_id,
                ai.total_quantity,
                ai.total_cost,
                GROUP_CONCAT(DISTINCT ais.office_id ORDER BY ais.office_id SEPARATOR ',') AS offices_involved,

                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,

                a.app_id,
                a.app_code,
                a.status AS app_status,
                av.app_version_id,
                av.version_number,
                av.status AS app_version_status,
                av.finalized_date,

                ai.item_description,
                ai.mode_of_procurement,
                pm.proc_mode_name,
                ai.pre_procurement_conference,
                ai.bid_cat_ID,
                bc.bid_cat_name,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.proc_strat_ID,
                ps.proc_strat_name,
                ai.remarks

            FROM app_items ai
            INNER JOIN app a
                ON ai.app_id = a.app_id
            INNER JOIN app_versions av
                ON a.app_version_id = av.app_version_id
            LEFT JOIN app_item_sources ais
                ON ai.app_item_id = ais.app_item_id
            INNER JOIN item_categories ic
                ON ai.category_id = ic.category_id
            LEFT JOIN sub_categories sc
                ON ai.sub_category_id = sc.sub_category_id
            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id
            LEFT JOIN procurement_modes pm
                ON ai.mode_of_procurement = pm.proc_mode_id
            LEFT JOIN bidding_category bc
                ON ai.bid_cat_ID = bc.bid_cat_ID
            LEFT JOIN procurement_strategy ps
                ON ai.proc_strat_ID = ps.proc_strat_ID

            WHERE av.fiscal_year_id = ?
            AND a.fiscal_year_id = ?
            AND a.status = 'Final'
            AND av.status = 'Finalized'

            GROUP BY
                ai.app_item_id,
                ai.total_quantity,
                ai.total_cost,
                ic.category_name,
                ic.category_code,
                sc.sub_cat_name,
                inn.item_name,
                a.app_id,
                a.app_code,
                a.status,
                av.app_version_id,
                av.version_number,
                av.status,
                av.finalized_date,
                ai.item_description,
                ai.mode_of_procurement,
                pm.proc_mode_name,
                ai.pre_procurement_conference,
                ai.bid_cat_ID,
                bc.bid_cat_name,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ai.proc_strat_ID,
                ps.proc_strat_name,
                ai.remarks

            ORDER BY av.version_number DESC, a.app_code ASC, ic.category_name ASC, sc.sub_cat_name ASC, inn.item_name ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $fiscal_year_id, $fiscal_year_id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getAppVersionsByFiscalYear($fiscal_year_id)
    {
        $fiscal_year_id = (int) $fiscal_year_id;

        $sql = "
            SELECT
                av.app_version_id,
                av.version_no,
                av.status,
                av.notes,
                av.finalized_at,
                av.created_at,

                COUNT(DISTINCT ai.app_item_id) AS total_items,
                COALESCE(SUM(ai.total_cost), 0) AS total_budget

            FROM app_versions av
            LEFT JOIN app_items ai
                ON ai.app_version_id = av.app_version_id

            WHERE av.fiscal_year_id = ?

            GROUP BY
                av.app_version_id,
                av.version_no,
                av.status,
                av.notes,
                av.finalized_at,
                av.created_at

            ORDER BY av.version_no DESC, av.app_version_id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $fiscal_year_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getAppVersionItems($app_version_id)
    {
        $sql = "
            SELECT
                ai.app_item_id,
                ai.total_quantity,
                ai.total_cost,
                GROUP_CONCAT(DISTINCT ais.office_id ORDER BY ais.office_id SEPARATOR ',') AS offices_involved,

                ic.category_name,
                sc.sub_cat_name,
                inn.item_name,

                ai.item_description,
                pm.proc_mode_name,
                ai.pre_procurement_conference,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ps.proc_strat_name,
                ai.remarks

            FROM app_items ai

            LEFT JOIN app_item_sources ais
                ON ai.app_item_id = ais.app_item_id

            INNER JOIN item_categories ic
                ON ai.category_id = ic.category_id

            LEFT JOIN sub_categories sc
                ON ai.sub_category_id = sc.sub_category_id

            INNER JOIN item_names inn
                ON ai.item_name_id = inn.item_name_id

            LEFT JOIN procurement_modes pm
                ON ai.mode_of_procurement = pm.proc_mode_id

            LEFT JOIN procurement_strategy ps
                ON ai.proc_strat_id = ps.proc_strat_ID

            WHERE ai.app_version_id = ?

            GROUP BY
                ai.app_item_id,
                ai.total_quantity,
                ai.total_cost,
                ic.category_name,
                sc.sub_cat_name,
                inn.item_name,
                ai.item_description,
                pm.proc_mode_name,
                ai.pre_procurement_conference,
                ai.procurement_start_date,
                ai.bidding_date,
                ai.source_of_funds,
                ps.proc_strat_name,
                ai.remarks

            ORDER BY ic.category_name, sc.sub_cat_name, inn.item_name
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $app_version_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getAppItemOfficePPMPSources($app_item_id)
    {
        $sql = "
            SELECT
                ais.office_id,
                o.office_name,
                o.office_code,
                CASE
                    WHEN o.office_name = 'Office of the President' THEN 'OP'
                    WHEN o.office_name = 'Office of the Vice President for Academic Affairs' THEN 'OVPAA'
                    WHEN o.office_name = 'Office of the Vice President for Planning, Development and Special Concerns' THEN 'OVPPDSC'
                    WHEN o.office_name = 'Office of the Vice President for Research and Extension' THEN 'OVPRE'
                    ELSE o.office_name
                END AS office_display,
                ai.item_description AS app_item_description,
                p.ppmp_id,
                p.ppmp_code,
                pv.ppmp_version_id,
                pvi.ppmp_version_item_id,
                pvi.item_description,
                pvi.specifications,
                pvi.quantity,
                pvi.estimated_budget,
                pvi.total_cost,
                pvi.file_attachment
            FROM app_item_sources ais
            INNER JOIN app_items ai ON ais.app_item_id = ai.app_item_id
            INNER JOIN offices o ON ais.office_id = o.office_id
            INNER JOIN ppmp_versions pv ON ais.ppmp_version_id = pv.ppmp_version_id
            INNER JOIN ppmp p ON pv.ppmp_id = p.ppmp_id
            INNER JOIN ppmp_version_items pvi ON ais.ppmp_version_item_id = pvi.ppmp_version_item_id
            WHERE ais.app_item_id = ?
            GROUP BY ais.office_id, ais.ppmp_version_item_id
            ORDER BY o.office_name ASC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $app_item_id);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getSubmittedPPMPItemsByOffice($office_id)
    {
        $sql = "
            SELECT
                o.office_id,
                o.office_name,
                o.office_code,
                CASE
                    WHEN o.office_name = 'Office of the President' THEN 'OP'
                    WHEN o.office_name = 'Office of the Vice President for Academic Affairs' THEN 'OVPAA'
                    WHEN o.office_name = 'Office of the Vice President for Planning, Development and Special Concerns' THEN 'OVPPDSC'
                    WHEN o.office_name = 'Office of the Vice President for Research and Extension' THEN 'OVPRE'
                    ELSE o.office_name
                END AS office_display,
                p.ppmp_id,
                p.ppmp_code,
                pv.ppmp_version_id,
                pvi.ppmp_version_item_id,
                pvi.item_description,
                pvi.specifications,
                pvi.quantity,
                pvi.estimated_budget,
                pvi.total_cost,
                pvi.file_attachment
            FROM ppmp p
            INNER JOIN offices o ON p.office_id = o.office_id
            INNER JOIN ppmp_versions pv ON p.current_version_id = pv.ppmp_version_id
            INNER JOIN ppmp_version_items pvi ON pv.ppmp_version_id = pvi.ppmp_version_id
            WHERE p.office_id = ?
            AND pv.status != 'Archived'
            ORDER BY p.ppmp_code ASC, pvi.ppmp_version_item_id ASC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $office_id);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function AllowPPMPRevision($office_ids, $operator_ID)
{
    if (!is_array($office_ids)) {
        $office_ids = [$office_ids];
    }

    $office_ids = array_values(array_unique(array_filter(array_map('intval', $office_ids))));

    if (empty($office_ids)) {
        return "Invalid office selected.";
    }

    $placeholders = implode(',', array_fill(0, count($office_ids), '?'));
    $types = str_repeat('i', count($office_ids));

    $check_sql = "
        SELECT COUNT(*)
        FROM ppmp p
        INNER JOIN ppmp_versions pv 
            ON pv.ppmp_version_id = p.current_version_id
        WHERE p.office_id IN ($placeholders)
        AND pv.status = 'Finalized'
    ";
    $count = 0;
    $check_stmt = $this->conn->prepare($check_sql);
    $check_stmt->bind_param($types, ...$office_ids);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count == 0) {
        return "No finalized PPMP found for the selected sector/s.";
    }

    $update_sql = "
        UPDATE ppmp_versions pv
        INNER JOIN ppmp p 
            ON p.current_version_id = pv.ppmp_version_id
        SET 
            pv.status = 'Returned',
            pv.is_editable = 1,
            pv.lifecycle_source = 'Post-APP Revision',
            pv.returned_by = ?,
            pv.returned_at = NOW(),
            pv.return_reason = 'APP revision allowed by admin',
            pv.updated_at = NOW()
        WHERE 
            p.office_id IN ($placeholders)
            AND pv.status = 'Finalized'
    ";

    $stmt = $this->conn->prepare($update_sql);

    $bind_types = 'i' . $types;
    $bind_values = array_merge([$operator_ID], $office_ids);

    $stmt->bind_param($bind_types, ...$bind_values);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return "Failed to enable revision: " . $error;
    }

    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected <= 0) {
        return "No records updated.";
    }

    $this->recordActivityLog(
        $operator_ID,
        "Enabled PPMP revision for office IDs: " . implode(', ', $office_ids),
        "ppmp_versions",
        0
    );

    return true;
}

    public function GetSectorsWithFinalizedPPMP()
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT o.office_id, o.office_name
            FROM ppmp p
            INNER JOIN ppmp_versions pv 
                ON pv.ppmp_version_id = p.current_version_id
            INNER JOIN offices o 
                ON o.office_id = p.office_id
            WHERE pv.status = 'Finalized'
        ");

        if (!$stmt) {
            return "Failed to fetch sectors.";
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();

        return $data;
    }
    // END PPMP FUNCTIONS **********************************************************


    // BIDDING CATEGORY **********************************************************

    public function getBiddingCategory($bid_cat_ID = null)
    {
        $sql = "SELECT * FROM bidding_category";

        if ($bid_cat_ID !== null) {
            $sql .= " WHERE i.bid_cat_ID = ?";
        }

        $sql .= " ORDER BY bid_cat_name";

        $stmt = $this->conn->prepare($sql);

        if ($bid_cat_ID !== null) {
            $stmt->bind_param("i", $bid_cat_ID);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    public function AddBiddingCategoryForm($bid_cat_name, $operator_ID)
    {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM bidding_category WHERE bid_cat_name = ?");
        $check_stmt->bind_param("s", $bid_cat_name);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Bidding Category already exists.";
        }

        $stmt = $this->conn->prepare("INSERT INTO bidding_category (bid_cat_name) VALUES (?)");
        $stmt->bind_param("s", $bid_cat_name);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert bidding category: " . $error;
        }

        $item_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new bidding bidding_category: $bid_cat_name",
            "bidding_category",
            $item_id
        );

        return true;
    }

    public function UpdateBiddingCategoryForm($bid_cat_ID, $bid_cat_name, $operator_ID)
    {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM bidding_category WHERE bid_cat_name = ? AND bid_cat_ID != ?");
        $check_stmt->bind_param("si", $bid_cat_name, $bid_cat_ID);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Bidding Category already exists.";
        }

        $stmt = $this->conn->prepare("UPDATE bidding_category SET bid_cat_name = ? WHERE bid_cat_ID = ?");
        $stmt->bind_param("si", $bid_cat_name, $bid_cat_ID);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update bidding category: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated bidding category: $bid_cat_name",
            "bidding_category",
            $bid_cat_ID
        );

        return true;
    }
    // END BIDDING CATEGORY **********************************************************


    // PROCUREMENT STRATEGY **********************************************************

    public function getProcStrategy($proc_strat_ID = null)
    {
        $sql = "SELECT * FROM procurement_strategy";

        if ($proc_strat_ID !== null) {
            $sql .= " WHERE proc_strat_ID = ?";
        }

        $sql .= " ORDER BY proc_strat_name";

        $stmt = $this->conn->prepare($sql);

        if ($proc_strat_ID !== null) {
            $stmt->bind_param("i", $proc_strat_ID);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result;
    }

    public function AddProcStrategyForm($proc_strat_name, $operator_ID)
    {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM procurement_strategy WHERE proc_strat_name = ?");
        $check_stmt->bind_param("s", $proc_strat_name);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Procurement Strategy already exists.";
        }

        $stmt = $this->conn->prepare("INSERT INTO procurement_strategy (proc_strat_name) VALUES (?)");
        $stmt->bind_param("s", $proc_strat_name);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to insert procurement strategy: " . $error;
        }

        $item_id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Added new procurement strategy: $proc_strat_name",
            "procurement_strategy",
            $item_id
        );

        return true;
    }

    public function UpdateProcStrategyForm($proc_strat_ID, $proc_strat_name, $operator_ID)
    {
        $count = 0;
        $check_stmt = $this->conn->prepare("SELECT COUNT(*) FROM procurement_strategy WHERE proc_strat_name = ? AND proc_strat_ID != ?");
        $check_stmt->bind_param("si", $proc_strat_name, $proc_strat_ID);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count > 0) {
            return "Procurement Strategy already exists.";
        }

        $stmt = $this->conn->prepare("UPDATE procurement_strategy SET proc_strat_name = ? WHERE proc_strat_ID = ?");
        $stmt->bind_param("si", $proc_strat_name, $proc_strat_ID);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            return "Failed to update procurement strategy: " . $error;
        }

        $stmt->close();

        $this->recordActivityLog(
            $operator_ID,
            "Updated procurement strategy: $proc_strat_name",
            "procurement_strategy",
            $proc_strat_ID
        );

        return true;
    }
    // END PROCUREMENT STRATEGY **********************************************************


    // PROCUREMENT MODES **********************************************************

    public function getProcMode($id = null)
    {
        $sql = "SELECT * FROM procurement_modes ";

        if ($id !== null) {
            $sql .= " WHERE proc_mode_id = ?";
        }

        $sql .= " ORDER BY proc_mode_name";

        $stmt = $this->conn->prepare($sql);

        if ($id !== null) {
            $stmt->bind_param("i", $id);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public function AddProcMode($name, $operator_ID)
    {
        $count = 0;
        $check = $this->conn->prepare("SELECT COUNT(*) FROM procurement_modes  WHERE proc_mode_name=?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0)
            return "Already exists.";

        $stmt = $this->conn->prepare("INSERT INTO procurement_modes  (proc_mode_name) VALUES (?)");
        $stmt->bind_param("s", $name);

        if (!$stmt->execute()) {
            return $stmt->error;
        }

        $id = $stmt->insert_id;
        $stmt->close();

        $this->recordActivityLog($operator_ID, "Added mode: $name", "procurement_modes ", $id);

        return true;
    }

    public function UpdateProcMode($id, $name, $operator_ID)
    {
        $count = 0;
        $check = $this->conn->prepare("SELECT COUNT(*) FROM procurement_modes  WHERE proc_mode_name=? AND proc_mode_id!=?");
        $check->bind_param("si", $name, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0)
            return "Already exists.";

        $stmt = $this->conn->prepare("UPDATE procurement_modes  SET proc_mode_name=? WHERE proc_mode_id=?");
        $stmt->bind_param("si", $name, $id);

        if (!$stmt->execute()) {
            return $stmt->error;
        }

        $stmt->close();

        $this->recordActivityLog($operator_ID, "Updated mode: $name", "procurement_modes ", $id);

        return true;
    }
    // END PROCUREMENT MODES **********************************************************


    // NOTIFICATIONS **********************************************************

    public function getAllNotificationsByOffice($userId)
    {
        $officeId = null;
        $sqlOffice = "SELECT office_id FROM offices WHERE head_id = ?";
        $stmt = $this->conn->prepare($sqlOffice);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($officeId);
        $stmt->fetch();
        $stmt->close();

        if (!$officeId)
            return [];

        $currentFY = $this->getCurrentFiscalYear(true);
        if (!$currentFY)
            return [];

        $fiscal_year_id = $currentFY['fiscal_year_id'];

        $sqlNotif = "SELECT notification_id, message, created_at, is_read 
                    FROM notifications 
                    WHERE office_id = ? AND fiscal_year_id = ? 
                    ORDER BY created_at DESC";
        $stmt2 = $this->conn->prepare($sqlNotif);
        $stmt2->bind_param("ii", $officeId, $fiscal_year_id);
        $stmt2->execute();
        $result = $stmt2->get_result();

        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        $stmt2->close();
        return $notifications;
    }

    public function getUnreadNotificationsByOffice($userId)
    {
        $officeId = null;
        $sqlOffice = "SELECT office_id FROM offices WHERE head_id = ?";
        $stmt = $this->conn->prepare($sqlOffice);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($officeId);
        $stmt->fetch();
        $stmt->close();

        if (!$officeId)
            return [];

        $currentFY = $this->getCurrentFiscalYear(true);
        if (!$currentFY)
            return [];

        $fiscal_year_id = $currentFY['fiscal_year_id'];

        $sqlNotif = "SELECT notification_id, message, created_at 
                    FROM notifications 
                    WHERE office_id = ? AND fiscal_year_id = ? AND is_read = 0 
                    ORDER BY created_at DESC";
        $stmt2 = $this->conn->prepare($sqlNotif);
        $stmt2->bind_param("ii", $officeId, $fiscal_year_id);
        $stmt2->execute();
        $result = $stmt2->get_result();

        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        $stmt2->close();
        return $notifications;
    }
    public function markNotificationAsRead($notificationId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    // END NOTIFICATIONS **********************************************************


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
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'thesisprojects2025@gmail.com';
            $mail->Password = 'bsgrztijucejdjti';
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

    // ACTIVITY LOGS NOTIFICATION**********************************************************

    public function recordActivityLog($userId, $action, $tableName, $recordId)
    {
        $stmt = $this->conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, ?, ?, ?) ");
        $stmt->bind_param("issi", $userId, $action, $tableName, $recordId);
        $stmt->execute();
        $stmt->close();
    }

    public function getActivityLogs()
    {
        $sql = "
            SELECT 
                al.log_id,
                al.action,
                al.table_name,
                al.record_id,
                al.created_at,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                u.user_id,
                COALESCE(ua.access_name, 'System User') AS access_type 
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            -- Join to user_access to get the role/type
            LEFT JOIN user_access ua ON u.user_id = ua.user_id
            GROUP BY al.log_id
            ORDER BY al.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }
    // END ACTIVITY LOGS NOTIFICATION**********************************************************

    public function logoutUser(int $userId, string $sessionToken): bool
    {
        $stmt = $this->conn->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ? AND session_token = ?");
        $stmt->bind_param("is", $userId, $sessionToken);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

}
