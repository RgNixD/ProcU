<?php
session_start();

// Redirect back to login if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body style="text-align:center; padding: 50px;">
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
  <p>You are now logged in to the Procurement Planning System.</p>
  <a href="logout.php">Logout</a>
</body>
</html>