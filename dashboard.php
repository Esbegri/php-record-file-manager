<?php
// Dashboard (home page after login)
session_start();
require_once 'unauthorized.php';

$role = $_SESSION['role'] ?? 'user'; // admin = admin

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Language" content="en">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Record Manager</title>

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/jquery.dataTables.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper & Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.0/js/jquery.dataTables.js"></script>

<style>
  /* Row highlighting */
  tr.has-file { background-color: #d4edda !important; }  /* light green */
  tr.no-file  { background-color: #ffffff !important; }  /* white */
  tr.cancelled{ background-color: #ffb3a7 !important; color: #fff; } /* red */
</style>

<script>
$(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#recordsTable')) {
        $('#recordsTable').DataTable().destroy();
    }
    $('#recordsTable').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/en-GB.json"
        }
    });

    $('[data-toggle="tooltip"]').tooltip();
});
</script>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <img src="./logo.png" class="card-img-top mr-1" style="width:3rem;" alt="Logo">
    <a class="navbar-brand disabled" href="#" aria-disabled="true">Record Manager (Demo)</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active ml-4">
            <?php
            if ($role === 'admin') {
				echo '
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreateRecord">
					<i class="fas fa-plus-circle mr-1"></i>Add New Record
				</button> &nbsp;&nbsp;

				<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalDeleteRecord">
					<i class="fas fa-trash-alt mr-1"></i>Delete Record
				</button> &nbsp;&nbsp;

				<button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalChangePassword">
					<i class="fas fa-key mr-1"></i>Change Password
				</button> &nbsp;&nbsp;

				<a class="btn btn-dark" href="advanced_search.php" target="_blank" rel="noopener">
				  <i class="fas fa-search mr-1"></i>Advanced Search
				</a>
				';
}

            ?>
            </li>
        </ul>

        <form action="logout.php" class="form-inline my-2 my-lg-0">
            <button type="submit" class="btn btn-warning ml-2">
                <i class="fas fa-sign-out-alt mr-1"></i>Logout
            </button>
        </form>
    </div>
</nav>

<!-- TABLE -->
<div class="container-fluid mt-3">
<table id="recordsTable" class="display table table-bordered table-striped">
    <thead class="thead-dark">
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>FILE NO</th>
            <th>NATIONAL ID</th>
            <th>FIRST NAME</th>
            <th>LAST NAME</th>
            <th>GENDER</th>
            <th>DATE OF BIRTH</th>
            <th>DATE OF DEATH</th>
            <th>MOTHER NAME</th>
            <th>FATHER NAME</th>
            <th>CATEGORY</th>
            <th>FILE</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
<?php
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset("utf8");

$query = "SELECT * FROM records ORDER BY id ASC";
$result = $conn->query($query);

$index = 0;

while ($row = $result->fetch_assoc()) {
    $index++;

    $id = (int)$row['id'];
    $isCancelled = (int)$row['cancelled'] === 1;
    $hasFile = (int)$row['has_file'] === 1;

    $cancelReason = htmlspecialchars($row['cancel_reason'] ?? '', ENT_QUOTES);

    $dob = !empty($row['date_of_birth']) ? date("d/m/Y", strtotime($row['date_of_birth'])) : "";
    $dod = !empty($row['date_of_death']) ? date("d/m/Y", strtotime($row['date_of_death'])) : "";

    // Row style
    if ($isCancelled) {
        $rowStyle = 'class="cancelled"';
    } elseif ($hasFile) {
        $rowStyle = 'class="has-file"';
    } else {
        $rowStyle = 'class="no-file"';
    }

    // File indicator
    $fileIcon = $hasFile
        ? "<span style='color:green;font-weight:bold;font-size:18px;'>✓</span>"
        : "<span style='color:black;font-weight:bold;font-size:18px;'>X</span>";

    // Tooltip if cancelled
    $tooltip = $cancelReason ? "data-toggle='tooltip' data-placement='top' title='Cancellation reason: {$cancelReason}'" : "";

    // Cancel/Restore buttons
    if ($isCancelled) {
        $cancelButtons = "
            <form action='restore_record.php' method='POST' style='display:inline-block;'>
                <input type='hidden' name='id' value='{$id}'>
                <button type='submit' class='btn btn-warning btn-sm'>
                    <i class='fa fa-undo'></i> Restore
                </button>
            </form>
        ";
    } else {
        $cancelButtons = "
            <button type='button' class='btn btn-danger btn-sm' data-toggle='modal' data-target='#cancelModal{$id}'>
                <i class='fa fa-ban'></i> Cancel
            </button>

            <!-- CANCEL MODAL -->
            <div class='modal fade' id='cancelModal{$id}' tabindex='-1' role='dialog'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Cancel Record</h5>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                        </div>

                        <form action='cancel_record.php' method='POST'>
                            <div class='modal-body'>
                                <p>You are about to cancel <strong>{$row["first_name"]} {$row["last_name"]}</strong>.</p>
                                <div class='form-group'>
                                    <label>Cancellation Reason:</label>
                                    <textarea name='cancel_reason' class='form-control' rows='3' required></textarea>
                                    <input type='hidden' name='id' value='{$id}'>
                                </div>
                            </div>
                            <div class='modal-footer'>
                                <button type='submit' class='btn btn-danger'>Confirm Cancel</button>
                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        ";
    }

    echo "
    <tr {$rowStyle} {$tooltip}>
        <td>{$index}</td>
        <td>{$id}</td>
        <td>{$row["file_no"]}</td>
        <td>{$row["national_id"]}</td>
        <td>{$row["first_name"]}</td>
        <td>{$row["last_name"]}</td>
        <td>{$row["gender"]}</td>
        <td>{$dob}</td>
        <td>{$dod}</td>
        <td>{$row["mother_name"]}</td>
        <td>{$row["father_name"]}</td>
        <td>{$row["category"]}</td>
        <td style='text-align:center;'>{$fileIcon}</td>
        <td>

            <form action='view_record.php' target='_blank' method='POST' style='display:inline-block;'>
                <button type='submit' name='id' value='{$id}' class='btn btn-info btn-sm'>
                    <i class='fa fa-folder-open'></i> View
                </button>
            </form>

            <form enctype='multipart/form-data' action='upload_file.php' method='POST' style='display:inline-block;'>
                <label class='btn btn-dark btn-sm mb-0'>
                    <i class='fa fa-upload'></i> Upload
                    <input type='file' name='file' hidden onchange='this.form.submit()'>
                    <input type='hidden' name='id' value='{$id}'>
                </label>
            </form>

            <form action='edit_record.php' target='_blank' method='POST' style='display:inline-block;'>
                <button type='submit' class='btn btn-success btn-sm' value='{$id}' name='id'>
                    <i class='fa fa-pen'></i> Edit
                </button>
            </form>

            {$cancelButtons}
        </td>
    </tr>
    ";
}

$conn->close();
?>
    </tbody>
</table>
</div>


<!-- CREATE RECORD MODAL -->
<div class="modal fade" id="modalCreateRecord" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">New Record</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form action="create_record.php" enctype="multipart/form-data" method="POST">

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">National ID</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter National ID" name="national_id">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">File No</span>
            </div>
            <input type="text" class="form-control" name="file_no" placeholder="Enter file number" required
                   oninvalid="this.setCustomValidity('File No cannot be empty!')"
                   oninput="this.setCustomValidity('')">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">First Name</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter first name" name="first_name">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Last Name</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter last name" name="last_name">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Gender</span>
            </div>
            <select class="custom-select" name="gender">
              <option value="M">MALE</option>
              <option value="F">FEMALE</option>
            </select>
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Date of Birth</span>
            </div>
            <input type="date" class="form-control" name="date_of_birth">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Date of Death</span>
            </div>
            <input type="date" class="form-control" name="date_of_death">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Mother Name</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter mother name" name="mother_name">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Father Name</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter father name" name="father_name">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Department</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter department" name="department">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Category</span>
            </div>
            <select class="custom-select" name="category">
              <option value="NORMAL">NORMAL</option>
              <option value="EX_ENTRY">EX ENTRY</option>
              <option value="LEGAL_CASE">LEGAL CASE</option>
              <option value="FETUS">FETUS</option>
            </select>
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Notes</span>
            </div>
            <input type="text" class="form-control" placeholder="Enter notes" name="notes">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">File</span>
            </div>
            <input type="file" class="form-control" name="file">
          </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Create Record</button>
        </form>
      </div>

    </div>
  </div>
</div>


<!-- DELETE RECORD MODAL -->
<script>
function confirmDelete() {
  return confirm("Are you sure you want to delete this record?");
}
</script>

<div class="modal fade" id="modalDeleteRecord" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Delete Record</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form action="delete_record.php" method="POST">
          <div class="form-group">
            <span class="input-group-text">Record to delete</span>
            <select class="form-control" name="id">
              <?php
              $conn = new mysqli('localhost','root','','belge');
              $conn->set_charset("utf8");
              $q = "SELECT id, first_name, last_name FROM records ORDER BY id ASC";
              $r = $conn->query($q);
              while ($rec = $r->fetch_assoc()) {
                $rid = (int)$rec['id'];
                $label = $rid . " - " . $rec['first_name'] . " " . $rec['last_name'];
                echo "<option value='{$rid}'>{$label}</option>";
              }
              $conn->close();
              ?>
            </select>
          </div>

          <div class="form-group">
            <span class="input-group-text">Deletion reason</span>
            <textarea class="form-control" name="delete_reason" rows="3" placeholder="Write the reason..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-danger" onclick="return confirmDelete()">DELETE</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>


<!-- CHANGE PASSWORD MODAL -->
<div class="modal fade" id="modalChangePassword" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form action="change_password.php" method="POST">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">New password</span>
            </div>
            <input type="password" class="form-control" placeholder="Enter new password" name="password1">
          </div>

          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Confirm password</span>
            </div>
            <input type="password" class="form-control" placeholder="Re-enter new password" name="password2">
          </div>

      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Change</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </form>
      </div>

    </div>
  </div>
</div>





</body>
</html>
