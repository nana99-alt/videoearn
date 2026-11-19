<?php
require __DIR__.'/app.php';
$id=(int)($_GET['id']??0);$q=db()->prepare("SELECT drive_file_id,mime_type FROM videos WHERE id=? AND status='approved'");$q->execute([$id]);$v=$q->fetch();if(!$v){http_response_code(404);exit('Video tidak ditemukan');}
try{$token=google_access_token();}catch(Throwable $e){http_response_code(503);exit('Streaming belum tersedia');}
$range=$_SERVER['HTTP_RANGE']??'';$url='https://www.googleapis.com/drive/v3/files/'.rawurlencode($v['drive_file_id']).'?alt=media';
$headers=['Authorization: Bearer '.$token];if($range)$headers[]='Range: '.$range;
$ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_FOLLOWLOCATION=>true,CURLOPT_RETURNTRANSFER=>false,CURLOPT_HEADER=>false,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>0,CURLOPT_WRITEFUNCTION=>function($ch,$data){echo $data;flush();return strlen($data);}]);
header('Content-Type: '.$v['mime_type']);header('Accept-Ranges: bytes');header('Cache-Control: public, max-age=3600');curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($status>=400&&!headers_sent())http_response_code($status);
