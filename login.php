<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND password=? LIMIT 1");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION["user"] = $user;

        // Role-based redirect
        if ($user["role"] == "dean") {
            header("Location: dean-dashboard.php");
        } elseif ($user["role"] == "budget") {
            header("Location: budget-dashboard.php");
        } elseif ($user["role"] == "procurement") {
            header("Location: procurement-dashboard.php");
        }
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>Invalid credentials. <a href='index.html'>Try again</a></p>";
    }
}
?>