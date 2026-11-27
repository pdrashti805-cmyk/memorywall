<?php
require_once __DIR__ . '/../app/bootstrap.php';
$c=app_config();
if (isset($_GET['logout'])) { unset($_SESSION['admin']); redirect('admin.php'); }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['login'])) {
    verify_csrf();
    $configured=$c['admin_password'];
    if ($configured==='CHANGE_THIS_TO_A_LONG_RANDOM_PASSWORD' || strlen($configured)<12) $error='Set a strong ADMIN_PASSWORD in your .env file before using the admin area.';
    elseif (hash_equals($configured,(string)($_POST['password']??''))) { $_SESSION['admin']=true; redirect('admin.php'); }
    else $error='Incorrect password. Please try again.';
}
$authed=!empty($_SESSION['admin']);
if ($authed && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    verify_csrf();
    $id=filter_var($_POST['photo_id']??null,FILTER_VALIDATE_INT);
    if ($id) {
        $s=db()->prepare('SELECT stored_name FROM photos WHERE id=?');$s->execute([$id]);$photo=$s->fetch();
        if ($photo) {
            if ($_POST['action']==='approve') { $q=db()->prepare("UPDATE photos SET status='approved' WHERE id=?");$q->execute([$id]); }
            elseif ($_POST['action']==='reject') { $q=db()->prepare("UPDATE photos SET status='rejected' WHERE id=?");$q->execute([$id]); }
            elseif ($_POST['action']==='delete') { $q=db()->prepare('DELETE FROM photos WHERE id=?');$q->execute([$id]);$path=$c['uploads'].'/'.$photo['stored_name'];if(is_file($path))@unlink($path); }
        }
    }
    redirect('admin.php');
}
$rows=$authed?db()->query("SELECT p.*,v.name,v.email FROM photos p JOIN visitors v ON v.id=p.visitor_id ORDER BY p.uploaded_at DESC")->fetchAll():[];
$stats=$authed?db()->query("SELECT COUNT(*) total, SUM(status='pending') pending, SUM(status='approved') approved, COUNT(DISTINCT visitor_id) visitors FROM photos")->fetch():[];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>MemoryWall · Admin</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="admin-page"><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">✳</span> MemoryWall</a><a class="quiet-link" href="slideshow.php">View wall ↗</a></header>
<main class="admin-wrap">
<?php if(!$authed): ?>
<section class="login-card"><div class="card-kicker">PRIVATE AREA</div><h1>Admin sign in</h1><p class="muted">Manage photos currently displayed on the public wall.</p><?php if($error):?><div class="notice error"><?=e($error)?></div><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label for="password">Admin password</label><input type="password" id="password" name="password" required autocomplete="current-password"><button class="primary-btn" name="login" value="1">Sign in <span>→</span></button></form>
<p class="privacy-note">Configure ADMIN_PASSWORD in your local .env file. The example password is intentionally disabled.</p></section>
<?php else: ?>
<div class="admin-heading"><div><div class="card-kicker">CONTROL ROOM</div><h1>Your memory wall</h1><p class="muted">New uploads appear on the public screen immediately. You can remove or hide any photo here.</p></div><a class="outline-btn" href="admin.php?logout=1">Sign out</a></div>
<div class="stats-grid"><div class="stat-card"><span>ALL PHOTOS</span><b><?= (int)($stats['total']??0)?></b></div><div class="stat-card"><span>HIDDEN / PENDING</span><b><?= (int)($stats['pending']??0)?></b></div><div class="stat-card"><span>LIVE PHOTOS</span><b><?= (int)($stats['approved']??0)?></b></div><div class="stat-card"><span>VISITORS</span><b><?= (int)($stats['visitors']??0)?></b></div></div>
<div class="review-header"><h2>Photo submissions</h2><span class="muted"><?=count($rows)?> total</span></div>
<?php if(!$rows):?><div class="empty-admin">No photos have been uploaded yet.</div><?php else:?><div class="review-grid">
<?php foreach($rows as $r):?><article class="review-card"><div class="review-image"><img src="admin_photo.php?id=<?=(int)$r['id']?>" alt="Visitor photo"></div><div class="review-content"><div class="review-person"><b><?=e($r['name'])?></b><span><?=e($r['email'])?></span></div><div class="review-meta"><span class="status status-<?=e($r['status'])?>"><?=e(strtoupper($r['status']))?></span><span><?=number_format((int)$r['size_bytes']/1048576,2)?> MB</span></div><div class="review-actions">
<?php if($r['status']!=='approved'):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="photo_id" value="<?=(int)$r['id']?>"><button class="small-btn approve" name="action" value="approve">Show on wall</button></form><?php endif;?>
<?php if($r['status']!=='rejected'):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="photo_id" value="<?=(int)$r['id']?>"><button class="small-btn reject" name="action" value="reject">Hide from wall</button></form><?php endif;?>
<form method="post" onsubmit="return confirm('Permanently delete this photo?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="photo_id" value="<?=(int)$r['id']?>"><button class="small-btn delete" name="action" value="delete">Delete</button></form></div></div></article><?php endforeach;?></div><?php endif;?>
<?php endif;?>
</main></body></html>
