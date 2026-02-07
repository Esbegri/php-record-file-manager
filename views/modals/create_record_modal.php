<div class="modal fade" id="modalNewRecord" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Add New Record</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="create_record.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">File Number</label>
                            <input type="text" name="file_no" class="form-control" placeholder="e.g. 2024/102" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">National ID</label>
                            <input type="text" name="national_id" class="form-control" placeholder="11-digit ID">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">First Name</label>
                            <input type="text" name="first_name" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Last Name</label>
                            <input type="text" name="last_name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Department</label>
                        <input type="text" name="department" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Initial Document (Optional)</label>
                        <input type="file" name="file" class="form-control-file">
                        <small class="text-muted">Allowed: PDF, JPG, PNG (Max 10MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>