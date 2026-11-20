# Yanet Demeke | PHP Portfolio

PHP adaptation of Yanet's original personal portfolio, retaining its black-and-purple glow theme, navigation, home/profile, about, hobbies, skills, and contact sections. The contact form stores messages in SQLite. The form saves messages to the database; it does not compose or send email.

## Profile
- Name: Yanet Demeke
- Contact: yanetdemeke234@gmail.com

## Requirements
- PHP 8.0+
- PDO SQLite extension (`pdo_sqlite`)
- Writable `data/` directory

The original repository contains `index.html`, `style.css`, and `myphoto.jpg.jpg`. This PHP version rebuilds the same section structure and visual direction. A photo placeholder is used rather than copying the original repository photo; add Yanet's own image if she wants one.

## Run locally
```bash
php -S localhost:8000
```
Open http://localhost:8000.

For XAMPP on Windows:
```powershell
C:\xampp\php\php.exe -S localhost:8000
```

## Database
The first valid form submission creates `data/messages.sqlite` and the `messages` table. Prepared statements are used. The database file is excluded from Git because it contains private visitor messages.

## Before publishing
1. Replace sample skills and project descriptions in `config.php` with Yanet's real information.
2. Add real work samples and a profile photo if desired.
3. Test the contact form on a server with `pdo_sqlite` enabled.
4. Use HTTPS and restrict database access. Add an authenticated admin interface before exposing stored messages.
