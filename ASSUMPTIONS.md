# Assumptions and decisions

The brief leaves several product and implementation details open. These are the assumptions used in this version.

1. **Use case and audience:** The app is for a single event, venue or organization using one shared public display. This keeps the first version understandable and avoids multi-tenant account management.
2. **Visitor identity:** A normalized email address uniquely identifies a visitor. Email was requested as part of the visitor form and is easier to implement than phone OTP or facial recognition. No biometric identification is used. Email ownership is not verified in this demo; production deployment should add a verification link or OTP to prevent someone claiming another person's email.
3. **Name changes:** If an existing email is submitted with a different name, the latest submitted name becomes the display name for that visitor.
4. **Returning visitor photos:** Previous approved photos remain associated with the same visitor and continue to appear in the shared slideshow. New photos are published immediately after a visitor explicitly consents to public display. The administrator can hide/reject or delete a photo. Existing pending photos are published by the one-time migration script.
5. **Moderation:** Uploads do not require administrator approval. The visitor must explicitly consent to public display before upload; the administrator retains the ability to hide or delete inappropriate photos.
6. **Consent:** Each upload submission requires an explicit checkbox confirming that the visitor has permission to share the photos and that they may be shown publicly. The consent timestamp is stored for every photo. The visitor should only upload images of people who have agreed to public display.
7. **Withdrawal and deletion:** The visitor is instructed to contact the event administrator to request removal. The admin can reject or permanently delete photos. A self-service deletion link is outside this first version.
8. **Supported images:** Only valid JPEG, PNG and WebP images are accepted. SVG, GIF, HEIC, video and documents are not supported to reduce risk and keep the slideshow simple.
9. **Upload limits:** The default limit is 5 MB per photo and 8 photos per submission. Both are configurable. PHP's `upload_max_filesize` and `post_max_size` must also be configured to accept the request.
10. **Simultaneous uploads:** MySQL with InnoDB is used. Each photo batch is written in a transaction, and a unique email index plus atomic upsert prevents duplicate visitor records when concurrent first-time uploads use the same email. PHP/server upload limits still constrain throughput; a high-volume deployment may need queueing and object storage.
11. **Slideshow behavior:** One shared slideshow displays all live/approved photos, oldest first, and rotates every 5 seconds by default. It polls the server every 4 seconds, so newly approved photos appear without manually refreshing the TV. The screen uses the visitor's display name, not email.
12. **Photo duration:** The display duration is fixed globally via `SLIDE_DURATION_MS`, not individually configurable per visitor.
13. **Photo ordering:** Photos are shown in upload order. No AI ranking, face detection, or duplicate detection is performed.
14. **Storage:** The database and uploaded images are stored locally under `storage/`, outside the `public/` document root. This is appropriate for a local demo; production deployments need persistent backups and private storage permissions.
15. **Admin access:** There is one shared admin password configured in `.env`. The example placeholder is intentionally rejected. Role-based admin accounts and password reset are not included.
16. **No deployed link:** The project is supplied as source code and has not been deployed to a public hosting provider, so no live URL is claimed.
17. **Browser support:** A current desktop or mobile browser is assumed. The slideshow is intended to run in a modern browser in full-screen mode on the TV/computer.
18. **No notifications:** Visitors see an upload success message, but no email confirmation or notification is sent.

19. **Database setup:** The operator creates/imports the `memorywall` database using `database/schema.sql` (phpMyAdmin Import is the recommended XAMPP path). The app does not attempt to create a database using broad server privileges on first page load.
20. **Local MySQL credentials:** XAMPP defaults are assumed for local development (`127.0.0.1:3306`, user `root`, blank password); deployments must provide their own least-privilege database account and credentials in `.env`.
