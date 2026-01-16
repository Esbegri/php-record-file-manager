## Version

**v1.0.0 – Initial stable release**

This version includes:
- User authentication
- Record create / edit / delete
- File upload per record
- Cancel & restore system
- Basic audit logging


# PHP Record & File Manager

A lightweight PHP + MySQL record management system with:
- Record creation & editing
- File upload per record
- Secure file view/download via PHP (not direct public links)
- Cancel / Restore workflow
- Soft Delete + deletion audit log
- Update audit log
- Login system with password hashing (`password_hash` / `password_verify`)

## Tech Stack
- PHP (plain PHP)
- MySQL (XAMPP)
- Bootstrap 4
- DataTables

## Features
- **Create records** with unique `file_no`
- **Upload files** to `storage/uploads/{recordId}/`
- **View/Download files** via `file.php` (access-controlled)
- **Cancel** a record with reason (and restore)
- **Soft delete** with reason + deletion log
- **Audit logs**
  - `record_changes` for updates
  - `record_deletions` for deletes

## Setup (XAMPP)
1. Copy project folder to:
   `C:\xampp\htdocs\kayit`

2. Create upload directory:
   `C:\xampp\htdocs\kayit\storage\uploads`

3. Import database:
   - Open phpMyAdmin
   - Import `schema.sql`
   - This creates database: `belge`

4. Create an admin user
   - Generate a password hash:
     - Create `make_hash.php` with:
       ```php
       <?php echo password_hash("Admin123!", PASSWORD_DEFAULT);
       ```
     - Open: `http://localhost/kayit/make_hash.php`
     - Copy the output hash and delete `make_hash.php`
   - Insert user:
     ```sql
     INSERT INTO users (username, password_hash, role)
     VALUES ('admin', '<PASTE_HASH_HERE>', '1');
     ```

5. Run the app:
   - Login page:
     `http://localhost/kayit/login_form.php`

## Default Pages
- `login_form.php` – login UI
- `login.php` – login handler
- `logout.php` – logout
- `dashboard.php` – main table + actions
- `create_record.php` – create record
- `edit_record.php` / `update_record.php` – edit workflow
- `upload_file.php` – upload handler
- `view_record.php` – list files for a record
- `file.php` – secure file streaming
- `cancel_record.php` / `restore_record.php` – cancel/restore
- `delete_record.php` – soft delete

## Security Notes
- All DB writes use prepared statements.
- Passwords are stored as hashes only.
- Session does not store passwords.
- Uploaded files are stored outside direct public linking and served via PHP with authorization checks.

## License
MIT (optional)
