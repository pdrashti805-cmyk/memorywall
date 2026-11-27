<?php
require_once __DIR__ . '/../app/bootstrap.php';
$c=app_config();
$justUploadedIds = $_SESSION['just_uploaded_photo_ids'] ?? [];
unset($_SESSION['just_uploaded_photo_ids']);
$uploadNotice = $_SESSION['notice'] ?? null;
unset($_SESSION['notice']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>MemoryWall · The Wall</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="screen-page">
<div class="screen-top"><a class="screen-brand" href="index.php">✳ MEMORYWALL</a><span><i class="live-dot"></i> LIVE MEMORY WALL</span><a href="index.php">Add a memory ↗</a></div>
<?php if ($uploadNotice && ($uploadNotice['type'] ?? '') === 'success'): ?>
<div id="uploadNotice" class="notice success" role="status" style="position:fixed;z-index:20;top:76px;left:50%;transform:translateX(-50%);width:min(92vw,620px);padding:15px 22px;border-radius:12px;box-shadow:0 12px 35px #0005;text-align:center;font-size:14px;line-height:1.5;background:#e9f3e7;color:#345b3a;">
  <strong>✓ Photo uploaded successfully!</strong><br><?=e($uploadNotice['text'])?>
</div>
<?php endif; ?>
<main class="screen-stage" id="stage">
  <div class="empty-wall"><div class="empty-symbol">✳</div><p class="eyebrow">A WALL OF SHARED MOMENTS</p><h1>Every memory<br><em>starts somewhere.</em></h1><p>Visitor photos will appear here automatically as soon as they are uploaded.</p></div>
</main>
<div class="screen-bottom"><span id="photoCount">WAITING FOR MEMORIES</span><span>ONE MOMENT AT A TIME · <b>MEMORYWALL</b></span></div>
<script>
const stage=document.getElementById('stage'), count=document.getElementById('photoCount');
let photos=[], index=0, timer=null, currentId=null;
const priorityIds=<?=json_encode(array_values(array_map('intval', (array)$justUploadedIds)), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;
function render(){
 if(!photos.length){stage.innerHTML='<div class="empty-wall"><div class="empty-symbol">✳</div><p class="eyebrow">A WALL OF SHARED MOMENTS</p><h1>Every memory<br><em>starts somewhere.</em></h1><p>Visitor photos will appear here automatically as soon as they are uploaded.</p></div>';count.textContent='WAITING FOR MEMORIES';return;}
 const p=photos[index%photos.length]; currentId=p.id;
 stage.innerHTML=`<div class="slide-photo-wrap"><img class="slide-photo" src="photo.php?id=${encodeURIComponent(p.id)}&v=${encodeURIComponent(p.version)}" alt="Shared visitor memory"><div class="slide-overlay"><span class="slide-eyebrow">A SHARED MEMORY</span><div class="slide-name">${escapeHtml(p.name)}</div></div></div><div class="slide-progress"><span></span></div>`;
 count.textContent=String(index+1).padStart(2,'0')+' / '+String(photos.length).padStart(2,'0')+' MEMORIES';
}
function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
async function refresh(){
 try{
  const r=await fetch('feed.php',{cache:'no-store'}); if(!r.ok)return;
  const data=await r.json(); let next=data.photos||[];
  if(priorityIds.length){const rank=new Map(priorityIds.map((id,i)=>[Number(id),i]));next.sort((a,b)=>(rank.has(Number(a.id))?rank.get(Number(a.id)):999999)-(rank.has(Number(b.id))?rank.get(Number(b.id)):999999));}
  const oldId=photos[index]?.id;
  if(JSON.stringify(next.map(p=>p.id))!==JSON.stringify(photos.map(p=>p.id))){
   photos=next; const found=photos.findIndex(p=>p.id===oldId); index=found>=0?found:Math.min(index,Math.max(0,photos.length-1)); render();
  }
 }catch(e){}
}
refresh(); setInterval(refresh,2000);
const uploadNotice=document.getElementById('uploadNotice'); if(uploadNotice){setTimeout(()=>{uploadNotice.style.transition='opacity .6s ease, transform .6s ease';uploadNotice.style.opacity='0';uploadNotice.style.transform='translate(-50%,-12px)';setTimeout(()=>uploadNotice.remove(),700);},7000);}
setInterval(()=>{if(photos.length){index=(index+1)%photos.length;render();}},<?= (int)$c['slide_ms'] ?>);
</script></body></html>
