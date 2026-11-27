# MemoryWall — PHP + MySQL Visitor Photo Gallery

MemoryWall is a responsive visitor photo-upload website with an instant-publication slideshow for a smart TV or computer display.

## Features
- Visitor enters a name, email address and one or more photos.
- Normalized email identifies returning visitors; their earlier photos remain linked to the same visitor.
- Explicit public-display consent is required and timestamped for each uploaded photo.
- New photos are saved with `approved` status. After a successful upload, the visitor is redirected directly to the slideshow, sees a success notification, and their newly uploaded photo(s) are prioritized at the start of the slideshow. The administrator can still hide/reject or delete a photo.
- Admin can restore hidden photos, hide photos from the public wall or permanently delete them.
- TV slideshow polls for live (approved) photos and rotates them automatically. Newly uploaded photos are prioritized on the next feed refresh; the upload redirect opens the slideshow immediately and refreshes the feed every 2 seconds.
- JPG, PNG and WebP validation; default 5 MB per image and 8 images per submission.
- Photos are stored on disk outside the public web root; MySQL stores visitor and photo metadata.

## Stack
- PHP 8.1+ (plain PHP, no Composer required)
- MySQL 5.7+/8.x or compatible MariaDB, accessed with PDO MySQL
- PHP extensions: `pdo_mysql`, `fileinfo`, `mbstring`
- HTML, CSS and vanilla JavaScript

## Run locally with XAMPP on Windows

1. Extract the project folder. Open it in VS Code.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL** (Apache is only needed to open phpMyAdmin; the website itself will run on PHP's built-in server).
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Select **Import**, choose `database/schema.sql`, and click **Import**. The script creates the `memorywall` database and both tables.
5. Copy `.env.example` to `.env` in the project root (same level as `app`, `public`, `storage`, and `tests`).
6. Edit `.env` to match your MySQL account. Default XAMPP values are usually:

   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=memorywall
   DB_USER=root
   DB_PASS=
   DB_CHARSET=utf8mb4
   ```

   Set `ADMIN_PASSWORD` to a new unique password of at least 12 characters. Never commit `.env`.
7. Ensure the PHP CLI uses the XAMPP `php.ini` and that `pdo_mysql`, `fileinfo`, and `mbstring` are enabled. Check with `php -m` (or the full PHP path below).
8. From the project root, start the app. If your XAMPP folder is `C:\xampp2`, run:

   ```powershell
   & "C:\xampp2\php\php.exe" -S localhost:8000 -t public
   ```

   If PHP is on PATH, use `php -S localhost:8000 -t public` instead.
9. Open these pages:
   - Visitor form: http://localhost:8000/
   - TV slideshow: http://localhost:8000/slideshow.php
   - Admin photo management: http://localhost:8000/admin.php

Keep the terminal running while using the website. Press `Ctrl+C` to stop the PHP server.

## Verify PHP extensions

```powershell
& "C:\xampp2\php\php.exe" -m
```

Confirm the output includes `PDO`, `pdo_mysql`, `fileinfo`, and `mbstring`. If `pdo_mysql` is missing, open `C:\xampp2\php\php.ini`, find `;extension=pdo_mysql`, remove the leading semicolon, save, and restart the terminal/server. (If the extension line is absent, check the XAMPP PHP distribution before proceeding.)

## Database structure

`database/schema.sql` creates:
- `visitors`: one row per normalized email address.
- `photos`: uploaded image metadata, consent timestamp and display status; each photo belongs to one visitor. New rows default to `approved` for immediate display.

Both tables use InnoDB and `utf8mb4`. A unique index on `visitors.email` plus an atomic MySQL upsert handles concurrent first-time submissions for the same email. Each photo batch's metadata writes run in a transaction; if a file or insert fails, the transaction rolls back and files already moved in that batch are removed.

## Configuration

| Variable | Default | Meaning |
|---|---|---|
| `APP_NAME` | MemoryWall | Display name |
| `APP_ENV` | development | Environment label |
| `APP_URL` | http://localhost:8000 | Local app URL |
| `DB_HOST` | 127.0.0.1 | MySQL host |
| `DB_PORT` | 3306 | MySQL port |
| `DB_NAME` | memorywall | Database name |
| `DB_USER` | root | MySQL username |
| `DB_PASS` | empty | MySQL password; set if configured |
| `DB_CHARSET` | utf8mb4 | Connection character set |
| `UPLOAD_DIR` | storage/uploads | Private photo storage path |
| `MAX_UPLOAD_MB` | 5 | Maximum size per image |
| `MAX_FILES_PER_UPLOAD` | 8 | Maximum images per submission |
| `SLIDE_DURATION_MS` | 5000 | Duration of each slide |
| `ADMIN_PASSWORD` | required | Admin password (12+ characters) |

PHP's `php.ini` upload limits must be at least as large as the app limit. For example: `upload_max_filesize=5M`, `post_max_size=45M`, `max_file_uploads=20`.

## Privacy and security
- Public display consent is mandatory and recorded for every uploaded photo.
- Uploads are immediately eligible for public display after the visitor checks the explicit consent box. Public image delivery serves only live/approved photos; admin image delivery requires an admin session. The administrator retains hide and delete controls.
- Email is used for visitor matching and is not shown on the public slideshow.
- Server-side MIME/content validation and randomized storage names are used.
- PDO prepared statements, InnoDB transactions, CSRF tokens and admin session checks are used.
- For a real public deployment, use HTTPS, a strong unique admin password, email verification, rate limiting, backups, monitoring and a documented retention/deletion process. The shared-password admin is intended for a small demo, not a high-security production environment.

## Tests
Run the environment and project smoke test:

```powershell
& "C:\xampp2\php\php.exe" tests/smoke.php
```

Then follow `tests/MANUAL_TEST_PLAN.md` for registration, repeat-visitor, upload, moderation and slideshow checks. The smoke test checks for the MySQL PDO driver; a live database test requires MySQL to be running and `database/schema.sql` imported.

## Git history
See `COMMIT_PLAN.md` for suggested meaningful milestones. Commit in stages rather than as one final snapshot.

## Deployment
No public deployment has been configured. For hosting, point the document root to `public/`, configure MySQL credentials and private writable upload storage, enable HTTPS, and never expose `.env` or `storage/`.

## Existing database upgrade

If you already imported the earlier schema, import `database/migration_auto_publish.sql` once in phpMyAdmin to publish old pending photos and set the default status to approved. Fresh installs should import `database/schema.sql`.
