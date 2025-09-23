// Simple temporary login credentials
$valid_username = "admin";
$valid_password = "admin123";

// Get submitted form data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Check credentials
if ($username === $valid_username && $password === $valid_password) {
    // Start session and redirect to dashboard
    session_start();
    $_SESSION['username'] = $username;
    header("Location: dashboard.php");
    exit();
} else {
    // If wrong, show error message and go back
    echo "<script>alert('Invalid username or password!'); window.history.back();</script>";
}