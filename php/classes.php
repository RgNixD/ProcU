<?php

// Load environment variables and configurations
require_once __DIR__ . '/functions.php';
// To prevent direct access to this file
preventDirectAccess();

require_once 'db_config.php';

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
    public function getUserById($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
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
    // END USER MANAGEMENT FUNCTION**********************************************************

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
