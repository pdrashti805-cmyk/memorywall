-- Run this ONCE on an existing MemoryWall MySQL database to publish photos
-- that were waiting for the old administrator-approval workflow.
USE memorywall;
UPDATE photos SET status='approved' WHERE status='pending';
ALTER TABLE photos MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved';
