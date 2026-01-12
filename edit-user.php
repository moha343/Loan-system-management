<?php
include "auth/dhc.php";

$id = $_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE user_id=$id")->fetch_assoc();

if (isset($_POST['update'])) {
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$image);
        $conn->query("UPDATE users SET image='$image' WHERE user_id=$id");
    }

    $conn->query("
        UPDATE users 
        SET fname='$fname', email='$email', role='$role'
        WHERE user_id=$id
    ");

    header("Location: user-management.php");
}
?>

<form method="POST" enctype="multipart/form-data">
<input name="fname" value="<?= $user['fname'] ?>">
<input name="email" value="<?= $user['email'] ?>">
<select name="role">
<option <?= $user['role']=='admin'?'selected':'' ?>>admin</option>
<option <?= $user['role']=='staff'?'selected':'' ?>>staff</option>
<option <?= $user['role']=='borrower'?'selected':'' ?>>borrower</option>
</select>
<input type="file" name="image">
<button name="update">Update</button>
</form>
