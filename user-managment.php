<?php
session_start();
include "auth/dhc.php";

// ADMIN CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
}

// FETCH USERS
$users = $conn->query("
    SELECT user_id, fname, username, email, role, image
    FROM users
    ORDER BY user_id DESC
");
?>

<?php include "includes/header.php"; ?>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#">
            <i class="bi bi-list"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
        <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php include "includes/navbar.php"; ?>
        <?php include "includes/navbar.links.php"; ?>
      </ul>
    </div>
  </nav>
<?php include "includes/sidebar.php"; ?>

<main class="app-main">
<div class="container-fluid py-4">

<div class="d-flex justify-content-between mb-3">
<h3>User Management</h3>
<a href="add-user.php" class="btn btn-primary">+ Add User</a>
</div>

<div class="card">
<div class="card-body">

<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Image</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php if ($users && $users->num_rows > 0) { ?>
<?php while ($u = $users->fetch_assoc()) { ?>
<tr>
<td><?= $u['user_id'] ?></td>
<td><?= htmlspecialchars($u['fname']) ?></td>
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= ucfirst($u['role']) ?></td>
<td>
<?php if ($u['image']) { ?>
<img src="uploads/<?= $u['image'] ?>" width="40" class="rounded-circle">
<?php } ?>
</td>
<td>
<a href="edit-user.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
<a href="delete-user.php?id=<?= $u['user_id'] ?>" 
   onclick="return confirm('Delete this user?')" 
   class="btn btn-sm btn-danger">Delete</a>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="7" class="text-center">No users found</td></tr>
<?php } ?>

</tbody>
</table>

</div>
</div>

</div>
</main>

<?php include "includes/footer.php"; ?>
</div>
</body>
</html>
