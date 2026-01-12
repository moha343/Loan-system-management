<?php
include "auth/dhc.php";
include "tables/borrowes.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$result = $conn->query("SELECT * FROM borrower ORDER BY borrower_id DESC");
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

<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBorrowerModal">
  <i class="bi bi-plus-lg"></i> Add Borrower
</button>

<div class="card">
<div class="card-body">

<table class="table table-bordered table-striped">
<thead>
<tr>
  <th>#</th>
  <th>Name</th>
  <th>Birth</th>
  <th>Phone</th>
  <th>Email</th>
  <th>Address</th>
  <th>Actions</th>
</tr>
</thead>
<tbody>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= $row['borrower_id'] ?></td>
  <td><?= $row['fname'].' '.$row['lname'] ?></td>
  <td><?= $row['bith'] ?></td>
  <td><?= $row['phone'] ?></td>
  <td><?= $row['email'] ?></td>
  <td><?= $row['address'] ?></td>
  <td>
    <!-- DELETE -->
    <a href="borrower.php?delete=<?= $row['borrower_id'] ?>"
       onclick="return confirm('Ma hubtaa inaad tirtirto borrower-kan?')"
       class="btn btn-danger btn-sm">
       <i class="bi bi-trash"></i>
    </a>

    <!-- EDIT -->
    <button class="btn btn-warning btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#edit<?= $row['borrower_id'] ?>">
      <i class="bi bi-pencil"></i>
    </button>
  </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addBorrowerModal">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<form action="borrower.php" method="POST">
<div class="modal-header">
<h5>Add Borrower</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input class="form-control mb-2" name="fname" placeholder="First Name" required>
<input class="form-control mb-2" name="lname" placeholder="Last Name" required>
<input class="form-control mb-2" type="date" name="bith">
<input class="form-control mb-2" name="phone" placeholder="Phone">
<input class="form-control mb-2" type="email" name="email" placeholder="Email">
<textarea class="form-control" name="address" placeholder="Address"></textarea>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-primary">Save</button>
</div>
</form>

</div>
</div>
</div>

<?php
$result->data_seek(0);
while ($row = $result->fetch_assoc()):
?>
<!-- EDIT MODAL -->
<div class="modal fade" id="edit<?= $row['borrower_id'] ?>">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">

<form action="borrower.php" method="POST">
<input type="hidden" name="action" value="update">
<input type="hidden" name="borrower_id" value="<?= $row['borrower_id'] ?>">

<div class="modal-header">
<h5>Edit Borrower</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input class="form-control mb-2" name="fname" value="<?= $row['fname'] ?>" required>
<input class="form-control mb-2" name="lname" value="<?= $row['lname'] ?>" required>
<input class="form-control mb-2" type="date" name="bith" value="<?= $row['bith'] ?>">
<input class="form-control mb-2" name="phone" value="<?= $row['phone'] ?>">
<input class="form-control mb-2" name="email" value="<?= $row['email'] ?>">
<textarea class="form-control" name="address"><?= $row['address'] ?></textarea>
</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-warning">Update</button>
</div>
</form>

</div>
</div>
</div>
<?php endwhile; ?>

</main>
<?php include "includes/footer.php"; ?>
<?php include "includes/script-links.php"; ?>
</div>
</body>
</html>
