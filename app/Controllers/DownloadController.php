<?php
namespace App\Controllers;
use App\Core\Database;
final class DownloadController{public function file():never{$s=Database::db()->prepare("SELECT p.original_path,p.download_enabled FROM orders o JOIN order_items i ON i.order_id=o.id JOIN photos p ON p.id=i.photo_id WHERE o.download_token=? AND p.id=? AND o.status='paid'");$s->execute([$_GET['token']??'',(int)($_GET['id']??0)]);$p=$s->fetch();$file=$p?ROOT.'/'.$p['original_path']:'';if(!$p||!$p['download_enabled']||!is_file($file)){http_response_code(403);exit('Descarga no autorizada');}header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.basename($file).'"');header('Content-Length: '.filesize($file));readfile($file);exit;}}

