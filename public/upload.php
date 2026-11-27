<?php
require_once __DIR__ . '/../app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
verify_csrf();
$c = app_config();
$name = trim((string)($_POST['name'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$_SESSION['prefill_name'] = $name; $_SESSION['prefill_email'] = $email;
$fail = function(string $message) { $_SESSION['notice']=['type'=>'error','text'=>$message]; redirect('index.php'); };
if ($name === '' || mb_strlen($name) > 80) $fail('Please enter a name of 1–80 characters.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $fail('Please enter a valid email address.');
if (($_POST['consent'] ?? '') !== 'yes') $fail('Please confirm consent before sharing photos.');
if (!isset($_FILES['photos']) || !is_array($_FILES['photos']['name'])) $fail('Please choose at least one photo.');
$count = count($_FILES['photos']['name']);
if ($count < 1 || $count > $c['max_files']) $fail('Please upload between 1 and ' . $c['max_files'] . ' photos at a time.');
$maxBytes = $c['max_mb'] * 1024 * 1024;
$finfo = new finfo(FILEINFO_MIME_TYPE);
$allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
$validated = [];
$total = 0;
for ($i=0; $i<$count; $i++) {
    $err = $_FILES['photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    if ($err !== UPLOAD_ERR_OK) $fail($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE ? 'A photo is larger than the server upload limit.' : 'One of the photos could not be uploaded. Please try again.');
    $tmp = $_FILES['photos']['tmp_name'][$i] ?? '';
    $size = (int)($_FILES['photos']['size'][$i] ?? 0);
    if (!is_uploaded_file($tmp) || $size < 1 || $size > $maxBytes) $fail('Each photo must be smaller than ' . $c['max_mb'] . ' MB.');
    $mime = $finfo->file($tmp);
    if (!isset($allowed[$mime]) || @getimagesize($tmp) === false) $fail('Only valid JPG, PNG and WebP image files are accepted.');
    $total += $size;
    $validated[] = ['tmp'=>$tmp,'size'=>$size,'mime'=>$mime,'ext'=>$allowed[$mime],'original'=>basename((string)$_FILES['photos']['name'][$i])];
}
if ($total > $maxBytes * $c['max_files']) $fail('The combined upload is too large.');
$pdo = db();
$pdo->beginTransaction();
$moved = [];
$newPhotoIds = [];
try {
    // Atomic email upsert: handles two simultaneous first-time submissions safely.
    $visitorUpsert = $pdo->prepare(
        'INSERT INTO visitors(name,email) VALUES(?,?) '
        . 'ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), name=VALUES(name), last_seen_at=CURRENT_TIMESTAMP'
    );
    $visitorUpsert->execute([$name, $email]);
    $visitorId = (int)$pdo->lastInsertId();
    $insert = $pdo->prepare("INSERT INTO photos(visitor_id,stored_name,original_name,mime_type,size_bytes,consent_at,status) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP,'approved')");
    foreach ($validated as $file) {
        $stored = bin2hex(random_bytes(18)) . '.' . $file['ext'];
        $destination = $c['uploads'] . '/' . $stored;
        if (!move_uploaded_file($file['tmp'], $destination)) throw new RuntimeException('Could not store uploaded photo.');
        $moved[] = $destination;
        $insert->execute([$visitorId,$stored,substr($file['original'],0,180),$file['mime'],$file['size']]);
        $newPhotoIds[] = (int)$pdo->lastInsertId();
    }
    $pdo->commit();
    unset($_SESSION['prefill_name'], $_SESSION['prefill_email']);
    $_SESSION['just_uploaded_photo_ids'] = $newPhotoIds;
    $_SESSION['notice']=['type'=>'success','text'=>$count . ' photo(s) uploaded successfully! Your slideshow is ready — your new photo(s) will appear first.'];
    redirect('slideshow.php');
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    foreach ($moved as $path) if (is_file($path)) @unlink($path);
    error_log('MemoryWall upload error: ' . $ex->getMessage());
    $fail('We could not save your photos just now. Please try again.');
}
