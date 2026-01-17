<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('localhost','root','','belge');
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("DB connection failed.");
}

$conditions = [];
$params = [];
$types = "";

function addCondition(&$conditions, &$params, &$types, $field, $value, $like=false) {
    if ($value !== '' && $value !== null) {
        if ($like) {
            $conditions[] = "$field LIKE ?";
            $params[] = "%$value%";
            $types .= "s";
        } else {
            $conditions[] = "$field = ?";
            $params[] = $value;
            $types .= "s";
        }
    }
}

$first_name  = trim($_GET['first_name'] ?? '');
$last_name   = trim($_GET['last_name'] ?? '');
$gender      = trim($_GET['gender'] ?? '');
$file_no     = trim($_GET['file_no'] ?? '');
$national_id = trim($_GET['national_id'] ?? '');
$department  = trim($_GET['department'] ?? '');
$category    = trim($_GET['category'] ?? '');
$has_file    = $_GET['has_file'] ?? '';     // '1' / '0' / ''
$cancelled   = $_GET['cancelled'] ?? '';    // '1' / '0' / ''
$dob_from    = trim($_GET['dob_from'] ?? '');
$dob_to      = trim($_GET['dob_to'] ?? '');
$dod_from    = trim($_GET['dod_from'] ?? '');
$dod_to      = trim($_GET['dod_to'] ?? '');

// Default: Do not show deleted records
$conditions[] = "is_deleted = 0";

addCondition($conditions, $params, $types, "first_name", $first_name, true);
addCondition($conditions, $params, $types, "last_name", $last_name, true);
addCondition($conditions, $params, $types, "file_no", $file_no, true);
addCondition($conditions, $params, $types, "national_id", $national_id, true);
addCondition($conditions, $params, $types, "department", $department, true);
addCondition($conditions, $params, $types, "category", $category, false);

if ($gender !== '') {
    $conditions[] = "gender = ?";
    $params[] = $gender;
    $types .= "s";
}

if ($has_file !== '') {
    $conditions[] = "has_file = ?";
    $params[] = $has_file;
    $types .= "s";
}

if ($cancelled !== '') {
    $conditions[] = "cancelled = ?";
    $params[] = $cancelled;
    $types .= "s";
}

// Date ranges
if ($dob_from !== '' && $dob_to !== '') {
    $conditions[] = "date_of_birth BETWEEN ? AND ?";
    $params[] = $dob_from; $params[] = $dob_to;
    $types .= "ss";
} elseif ($dob_from !== '') {
    $conditions[] = "date_of_birth >= ?";
    $params[] = $dob_from;
    $types .= "s";
} elseif ($dob_to !== '') {
    $conditions[] = "date_of_birth <= ?";
    $params[] = $dob_to;
    $types .= "s";
}

if ($dod_from !== '' && $dod_to !== '') {
    $conditions[] = "date_of_death BETWEEN ? AND ?";
    $params[] = $dod_from; $params[] = $dod_to;
    $types .= "ss";
} elseif ($dod_from !== '') {
    $conditions[] = "date_of_death >= ?";
    $params[] = $dod_from;
    $types .= "s";
} elseif ($dod_to !== '') {
    $conditions[] = "date_of_death <= ?";
    $params[] = $dod_to;
    $types .= "s";
}

$sql = "SELECT
            id, file_no, national_id, first_name, last_name, gender,
            date_of_birth, date_of_death, mother_name, father_name,
            department, category, has_file, cancelled, cancel_reason, created_at
        FROM records";

if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY id DESC LIMIT 500";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Advanced Search</title>

<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/jquery.dataTables.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.12.0/js/jquery.dataTables.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<style>
tr.has-file { background-color: #d4edda !important; }
tr.cancelled { background-color: #ffb3a7 !important; color: #111; }
</style>
</head>

<body>
<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Advanced Search</h3>
    <a class="btn btn-outline-secondary" href="dashboard.php">Back</a>
  </div>

  <form method="GET" class="mb-3">
    <div class="form-row">
      <div class="col">
        <input type="text" class="form-control" name="first_name" placeholder="First name" value="<?= htmlspecialchars($first_name) ?>">
      </div>
      <div class="col">
        <input type="text" class="form-control" name="last_name" placeholder="Last name" value="<?= htmlspecialchars($last_name) ?>">
      </div>
      <div class="col">
        <select class="form-control" name="gender">
          <option value="">Gender (Any)</option>
          <option value="M" <?= $gender==='M'?'selected':''; ?>>M</option>
          <option value="F" <?= $gender==='F'?'selected':''; ?>>F</option>
          <option value="Male" <?= $gender==='Male'?'selected':''; ?>>Male</option>
          <option value="Female" <?= $gender==='Female'?'selected':''; ?>>Female</option>
        </select>
      </div>
    </div>

    <div class="form-row mt-2">
      <div class="col">
        <input type="text" class="form-control" name="file_no" placeholder="File No" value="<?= htmlspecialchars($file_no) ?>">
      </div>
      <div class="col">
        <input type="text" class="form-control" name="national_id" placeholder="National ID" value="<?= htmlspecialchars($national_id) ?>">
      </div>
      <div class="col">
        <input type="text" class="form-control" name="department" placeholder="Department" value="<?= htmlspecialchars($department) ?>">
      </div>
      <div class="col">
        <select class="form-control" name="category">
          <option value="">Category (Any)</option>
          <option value="Patient" <?= $category==='Patient'?'selected':''; ?>>Patient</option>
          <option value="Outpatient" <?= $category==='Outpatient'?'selected':''; ?>>Outpatient</option>
          <option value="Archived" <?= $category==='Archived'?'selected':''; ?>>Archived</option>
          <option value="Document" <?= $category==='Document'?'selected':''; ?>>Document</option>
          <option value="Case File" <?= $category==='Case File'?'selected':''; ?>>Case File</option>
        </select>
      </div>
    </div>

    <div class="form-row mt-2">
      <div class="col">
        <select class="form-control" name="has_file">
          <option value="">Has File? (Any)</option>
          <option value="1" <?= $has_file==='1'?'selected':''; ?>>Yes</option>
          <option value="0" <?= $has_file==='0'?'selected':''; ?>>No</option>
        </select>
      </div>
      <div class="col">
        <select class="form-control" name="cancelled">
          <option value="">Cancelled? (Any)</option>
          <option value="0" <?= $cancelled==='0'?'selected':''; ?>>Active</option>
          <option value="1" <?= $cancelled==='1'?'selected':''; ?>>Cancelled</option>
        </select>
      </div>
      <div class="col"><input type="date" class="form-control" name="dob_from" value="<?= htmlspecialchars($dob_from) ?>" title="DOB From"></div>
      <div class="col"><input type="date" class="form-control" name="dob_to" value="<?= htmlspecialchars($dob_to) ?>" title="DOB To"></div>
    </div>

    <div class="form-row mt-2">
      <div class="col"><input type="date" class="form-control" name="dod_from" value="<?= htmlspecialchars($dod_from) ?>" title="DOD From"></div>
      <div class="col"><input type="date" class="form-control" name="dod_to" value="<?= htmlspecialchars($dod_to) ?>" title="DOD To"></div>
      <div class="col">
        <button type="submit" class="btn btn-primary btn-block">Search</button>
      </div>
      <div class="col">
        <a class="btn btn-secondary btn-block" href="advanced_search.php">Reset</a>
      </div>
    </div>
  </form>

  <table id="datatable" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>File No</th>
        <th>National ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Gender</th>
        <th>DOB</th>
        <th>DOD</th>
        <th>Mother</th>
        <th>Father</th>
        <th>Department</th>
        <th>Category</th>
        <th>File</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
<?php while($row = $result->fetch_assoc()):
    $rowClass = $row['cancelled'] ? 'cancelled' : ($row['has_file'] ? 'has-file' : '');
    $tooltip = $row['cancel_reason'] ?? '';
?>
<tr class="<?= $rowClass ?>" <?= $tooltip ? "title='".htmlspecialchars($tooltip)."'" : "" ?>>
  <td><?= (int)$row['id'] ?></td>
  <td><?= htmlspecialchars($row['file_no']) ?></td>
  <td><?= htmlspecialchars($row['national_id']) ?></td>
  <td><?= htmlspecialchars($row['first_name']) ?></td>
  <td><?= htmlspecialchars($row['last_name']) ?></td>
  <td><?= htmlspecialchars($row['gender']) ?></td>
  <td><?= htmlspecialchars($row['date_of_birth']) ?></td>
  <td><?= htmlspecialchars($row['date_of_death']) ?></td>
  <td><?= htmlspecialchars($row['mother_name']) ?></td>
  <td><?= htmlspecialchars($row['father_name']) ?></td>
  <td><?= htmlspecialchars($row['department']) ?></td>
  <td><?= htmlspecialchars($row['category']) ?></td>
  <td><?= $row['has_file'] ? 'Yes' : 'No' ?></td>
  <td><?= $row['cancelled'] ? 'Cancelled' : 'Active' ?></td>
</tr>
<?php endwhile; ?>
    </tbody>
  </table>

<script>
$(document).ready(function() {
  $('#datatable').DataTable();
});
</script>

</div>
</body>
</html>
