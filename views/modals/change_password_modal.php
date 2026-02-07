<div class="modal fade" id="modalChangePassword" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key mr-2"></i>Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="change_password.php" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password1" class="form-control" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="password2" class="form-control" placeholder="Repeat new password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>