<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

include "auth/dhc.php";

/* =========================
   TOTAL BORROWERS
========================= */
$borrowersCountResult = $conn->query("SELECT COUNT(*) as total FROM borrower");
$borrowersCount = $borrowersCountResult->fetch_assoc()['total'];

/* =========================
   ACTIVE LOANS
========================= */
$activeLoansResult = $conn->query("SELECT COUNT(*) as total, SUM(loan_amount) as total_amount FROM loans WHERE status='active'");
$activeLoansRow = $activeLoansResult->fetch_assoc();
$activeLoansCount = $activeLoansRow['total'] ?? 0;
$totalLoanAmount = $activeLoansRow['total_amount'] ?? 0;

/* =========================
   TOTAL COLLECTED PAYMENTS
========================= */
$totalCollectedResult = $conn->query("SELECT SUM(amount) as total_collected FROM payments");
$totalCollected = $totalCollectedResult->fetch_assoc()['total_collected'] ?? 0;

/* =========================
   RECENT LOANS (LATEST 5)
========================= */
$recentLoans = $conn->query("
    SELECT 
        l.loan_id,
        CONCAT(b.fname, ' ', b.lname) AS borrower_name,
        l.loan_amount,
        l.status,
        l.start_date
    FROM loans l
    JOIN borrower b ON l.borrower_id = b.borrower_id
    ORDER BY l.loan_id DESC
    LIMIT 5
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

  <!-- Page Header -->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Dashboard</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="container">

    <!-- Cards Row -->
    <div class="row g-4 mb-4">

      <div class="col-md-6 col-lg-3">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="card-title">Total Borrowers</span>
            <span class="fs-4">👥</span>
          </div>
          <div class="card-value"><?= $borrowersCount ?></div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="card-title">Active Loans</span>
            <span class="fs-4">💳</span>
          </div>
          <div class="sub-text"><?= $activeLoansCount ?></div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="card-title">Total Loan Amount</span>
            <span class="fs-4">📈</span>
          </div>
          <div class="card-value">$<?= number_format($totalLoanAmount,2) ?></div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="card-title">Total Collected</span>
            <span class="fs-4">💵</span>
          </div>
          <div class="card-value">$<?= number_format($totalCollected,2) ?></div>
        </div>
      </div>

    </div>

    <!-- Recent Loans -->
    <div class="card p-4 mb-4">
      <h5 class="mb-3">Recent Loans</h5>
      <?php if($recentLoans && $recentLoans->num_rows > 0){ ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Borrower</th>
            <th>Loan Amount</th>
            <th>Status</th>
            <th>Start Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $recentLoans->fetch_assoc()){ ?>
          <tr>
            <td><?= $row['loan_id'] ?></td>
            <td><?= htmlspecialchars($row['borrower_name']) ?></td>
            <td>$<?= number_format($row['loan_amount'],2) ?></td>
            <td><?= ucfirst($row['status']) ?></td>
            <td><?= $row['start_date'] ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
      <p class="text-center text-secondary fs-6">No loans yet. Add a borrower and create your first loan.</p>
      <?php } ?>
    </div>

  </div>

</main>

<?php include "includes/footer.php"; ?>
</div>

<?php include "includes/script-links.php"; ?>
</body>
</html>
