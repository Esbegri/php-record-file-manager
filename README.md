# 📂 PHP Record & File Manager v2.0 (Modernized)

A secure, modular, and lightweight PHP + MySQL record management system.

## ✨ Key Enhancements in v2.0
- **Modular Architecture:** Separated logic into `app/`, `config/`, and `views/` for better maintainability.
- **PDO Integration:** Transitioned from mysqli to PDO for robust SQL injection protection.
- **Secure File Handling:** Files are stored outside public access and served via an authenticated PHP stream.
- **Improved UI:** Fully responsive dashboard with Bootstrap 4 and Dynamic Modals.
- **Security Hardening:** CSRF protection, secure sessions, and environment-aware file paths.

## 🛠️ Tech Stack
- **Backend:** PHP 8.x (PDO, Session Security)
- **Database:** MySQL (MariaDB)
- **Frontend:** Bootstrap 4, DataTables, FontAwesome 6

## 🚀 Installation (XAMPP)
1. Clone the repository to `C:\xampp\htdocs\kayit`.
2. Configure your database in `config/config.php` (Rename from `config.php.example` if needed).
3. Import `schema.sql` via phpMyAdmin.
4. Ensure `storage/uploads` is writable.
5. Visit `http://localhost/kayit/login_form.php`.

## 🔒 Security
- Use `.gitignore` to prevent sensitive `config.php` and user uploads from being public.
- Passwords hashed via `password_hash()`.