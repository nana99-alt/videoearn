<?php
require __DIR__.'/app.php';
if(($_GET['action']??'')==='logout'){session_destroy();header('Location: index.php');exit;}
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
verify_csrf(); rate_limit('auth', 12, 300);
$action=$_POST['action']??'';$email=strtolower(trim($_POST['email']??''));$pass=$_POST['password']??'';
try{
 if($action==='register'){
  $username=trim($_POST['username']??'');if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<8||$username==='')throw new RuntimeException('Data pendaftaran tidak valid.');
  $q=db()->prepare("INSERT INTO users(email,password_hash,username) VALUES(?,?,?)");$q->execute([$email,password_hash($pass,PASSWORD_DEFAULT),mb_substr($username,0,30)]);session_regenerate_id(true);$_SESSION['uid']=(int)db()->lastInsertId();flash('Akun berhasil dibuat.');
 }elseif($action==='login'){
  $q=db()->prepare("SELECT id,password_hash FROM users WHERE email=?");$q->execute([$email]);$u=$q->fetch();if(!$u||!password_verify($pass,$u['password_hash']))throw new RuntimeException('Email atau password salah.');session_regenerate_id(true);$_SESSION['uid']=(int)$u['id'];flash('Berhasil masuk.');
 }
 header('Location: index.php');exit;
}catch(Throwable $e){flash($e->getMessage());header('Location: index.php?page='.($action==='register'?'register':'login'));exit;}
