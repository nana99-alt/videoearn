<?php
require __DIR__.'/app.php';
require_admin();
$g=cfg('google');
$action=$_GET['action']??'';
if ($action === 'connect') {
    if (!$g['client_id'] || !$g['client_secret']) exit('Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada konfigurasi server.');
    $state=bin2hex(random_bytes(16));
    $_SESSION['oauth_state']=$state;
    $params=['client_id'=>$g['client_id'],'redirect_uri'=>cfg('base_url').'/google.php?action=callback','response_type'=>'code','scope'=>'https://www.googleapis.com/auth/drive.file','access_type'=>'offline','prompt'=>'consent','state'=>$state];
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params));
    exit;
}
if ($action === 'callback') {
    $state=$_GET['state']??'';
    if (!$state || !hash_equals((string)($_SESSION['oauth_state']??''), $state)) exit('OAuth state tidak valid.');
    unset($_SESSION['oauth_state']);
    if (empty($_GET['code'])) exit('Google tidak mengembalikan kode OAuth.');
    $ch=curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_POSTFIELDS=>http_build_query(['code'=>$_GET['code'],'client_id'=>$g['client_id'],'client_secret'=>$g['client_secret'],'redirect_uri'=>cfg('base_url').'/google.php?action=callback','grant_type'=>'authorization_code']),CURLOPT_TIMEOUT=>30]);
    $raw=curl_exec($ch);$err=curl_error($ch);curl_close($ch);
    if (!$raw) exit('Koneksi Google gagal: '.e($err));
    $d=json_decode($raw,true);
    if (empty($d['refresh_token'])) exit('Google tidak mengembalikan refresh token. Hapus akses aplikasi dari akun Google, lalu coba hubungkan lagi.');
    $q=db()->prepare("INSERT INTO settings(name,value) VALUES('google_refresh_token',?) ON DUPLICATE KEY UPDATE value=VALUES(value)");
    $q->execute([$d['refresh_token']]);
    flash('Google Drive admin berhasil dihubungkan.');
    header('Location: index.php?page=profile');
    exit;
}
header('Location: index.php');
