<?php
require_once __DIR__ . '/../app/bootstrap.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit; }
$s=db()->prepare("SELECT stored_name,mime_type FROM photos WHERE id=? AND status='approved'");
$s->execute([$id]); $p=$s->fetch();
if (!$p) { http_response_code(404); exit; }
$path=app_config()['uploads'].'/'.$p['stored_name'];
if (!is_file($path)) { http_response_code(404); exit; }
header('Content-Type: '.$p['mime_type']); header('Content-Length: '.filesize($path));
header('X-Content-Type-Options: nosniff'); header('Cache-Control: public, max-age=60');
readfile($path);
