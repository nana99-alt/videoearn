<?php
require __DIR__.'/app.php';
$page = $_GET['page'] ?? 'home';
$user = current_user();
?><!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#090b12"><title><?=e(cfg('app_name'))?></title>
<link rel="stylesheet" href="assets/style.css">
</head><body><div class="app">
<header class="top"><a class="brand" href="index.php">Video<span>Earn</span></a>
<div class="top-actions"><?php if($user && $user['role']==='admin'): ?><a class="chip" href="admin.php">Admin</a><?php endif; ?><?php if($user): ?><a class="chip" href="?page=wallet">🪙 <?=number_format((int)$user['tokens'])?></a><?php else: ?><a class="chip" href="?page=login">Masuk</a><?php endif; ?></div></header>
<?php if(isset($_SESSION['flash'])): ?><div class="flash"><?=e($_SESSION['flash']);unset($_SESSION['flash']);?></div><?php endif; ?>
<?php if($page==='login' || $page==='register'): ?>
<section class="auth"><h1><?=$page==='login'?'Masuk ke VideoEarn':'Buat akun VideoEarn'?></h1>
<form method="post" action="auth.php"><?=csrf_field()?><input type="hidden" name="action" value="<?=$page?>">
<label>Email</label><input name="email" type="email" required autocomplete="email">
<label>Password</label><input name="password" type="password" minlength="8" required autocomplete="<?=$page==='login'?'current-password':'new-password'?>">
<?php if($page==='register'): ?><label>Nama pengguna</label><input name="username" maxlength="30" required><?php endif; ?>
<button class="primary"> <?=$page==='login'?'Masuk':'Daftar'?> </button></form>
<p class="muted"><?=$page==='login'?'Belum punya akun? <a href="?page=register">Daftar</a>':'Sudah punya akun? <a href="?page=login">Masuk</a>'?></p></section>
<?php elseif($page==='wallet'): require_login(); ?>
<section class="content"><h1>Dompet Token</h1><div class="balance"><small>Saldo token</small><strong><?=number_format((int)$user['tokens'])?></strong><small>Token</small></div>
<div class="panel"><h3>Riwayat token</h3><?php $q=db()->prepare("SELECT * FROM token_ledger WHERE user_id=? ORDER BY id DESC LIMIT 30");$q->execute([$user['id']]);$rows=$q->fetchAll(); if(!$rows):?><p class="muted">Belum ada transaksi.</p><?php else: foreach($rows as $r):?><div class="line"><span><?=e($r['description'])?></span><b><?=((int)$r['amount']>0?'+':'')?><?=number_format((int)$r['amount'])?></b></div><?php endforeach;endif;?></div><p class="muted">Penukaran uang belum diaktifkan. Aktifkan setelah aturan reward, verifikasi, dan pembayaran disiapkan.</p></section>
<?php elseif($page==='profile'): require_login(); ?>
<section class="content"><div class="profile"><div class="avatar big"><?=e(strtoupper(substr($user['username'],0,1)))?></div><h1><?=e($user['username'])?></h1><p class="muted"><?=e($user['email'])?></p></div>
<div class="panel"><h3>Upload saya</h3><?php $mine=db()->prepare("SELECT status,caption,created_at FROM videos WHERE user_id=? ORDER BY id DESC LIMIT 10");$mine->execute([$user['id']]);$myVideos=$mine->fetchAll();if(!$myVideos):?><p class="muted">Belum ada upload.</p><?php else:foreach($myVideos as $mv):?><div class="line"><span><?=e($mv['caption']?:'Video tanpa caption')?></span><b><?=e($mv['status'])?></b></div><?php endforeach;endif;?></div>
<div class="panel"><h3>Akun Google Drive VideoEarn</h3><p class="muted">Video akan disimpan ke Google Drive pemilik aplikasi menggunakan koneksi OAuth admin.</p><a class="button" href="google.php?action=connect">Hubungkan Google Drive admin</a></div><div class="panel"><a href="auth.php?action=logout">Keluar</a></div></section>
<?php elseif($page==='upload'): require_login(); ?>
<section class="content"><h1>Upload video</h1><form class="panel" method="post" action="upload.php" enctype="multipart/form-data"><?=csrf_field()?><label>File video</label><input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" required><label>Caption</label><textarea name="caption" maxlength="500" placeholder="Tulis caption video..."></textarea><button class="primary">Upload video</button><p class="muted">Maksimum <?= (int)cfg('upload_max_mb')?> MB. Video akan masuk status menunggu moderasi.</p></form></section>
<?php else: ?>
<main class="feed"><?php $videos=db()->query("SELECT v.*,u.username FROM videos v JOIN users u ON u.id=v.user_id WHERE v.status='approved' ORDER BY v.created_at DESC LIMIT 50")->fetchAll(); if(!$videos):?><div class="empty"><div class="empty-icon">▶</div><h2>Belum ada video publik</h2><p>Video yang lolos moderasi akan tampil di sini.</p><?php if($user):?><a class="primary inline" href="?page=upload">Upload video pertama</a><?php else:?><a class="primary inline" href="?page=register">Buat akun</a><?php endif;?></div><?php else: foreach($videos as $v):?>
<article class="video-card"><video controls playsinline preload="metadata" src="stream.php?id=<?=(int)$v['id']?>"></video><div class="video-info"><b>@<?=e($v['username'])?></b><p><?=e($v['caption'])?></p><small>▶ VideoEarn</small></div></article>
<?php endforeach;endif;?></main>
<?php endif; ?>
<nav class="bottom"><a class="<?=$page==='home'?'active':''?>" href="index.php"><b>⌂</b>Beranda</a><?php if($user):?><a class="<?=$page==='upload'?'active':''?>" href="?page=upload"><b>＋</b>Upload</a><a class="<?=$page==='wallet'?'active':''?>" href="?page=wallet"><b>◉</b>Token</a><a class="<?=$page==='profile'?'active':''?>" href="?page=profile"><b>♙</b>Profil</a><?php else:?><a href="?page=login"><b>↪</b>Masuk</a><a href="?page=register"><b>＋</b>Daftar</a><?php endif;?></nav>
</div></body></html>
