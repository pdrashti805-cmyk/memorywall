# Manual test plan

Run the app locally using the README instructions. These are the expected checks; fill in the **Actual result** column while testing on your machine. The source package has not been run in your PHP environment, so no unverified pass results are claimed.

| # | Test | Steps | Expected result | Actual result |
|---|---|---|---|---|
| 1 | First-time visitor | Enter a new name/email, consent, upload a valid JPG | Visitor is created; photo is approved/live immediately; success message appears | Not run |
| 2 | Required fields | Submit without name, email, file or consent | Browser/server rejects the incomplete submission | Not run |
| 3 | Invalid email | Enter malformed email | Server rejects it | Not run |
| 4 | Returning visitor | Upload again using the same email | Existing visitor is reused; previous photos remain associated | Not run |
| 5 | Unsupported file | Rename a text file to `.jpg` and upload | Server rejects it based on actual content/MIME | Not run |
| 6 | Oversized file | Upload a photo larger than configured limit | Server rejects it with a clear message | Not run |
| 7 | Too many files | Select more than configured maximum | Server rejects the submission | Not run |
| 9 | Immediate publication | Upload a photo with consent and open slideshow | Photo appears within the feed polling interval without admin approval | Not run |
| 10 | Reject photo | Hide an approved photo | Photo disappears from the public feed | Not run |
| 11 | Delete photo | Permanently delete a photo from admin | Database row and stored file are removed | Not run |
| 12 | Concurrent visitors | Submit uploads from two separate browser sessions close together | Both submissions are saved without one overwriting the other's files | Not run |
| 13 | Privacy | Inspect public feed and slideshow | Email addresses and hidden/rejected images are not exposed | Not run |
| 14 | CSRF protection | Remove/change the CSRF token and submit | Request is rejected with HTTP 419 | Not run |

## Lightweight automated smoke test

Run `php tests/smoke.php` from the project root. It checks PHP modules and required project files; it does not replace browser-based upload/moderation testing.


## Upload redirect and success notification
1. Open the visitor form and submit one valid photo with public-display consent.
2. Confirm the browser redirects to `slideshow.php` (not back to the visitor form).
3. Confirm a visible success notification says the photo uploaded successfully.
4. Confirm the newly uploaded photo appears first in the slideshow, then normal rotation continues.
5. Repeat with multiple photos and confirm each newly uploaded photo is shown before older photos.
