<?php
require __DIR__.'/app.php';require_login(); verify_csrf(); rate_limit('upload', 5, 300);
try{
 if(empty($_FILES['video'])||$_FILES['video']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Upload file gagal.');
 $f=$_FILES['video'];$max=(int)cfg('upload_max_mb')*1024*1024;if($f['size']>$max)throw new RuntimeException('Ukuran video melewati batas.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$allowed=['video/mp4','video/webm','video/quicktime'];if(!in_array($mime,$allowed,true))throw new RuntimeException('Format harus MP4, WEBM, atau MOV.');
 $token=google_access_token();$g=cfg('google');$metadata=['name'=>preg_replace('/[^a-zA-Z0-9._ -]/','_',basename($f['name'])),'mimeType'=>$mime];if(!empty($g['folder_id']))$metadata['parents']=[$g['folder_id']];
 $boundary='vEarn'.bin2hex(random_bytes(8));$body="--$boundary\r\nContent-Type: application/json; charset=UTF-8\r\n\r\n".json_encode($metadata)."\r\n--$boundary\r\nContent-Type: $mime\r\n\r\n".file_get_contents($f['tmp_name'])."\r\n--$boundary--";
 [$status,$raw]=google_api('POST','https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,mimeType,size',$token,['Content-Type: multipart/related; boundary='.$boundary],$body);
 $data=json_decode($raw,true);if($status<200||$status>=300||empty($data['id']))throw new RuntimeException('Google Drive menolak upload: '.($data['error']['message']??'HTTP '.$status));
 $fileId=$data['id'];
 // File tetap privat selama moderasi; admin akan mempublikasikannya saat approval.
 $q=db()->prepare("INSERT INTO videos(user_id,drive_file_id,mime_type,caption,status) VALUES(?,?,?,?, 'pending')");$q->execute([$_SESSION['uid'],$fileId,$mime,trim($_POST['caption']??'')]);
 flash('Video berhasil diunggah dan menunggu persetujuan admin.');header('Location: index.php');exit;
}catch(Throwable $e){flash($e->getMessage());header('Location: index.php?page=upload');exit;}
