<?php 
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

include "auth/dhc.php";

/* =========================
   DELETE LOAN (ADDED)
========================= */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $loan_id = (int) $_GET['delete'];

    $conn->query("DELETE FROM loans WHERE loan_id = $loan_id");

    $success = "Loan waa la tirtiray";
}

/* =========================
   UPDATE LOAN (ADDED)
========================= */
if (isset($_POST['update_loan'])) {

    $loan_id = $_POST['loan_id'];
    $loan_amount = $_POST['loan_amount'];
    $interest_rate = $_POST['interest_rate'];
    $loan_term_months = $_POST['loan_term_months'];
    $start_date = $_POST['start_date'];
    $status = $_POST['status'];
    $node = $_POST['node'];

    $stmt = $conn->prepare("
        UPDATE loans SET
            loan_amount = ?,
            interest_rate = ?,
            loan_term_months = ?,
            start_date = ?,
            status = ?,
            node = ?
        WHERE loan_id = ?
    ");

    $stmt->bind_param(
        "ddisssi",
        $loan_amount,
        $interest_rate,
        $loan_term_months,
        $start_date,
        $status,
        $node,
        $loan_id
    );

    $stmt->execute();
    $stmt->close();

    $success = "Loan waa la cusbooneysiiyay";
}

/* =========================
   FETCH BORROWERS
========================= */
$borrowers = $conn->query("
    SELECT borrower_id, fname, lname 
    FROM borrower 
    ORDER BY fname ASC
");

/* =========================
   ADD LOAN (ORIGINAL)
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {

    $borrower_id = $_POST['borrower_id'];
    $loan_amount = $_POST['loan_amount'];
    $interest_rate = $_POST['interest_rate'];
    $loan_term_months = $_POST['loan_term_months'];
    $start_date = $_POST['start_date'];
    $status = strtolower($_POST['status']);
    $node = $_POST['node'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO loans
        (borrower_id, loan_amount, interest_rate, loan_term_months, start_date, status, node)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iddisss",
        $borrower_id,
        $loan_amount,
        $interest_rate,
        $loan_term_months,
        $start_date,
        $status,
        $node
    );

    if ($stmt->execute()) {
        $success = "Loan added successfully!";
    } else {
        $error = "Failed to add loan!";
    }

    $stmt->close();
}

/* =========================
   FETCH LOANS
========================= */
$loans = $conn->query("
    SELECT 
        l.loan_id,
        CONCAT(b.fname, ' ', b.lname) AS borrower_name,
        l.loan_amount,
        l.interest_rate,
        l.loan_term_months,
        l.start_date,
        l.status,
        l.node
    FROM loans l
    JOIN borrower b ON l.borrower_id = b.borrower_id
    ORDER BY l.loan_id DESC
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

<h3>Loan Management</h3>

<?php if (!empty($success)) { ?>
<div class="alert alert-success"><?= $success ?></div>
<?php } ?>

<?php if (!empty($error)) { ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php } ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addLoanModal">
Add Loan
</button>

<div class="card">
<div class="card-body">

<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th>Borrower</th>
<th>Amount</th>
<th>Interest</th>
<th>Term</th>
<th>Start Date</th>
<th>Status</th>
<th>Node</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php if ($loans->num_rows > 0) { ?>
<?php while ($row = $loans->fetch_assoc()) { ?>
<tr>
<td><?= $row['loan_id'] ?></td>
<td><?= htmlspecialchars($row['borrower_name']) ?></td>
<td><?= number_format($row['loan_amount'], 2) ?></td>
<td><?= $row['interest_rate'] ?>%</td>
<td><?= $row['loan_term_months'] ?></td>
<td><?= $row['start_date'] ?></td>
<td><?= ucfirst($row['status']) ?></td>
<td><?= htmlspecialchars($row['node']) ?></td>
<td>
<a href="loan.php?delete=<?= $row['loan_id'] ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Delete loan?')">
<i class="bi bi-trash"></i>
</a>

<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editLoan<?= $row['loan_id'] ?>">
<i class="bi bi-pencil"></i>
</button>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="9" class="text-center">No loans found</td></tr>
<?php } ?>

</tbody>
</table>

</div>
</div>
</div>
</main>

<!-- EDIT LOAN MODAL (ADDED) -->
<?php
$loans->data_seek(0);
while ($l = $loans->fetch_assoc()) {
?>
<div class="modal fade" id="editLoan<?= $l['loan_id'] ?>">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<form method="POST">
<input type="hidden" name="loan_id" value="<?= $l['loan_id'] ?>">

<div class="modal-header">
<h5>Edit Loan</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="number" step="0.01" name="loan_amount" class="form-control mb-2" value="<?= $l['loan_amount'] ?>" required>
<input type="number" step="0.01" name="interest_rate" class="form-control mb-2" value="<?= $l['interest_rate'] ?>">
<input type="number" name="loan_term_months" class="form-control mb-2" value="<?= $l['loan_term_months'] ?>">
<input type="date" name="start_date" class="form-control mb-2" value="<?= $l['start_date'] ?>">
<select name="status" class="form-select mb-2">
<option value="active" <?= $l['status']=="active"?"selected":"" ?>>Active</option>
<option value="paid" <?= $l['status']=="paid"?"selected":"" ?>>Paid</option>
<option value="defaulted" <?= $l['status']=="defaulted"?"selected":"" ?>>Defaulted</option>
</select>
<textarea name="node" class="form-control"><?= $l['node'] ?></textarea>
</div>

<div class="modal-footer">
<button type="submit" name="update_loan" class="btn btn-warning">Update</button>
</div>

</form>

</div>
</div>
</div>
<?php } ?>

<?php include "includes/footer.php"; ?>
</div>
<?php include "includes/script-links.php"; ?>
</body>
</html>
