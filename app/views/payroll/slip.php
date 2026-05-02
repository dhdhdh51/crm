<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Salary Slip – <?= e($slip['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',sans-serif;font-size:13px;color:#222;background:#f5f5f5;padding:20px}
  .slip{max-width:720px;margin:0 auto;background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden}
  .slip-header{background:linear-gradient(135deg,#800000,#5a0000);color:#fff;padding:28px 32px;display:flex;justify-content:space-between;align-items:center}
  .slip-header h1{font-size:22px;font-weight:700;letter-spacing:.5px}
  .slip-header p{opacity:.8;margin-top:2px;font-size:12px}
  .slip-header .slip-month{text-align:right}
  .slip-header .slip-month strong{font-size:16px;display:block}
  .slip-body{padding:28px 32px}
  .emp-section{display:grid;grid-template-columns:1fr 1fr;gap:20px;padding-bottom:20px;border-bottom:1px dashed #e0d3c7;margin-bottom:20px}
  .emp-section dl dt{font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px}
  .emp-section dl dd{font-weight:500;margin-bottom:10px}
  .salary-table{width:100%;border-collapse:collapse;margin-bottom:20px}
  .salary-table th{background:#f9f3ee;color:#800000;font-size:11px;text-transform:uppercase;letter-spacing:.4px;padding:10px 14px;text-align:left}
  .salary-table td{padding:10px 14px;border-bottom:1px solid #f2ece6}
  .salary-table .amount{text-align:right;font-variant-numeric:tabular-nums}
  .net-row{background:#800000 !important;color:#fff}
  .net-row td{font-weight:700;font-size:15px;padding:14px !important}
  .footer-note{font-size:11px;color:#888;text-align:center;padding:16px 32px;border-top:1px solid #eee}
  .print-btn{display:block;margin:16px auto;padding:10px 28px;background:#800000;color:#fff;border:none;border-radius:6px;font-size:14px;cursor:pointer}
  @media print{.print-btn{display:none}body{background:#fff;padding:0}.slip{border:none;border-radius:0}}
</style>
</head>
<body>
<?php
$months = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
$monthName = $months[$slip['month']] ?? $slip['month'];
?>
<div class="slip">
  <div class="slip-header">
    <div>
      <h1>VastuVeda Realty CRM</h1>
      <p>Salary Slip</p>
    </div>
    <div class="slip-month">
      <strong><?= $monthName ?> <?= $slip['year'] ?></strong>
      <span>Pay Period</span>
    </div>
  </div>

  <div class="slip-body">
    <div class="emp-section">
      <dl>
        <dt>Employee Name</dt><dd><?= e($slip['name']) ?></dd>
        <dt>Employee ID</dt><dd><?= e($slip['employee_id']) ?></dd>
        <dt>Designation</dt><dd><?= e($slip['designation'] ?? '–') ?></dd>
      </dl>
      <dl>
        <dt>Role</dt><dd><?= e($slip['role_name']) ?></dd>
        <dt>Email</dt><dd><?= e($slip['email'] ?? '–') ?></dd>
        <dt>Payment Status</dt><dd><?= ucfirst(e($slip['payment_status'])) ?></dd>
      </dl>
    </div>

    <table class="salary-table">
      <thead>
        <tr><th>Earnings</th><th class="amount">Amount (₹)</th><th>Deductions</th><th class="amount">Amount (₹)</th></tr>
      </thead>
      <tbody>
        <tr>
          <td>Basic Salary</td>
          <td class="amount"><?= number_format((float)$slip['base_salary'], 2) ?></td>
          <td>Total Deductions</td>
          <td class="amount"><?= number_format((float)$slip['deductions'], 2) ?></td>
        </tr>
        <tr>
          <td>Incentives</td>
          <td class="amount"><?= number_format((float)$slip['incentives'], 2) ?></td>
          <td></td><td></td>
        </tr>
        <tr>
          <td>Bonus</td>
          <td class="amount"><?= number_format((float)$slip['bonus'], 2) ?></td>
          <td></td><td></td>
        </tr>
        <tr>
          <td><strong>Gross Earnings</strong></td>
          <td class="amount"><strong><?= number_format((float)$slip['base_salary']+(float)$slip['incentives']+(float)$slip['bonus'], 2) ?></strong></td>
          <td></td><td></td>
        </tr>
        <tr class="net-row">
          <td colspan="3">Net Salary (Gross – Deductions)</td>
          <td class="amount">₹ <?= number_format((float)$slip['net_salary'], 2) ?></td>
        </tr>
      </tbody>
    </table>

    <?php if ($slip['notes']): ?>
      <p style="font-size:12px;color:#555;margin-bottom:12px"><strong>Notes:</strong> <?= e($slip['notes']) ?></p>
    <?php endif; ?>
  </div>

  <div class="footer-note">
    This is a computer-generated salary slip and does not require a signature. · VastuVeda Realty CRM
  </div>
</div>

<button class="print-btn" onclick="window.print()">🖨 Print Slip</button>
</body>
</html>
