<?php
include "auth/dhc.php";

if (isset($_POST['save'])) {
    $fname = $_POST['fname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $image = $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "uploads/".$image);

    $stmt = $conn->prepare("
        INSERT INTO users (fname, username, email, password, role, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $fname, $username, $email, $password, $role, $image);
    $stmt->execute();

    header("Location: user-managment.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up || Loen System</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: linear-gradient(135deg, #1d2671, #c33764);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: Arial, sans-serif;
    }

    .signup-box {
        background: #fff;
        padding: 40px 30px;
        border-radius: 15px;
        width: 100%;
        max-width: 650px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .signup-title {
        text-align: center;
        font-weight: bold;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }

    .signup-subtitle {
        text-align: center;
        font-size: 0.95rem;
        margin-bottom: 25px;
        color: #555;
    }

    .btn-primary {
        background-color: #1d2671;
        border-color: #1d2671;
        transition: 0.3s;
    }

    .btn-primary:hover {
        background-color: #c33764;
        border-color: #c33764;
    }

    .form-label {
        font-weight: 500;
    }

    .text-center small a {
        color: #1d2671;
        text-decoration: none;
    }

    .text-center small a:hover {
        text-decoration: underline;
        color: #c33764;
    }
</style>
</head>
<body>

<div class="signup-box">
    <h3 class="signup-title">Loen System</h3>
    <p class="signup-subtitle">Create your account and start using our system</p>

    <form action="add-user.php" method="POST" enctype="multipart/form-data">
        
        <!-- Row: Full Name + Age -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fname" class="form-label">Full Name</label>
                <input type="text" name="fname" id="fname" class="form-control" placeholder="Enter your full name" required>
            </div>
            <div class="col-md-6">
                <label for="age" class="form-label">Age</label>
                <input type="number" name="age" id="age" class="form-control" placeholder="Enter your age">
            </div>
        </div>
        <!-- Row: Phone + Email -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter your phone number">
            </div>
            <div class="col-md-6">
                <label for="sex" class="form-label">Sex</label>
                <select name="sex" id="sex" class="form-select">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other" selected>Other</option>
                </select>
            </div>
        </div>

        <!-- Row: Username + Sex -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
            </div>
            <!-- email -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
            </div>

        </div>
        <!-- Row: Role -->
        <div class="mb-3">
           <label for="role" class="form-label">Role</label>
          <select name="role" id="role" class="form-select">
           <option value="select" selected>Select</option>
           <option value="staff">Admin</option>
                
            <option value="staff">Borrower</option>

            <option value="staff">Staff</option>

           </select>
       </div>
        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <!-- Profile Image -->
        <div class="mb-3">
            <label for="image" class="form-label">Profile Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
        </div>
            <div class="d-grid gap-2">
              <button type="submit" name="save" class="btn btn-primary">Save User</button>
              <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>
