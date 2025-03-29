<?php
@include 'config.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details from database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];
    
    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
    } else {
        $target_file = $user['profile_picture'];
    }
    
    $update_sql = "UPDATE users SET username = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $new_username, $new_email, $new_password, $target_file, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: edit_profile.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h3>Edit Profile</h3>
        <?php if (isset($_SESSION['success_message'])) { echo "<p style='color:green;'>" . $_SESSION['success_message'] . "</p>"; unset($_SESSION['success_message']); } ?>
        <?php if (isset($_SESSION['error_message'])) { echo "<p style='color:red;'>" . $_SESSION['error_message'] . "</p>"; unset($_SESSION['error_message']); } ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <input type="password" name="password" placeholder="Enter New Password (Leave blank to keep current password)">
            <label>Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*">
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>
</body>
</html>
