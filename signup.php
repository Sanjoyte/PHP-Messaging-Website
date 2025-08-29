<?php
require 'connection.php'; // include your DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $name    = $_POST['name'];
    $email   = $_POST['email'];
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (user_id, name, email, password) 
            VALUES ('$user_id', '$name', '$email', '$hashed_password')";

    if (mysqli_query($connection, $sql)) {
        // assign role
        if ($role == "student") {
            mysqli_query($connection, "INSERT INTO student (user_id) VALUES ('$user_id')");
        } elseif ($role == "both") {
            mysqli_query($connection, "INSERT INTO admin (user_id) VALUES ('$user_id')");
            mysqli_query($connection, "INSERT INTO student (user_id) VALUES ('$user_id')");
        }
        echo "Signup successful. <a href='login.php'>Login here</a>";
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
</head>
<body>
    <h2>Signup</h2>
    <form method="POST" action="">
        User ID: <input type="text" name="user_id" required><br><br>
        Name: <input type="text" name="name" required><br><br>
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        Role: 
        <select name="role" required>
            <option value="student">Student</option>          
            <option value="both">Student & Admin</option>
        </select><br><br>
        <button type="submit">Signup</button>
    </form>
</body>
</html>
