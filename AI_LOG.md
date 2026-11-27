# AI interaction log

This log records representative AI-assisted design and implementation decisions for this project. The incorrect suggestions below are examples of suggestions that were reviewed and deliberately not used; they are documented to show verification rather than blind acceptance.

1. **Prompt:** "Explain the visitor photo-wall assignment in simple words."
   - **Useful result:** Identified the main flow as visitor details → photo upload → shared slideshow → returning visitor history.
   - **Applied:** Built separate visitor and slideshow pages.

2. **Prompt:** "Suggest a simple stack for a beginner-friendly full-stack implementation."
   - **Useful result:** PHP, SQLite, HTML/CSS and JavaScript fit the requested PHP implementation and avoid a frontend build tool.
   - **Applied:** Used plain PHP with PDO SQLite.

3. **Prompt:** "How should returning visitors be identified?"
   - **Useful result:** Compared email, phone OTP and a generated visitor code.
   - **Applied:** Used normalized email as a unique visitor key, as the project form asks for email.

4. **Prompt (incorrect suggestion #1):** "Can I trust the file extension to validate uploaded photos?"
   - **Incorrect AI suggestion:** Checking whether the filename ends in `.jpg` or `.png` is enough.
   - **How the error was noticed:** A filename can be renamed to look like an image while containing unrelated or unsafe data; browser `accept` values are also not security controls.
   - **Correction:** The implementation uses PHP Fileinfo to inspect MIME type and `getimagesize()` to confirm image content, and then assigns a random server-side filename.

5. **Prompt:** "What should happen to new public visitor photos?"
   - **Useful result:** Public display can expose people who did not expect their image to be shown.
   - **Applied:** Added explicit consent and pending-by-default moderation. Only approved photos are returned by the public feed.

6. **Prompt (incorrect suggestion #2):** "Can I place uploaded images in the public uploads folder and show their direct URLs?"
   - **Incorrect AI suggestion:** A public folder is the simplest option and direct URLs are fine for every uploaded file.
   - **How the error was noticed:** Anyone who guesses or receives a URL could view pending or rejected photos, bypassing the approval process.
   - **Correction:** Files are stored outside `public/`; `photo.php` serves approved images only, and `admin_photo.php` requires an admin session.

7. **Prompt:** "How can the TV update without manual refresh?"
   - **Useful result:** A small JSON endpoint can be polled by the slideshow page.
   - **Applied:** `slideshow.php` polls `feed.php` every four seconds and rotates images on a configurable timer.

8. **Prompt:** "What should I document for concurrent uploads and deployment?"
   - **Useful result:** A single SQLite instance is suitable for a small demo but not high-volume multi-server traffic.
   - **Applied:** Enabled SQLite WAL and a busy timeout, wrapped each submission in a transaction, and documented when to upgrade the database and storage.

9. **Prompt:** "Review the project setup for someone running it locally."
   - **Useful result:** A clear PHP built-in server command and PHP extension checklist reduce setup friction.
   - **Applied:** Added setup steps, `.env.example`, a smoke test and a manual test plan.


## MySQL conversion follow-up (2026-11-27)

8. **User request:** “Convert the connectivity and the whole schema database into MySQL and give me the whole project in runnable state.”
   - **Decision:** Preserve the existing PHP pages and styling; replace only the persistence layer and database-specific setup/documentation.
   - **Applied:** Switched the PDO DSN to `mysql:`, added MySQL environment settings, created `database/schema.sql`, updated visitor identity handling to an atomic unique-email upsert, and rewrote setup/test/architecture documentation for XAMPP MySQL.
9. **Validation correction:** The original SQLite-specific smoke test and documentation were no longer valid after the migration.
   - **How noticed:** Searched the full source for `sqlite`, `DB_PATH`, and `pdo_sqlite` references.
   - **Fix:** Replaced the smoke-test driver requirement with `pdo_mysql`, removed SQLite connection code and updated all setup and architecture references.
