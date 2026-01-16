<?php
session_start();
require_once 'unauthorized.php';

// Expect record id from POST (dashboard sends name="id")
if (!isset($_POST['id'])) {
    header('Location: dashboard.php');
    exit;
}

$recordId = (int)$_POST['id'];
if ($recordId <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'belge');
if ($conn->connect_error) {
    die('Database connection error.');
}
$conn->set_charset('utf8');

// Fetch record securely
$stmt = $conn->prepare("
    SELECT
        file_no,
        national_id,
        first_name,
        last_name,
        gender,
        category,
        date_of_birth,
        date_of_death,
        mother_name,
        father_name,
        department,
        notes
    FROM records
    WHERE id = ?
");
$stmt->bind_param('i', $recordId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php');
    exit;
}

$record = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Safe output (XSS protection)
$fileNo      = htmlspecialchars($record['file_no'] ?? '', ENT_QUOTES, 'UTF-8');
$nationalId  = htmlspecialchars($record['national_id'] ?? '', ENT_QUOTES, 'UTF-8');
$firstName   = htmlspecialchars($record['first_name'] ?? '', ENT_QUOTES, 'UTF-8');
$lastName    = htmlspecialchars($record['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
$gender      = strtoupper($record['gender'] ?? '');
$dob         = htmlspecialchars($record['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8');
$dod         = htmlspecialchars($record['date_of_death'] ?? '', ENT_QUOTES, 'UTF-8');
$motherName  = htmlspecialchars($record['mother_name'] ?? '', ENT_QUOTES, 'UTF-8');
$fatherName  = htmlspecialchars($record['father_name'] ?? '', ENT_QUOTES, 'UTF-8');
$department  = htmlspecialchars($record['department'] ?? '', ENT_QUOTES, 'UTF-8');
$notes       = htmlspecialchars($record['notes'] ?? '', ENT_QUOTES, 'UTF-8');
$category    = strtoupper($record['category'] ?? '');

// Category selected
$catNormal = $catExEntry = $catLegal = $catFetus = '';
switch ($category) {
    case 'NORMAL':     $catNormal = 'selected'; break;
    case 'EX_ENTRY':   $catExEntry = 'selected'; break;
    case 'LEGAL_CASE': $catLegal = 'selected'; break;
    case 'FETUS':      $catFetus = 'selected'; break;
    default:           $catNormal = 'selected'; break;
}

// Gender selected
$genderMale = $genderFemale = '';
if ($gender === 'M' || $gender === 'MALE') $genderMale = 'selected';
else $genderFemale = 'selected';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Language" content="en">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<title>Edit Record</title>
</head>
<body class="bg-light">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0">EDIT RECORD (ID: <?php echo (int)$recordId; ?>)</h5>
        </div>

        <div class="card-body">
          <form action="update_record.php" method="POST">

            <!-- Hidden ID: which record will be updated -->
            <input type="hidden" name="id" value="<?php echo (int)$recordId; ?>">

            <div class="form-group">
              <label>National ID</label>
              <input type="text" class="form-control" name="national_id" value="<?php echo $nationalId; ?>">
            </div>

            <div class="form-group">
              <label>File No (read-only)</label>
              <input type="text" class="form-control" value="<?php echo $fileNo; ?>" readonly>
            </div>

            <div class="form-group">
              <label>First Name</label>
              <input type="text" class="form-control" name="first_name" value="<?php echo $firstName; ?>">
            </div>

            <div class="form-group">
              <label>Last Name</label>
              <input type="text" class="form-control" name="last_name" value="<?php echo $lastName; ?>">
            </div>

            <div class="form-group">
              <label>Gender</label>
              <select class="custom-select" name="gender">
                <option value="M" <?php echo $genderMale; ?>>MALE</option>
                <option value="F" <?php echo $genderFemale; ?>>FEMALE</option>
              </select>
            </div>

            <div class="form-group">
              <label>Date of Birth</label>
              <input type="date" class="form-control" name="date_of_birth" value="<?php echo $dob; ?>">
            </div>

            <div class="form-group">
              <label>Date of Death</label>
              <input type="date" class="form-control" name="date_of_death" value="<?php echo $dod; ?>">
            </div>

            <div class="form-group">
              <label>Mother Name</label>
              <input type="text" class="form-control" name="mother_name" value="<?php echo $motherName; ?>">
            </div>

            <div class="form-group">
              <label>Father Name</label>
              <input type="text" class="form-control" name="father_name" value="<?php echo $fatherName; ?>">
            </div>

            <div class="form-group">
              <label>Department</label>
              <input type="text" class="form-control" name="department" value="<?php echo $department; ?>">
            </div>

            <div class="form-group">
              <label>Category</label>
              <select class="custom-select" name="category">
                <option value="NORMAL" <?php echo $catNormal; ?>>NORMAL</option>
                <option value="EX_ENTRY" <?php echo $catExEntry; ?>>EX ENTRY</option>
                <option value="LEGAL_CASE" <?php echo $catLegal; ?>>LEGAL CASE</option>
                <option value="FETUS" <?php echo $catFetus; ?>>FETUS</option>
              </select>
            </div>

            <div class="form-group">
              <label>Notes</label>
              <input type="text" class="form-control" name="notes" value="<?php echo $notes; ?>">
            </div>

            <div class="text-right">
              <a href="dashboard.php" class="btn btn-secondary">Close</a>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
