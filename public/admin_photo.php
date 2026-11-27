<?php
require_once __DIR__ . '/../app/bootstrap.php';
if (empty($_SESSION['admin'])) { http_response_code(403); exit; }
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT); if(!$id){http_response_code(404);exit;}
$s=db()->prepare('SELECT stored_name,mime_type FROM photos WHERE id=?');$s->execute([$id]);$p=$s->fetch();
if(!$p){http_response_code(404);exit;}
$path=app_config()['uploads'].'/'.$p['stored_name'];if(!is_file($path)){http_response_code(404);exit;}
header('Content-Type: '.$p['mime_type']);header('X-Content-Type-Options: nosniff');header('Cache-Control: no-store');readfile($path);
