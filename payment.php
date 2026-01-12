<?php 
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

include "auth/dhc.php";

/* =========================
   DELETE PAYMENT (ADDED)
========================= */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {

    $payment_id = (int) $_GET['delete'];

    $get = $conn->query("
        SELECT loan_id, amount 
        FROM payments 
        WHERE payment_id = $payment_id
    ");

    if ($get && $get->num_rows > 0) {

        $row = $get->fetch_assoc();
        $loan_id = $row['loan_id'];
        $amount  = $row['amount'];

        $conn->query("DELETE FROM payments WHERE payment_id = $payment_id");

        $conn->query("
            UPDATE loans 
            SET loan_amount = loan_amount + $amount, status='active'
            WHERE loan_id = $loan_id
        ");

        $success = "Payment waa la tirtiray";
    } else {
        $error = "Payment lama helin";
    }
}

/* =========================
   UPDATE PAYMENT (ADDED)
========================= */
if (isset($_POST['update_payment'])) {

    $payment_id = $_POST['payment_id'];
    $new_amount = $_POST['payment_amount'];
    $method     = $_POST['payment_method'];
    $date       = $_POST['payment_date'];

    $old = $conn->query("
        SELECT loan_id, amount 
        FROM payments 
        WHERE payment_id = $payment_id
    ")->fetch_assoc();

    $loan_id    = $old['loan_id'];
    $old_amount = $old['amount'];
    $diff       = $new_amount - $old_amount;

    $conn->query("
        UPDATE payments
        SET amount=$new_amount,
            payment_date='$date',
            payment_method='$method'
        WHERE payment_id=$payment_id
    ");

    $conn->query("
        UPDATE loans
        SET loan_amount = loan_amount - $diff
        WHERE loan_id = $loan_id
    ");

    $success = "Payment waa la cusbooneysiiyay";
}

/* =========================
   FETCH LOANS (FOR DROPDOWN)
========================= */
$loans = $conn->query("
    SELECT 
        l.loan_id,
        CONCAT(b.fname, ' ', b.lname) AS borrower_name,
        l.loan_amount
    FROM loans l
    JOIN borrower b ON l.borrower_id = b.borrower_id
    WHERE l.loan_amount > 0
    ORDER BY l.loan_id DESC
");

/* =========================
   ADD PAYMENT (ORIGINAL)
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {

    $loan_id = $_POST['loan_id'];
    $amount = $_POST['payment_amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];

    $stmt = $conn->prepare("
        INSERT INTO payments
        (loan_id, amount, payment_date, payment_method)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("idss", $loan_id, $amount, $payment_date, $payment_method);

    if ($stmt->execute()) {

        $updateLoan = $conn->prepare("
            UPDATE loans
            SET loan_amount = loan_amount - ?
            WHERE loan_id = ?
        ");
        $updateLoan->bind_param("di", $amount, $loan_id);
        $updateLoan->execute();
        $updateLoan->close();

        $loanCheck = $conn->query("SELECT loan_amount FROM loans WHERE loan_id = $loan_id");
        $loanRow = $loanCheck->fetch_assoc();
        if ($loanRow['loan_amount'] <= 0) {
            $conn->query("UPDATE loans SET status='paid', loan_amount=0 WHERE loan_id = $loan_id");
        }

        $success = "Payment added and loan balance updated!";
    } else {
        $error = "Failed to add payment!";
    }

    $stmt->close();
}

/* =========================
   FETCH PAYMENTS
========================= */
$payments = $conn->query("
    SELECT 
        p.payment_id,
        CONCAT(b.fname, ' ', b.lname) AS borrower_name,
        p.amount,
        p.payment_date,
        p.payment_method,
        l.loan_amount AS remaining_loan
    FROM payments p
    JOIN loans l ON p.loan_id = l.loan_id
    JOIN borrower b ON l.borrower_id = b.borrower_id
    ORDER BY p.payment_id DESC
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

<h3>Payments</h3>

<?php if (!empty($success)) { ?>
<div class="alert alert-success"><?= $success ?></div>
<?php } ?>
<?php if (!empty($error)) { ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php } ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
<i class="bi bi-plus-lg"></i> Add Payment
</button>

<div class="card">
<div class="card-body">

<table class="table table-bordered">
<thead>
<tr>
<th>ID</th>
<th>Borrower</th>
<th>Amount</th>
<th>Date</th>
<th>Method</th>
<th>Remaining Loan</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php if ($payments && $payments->num_rows > 0) { ?>
<?php while ($row = $payments->fetch_assoc()) { ?>
<tr>
<td><?= $row['payment_id'] ?></td>
<td><?= htmlspecialchars($row['borrower_name']) ?></td>
<td><?= number_format($row['amount'], 2) ?></td>
<td><?= $row['payment_date'] ?></td>
<td><?= $row['payment_method'] ?></td>
<td><?= number_format($row['remaining_loan'], 2) ?></td>
<td>
<a href="payment.php?delete=<?= $row['payment_id'] ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Delete payment?')">
<i class="bi bi-trash"></i>
</a>

<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#editPayment<?= $row['payment_id'] ?>">
<i class="bi bi-pencil"></i>
</button>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="7" class="text-center">No payments found</td></tr>
<?php } ?>

</tbody>
</table>

</div>
</div>
</div>
</main>

<!-- ADD PAYMENT MODAL (ORIGINAL) -->
<?php /* unchanged modal here */ ?>

<?php
$payments->data_seek(0);
while ($p = $payments->fetch_assoc()) {
?>
<!-- EDIT PAYMENT MODAL -->
<div class="modal fade" id="editPayment<?= $p['payment_id'] ?>">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">
<form method="POST">
<input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">

<div class="modal-header">
<h5>Edit Payment</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="number" step="0.01" name="payment_amount" class="form-control mb-2" value="<?= $p['amount'] ?>" required>
<input type="date" name="payment_date" class="form-control mb-2" value="<?= $p['payment_date'] ?>" required>
<select name="payment_method" class="form-select">
<option <?= $p['payment_method']=="Cash"?"selected":"" ?>>Cash</option>
<option <?= $p['payment_method']=="Bank"?"selected":"" ?>>Bank</option>
<option <?= $p['payment_method']=="Mobile Money"?"selected":"" ?>>Mobile Money</option>
</select>
</div>

<div class="modal-footer">
<button type="submit" name="update_payment" class="btn btn-warning">Update</button>
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
