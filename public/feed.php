<?php
require_once __DIR__ . '/../app/bootstrap.php';
$rows=db()->query("SELECT p.id, p.uploaded_at, v.name FROM photos p JOIN visitors v ON v.id=p.visitor_id WHERE p.status='approved' ORDER BY p.uploaded_at ASC, p.id ASC")->fetchAll();
$photos=array_map(fn($r)=>['id'=>(int)$r['id'],'name'=>$r['name'],'version'=>strtotime($r['uploaded_at']) ?: time()],$rows);
json_response(['photos'=>$photos,'count'=>count($photos)]);
