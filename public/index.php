<?php
require_once __DIR__ . '/../app/bootstrap.php';
$c = app_config();
$notice = $_SESSION['notice'] ?? null;
unset($_SESSION['notice']);
$prefillName = $_SESSION['prefill_name'] ?? '';
$prefillEmail = $_SESSION['prefill_email'] ?? '';
unset($_SESSION['prefill_name'], $_SESSION['prefill_email']);
$existing = null;
if ($prefillEmail !== '') $existing = visitor_by_email(strtolower(trim($prefillEmail)));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e($c['name'])?> · Share a memory</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="visitor-page">
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">✳</span> MemoryWall</a><a class="quiet-link" href="slideshow.php" target="_blank">View the wall <span>↗</span></a></header>
<main class="hero-wrap">
  <section class="hero-copy">
    <div class="eyebrow"><span class="live-dot"></span> A little space for shared memories</div>
    <h1>Good moments<br><em>belong on the wall.</em></h1>
    <p class="hero-sub">Add your photos and let everyone enjoy the memories. If you have visited before, we’ll bring your earlier shared photos along too.</p>
    <div class="mini-points"><span>✦ Simple upload</span><span>✦ Automatic slideshow</span><span>✦ Your choice, always</span></div>
    <div class="preview-frame"><div class="preview-photo preview-one"></div><div class="preview-photo preview-two"></div><div class="preview-photo preview-three"></div><div class="preview-caption"><span>MEMORIES, TOGETHER</span><b>Every visit adds a story.</b></div></div>
  </section>
  <section class="form-card">
    <div class="card-kicker">WELCOME IN</div><h2>Share your moment</h2><p class="muted">A few details, then choose the photos you’d like to share.</p>
    <?php if ($notice): ?><div class="notice <?=e($notice['type'])?>"><?=e($notice['text'])?></div><?php endif; ?>
    <?php if ($existing): ?><div class="returning-note">✦ Welcome back! We found your earlier photos. Your new photos will appear on the wall right away.</div><?php endif; ?>
    <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
      <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
      <label for="name">Your name</label><input id="name" name="name" type="text" maxlength="80" placeholder="e.g. Aanya Shah" value="<?=e($prefillName)?>" required autocomplete="name">
      <label for="email">Email address</label><input id="email" name="email" type="email" maxlength="190" placeholder="you@example.com" value="<?=e($prefillEmail)?>" required autocomplete="email">
      <div class="field-hint">We use your email to find your previous visits. It is not shown on the public photo wall.</div>
      <label class="upload-label" for="photos">Choose your photos <span>UP TO <?= (int)$c['max_files'] ?> FILES</span></label>
      <label class="dropzone" for="photos" id="dropzone"><span class="upload-icon">↑</span><b>Drop photos here or <u>browse files</u></b><small>JPG, PNG or WebP · Up to <?= (int)$c['max_mb'] ?> MB each</small><span id="fileList" class="file-list">No photos selected yet</span></label>
      <input class="visually-hidden" id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required>
      <label class="consent-row"><input type="checkbox" name="consent" value="yes" required><span>I confirm these photos are okay to share publicly on the event display. I have permission from the people shown and agree that these photos will appear publicly on the event display. I understand the administrator may remove a photo if needed.</span></label>
      <button class="primary-btn" type="submit" id="submitBtn">Upload my memories <span>→</span></button>
      <p class="privacy-note">Your photos appear on the public screen as soon as the upload completes. You can ask the event administrator to remove them.</p>
    </form>
  </section>
</main>
<footer class="site-footer"><span>MEMORYWALL</span><span>Made for moments worth remembering.</span><a href="admin.php">Admin</a></footer>
<script>
const input=document.getElementById('photos'), list=document.getElementById('fileList'), zone=document.getElementById('dropzone'), form=document.getElementById('uploadForm');
function showFiles(){list.textContent=input.files.length ? [...input.files].map(f=>f.name).join(' · ') : 'No photos selected yet';}
input.addEventListener('change',showFiles);
['dragenter','dragover'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();zone.classList.add('dragging')}));
['dragleave','drop'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();zone.classList.remove('dragging')}));
zone.addEventListener('drop',e=>{if(e.dataTransfer.files.length){input.files=e.dataTransfer.files;showFiles();}});
form.addEventListener('submit',()=>{const b=document.getElementById('submitBtn');b.disabled=true;b.innerHTML='Uploading… <span>✦</span>';});
</script>
</body></html>
