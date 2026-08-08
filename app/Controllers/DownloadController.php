<?php
namespace App\Controllers;

use App\Core\Database;
use ZipArchive;

final class DownloadController
{
    public function file():never{$token=(string)($_GET['token']??'');$id=(int)($_GET['id']??0);$s=Database::db()->prepare("SELECT p.original_path,p.download_enabled FROM orders o JOIN order_items i ON i.order_id=o.id JOIN photos p ON p.id=i.photo_id WHERE o.download_token=? AND p.id=? AND i.item_type='photo' AND o.status='paid' AND o.paid_at IS NOT NULL AND o.download_expires_at IS NOT NULL AND NOW()<=o.download_expires_at");$s->execute([$token,$id]);$this->sendPhoto($s->fetch(),$id);}
    public function set():never{$token=(string)($_GET['token']??'');$id=(int)($_GET['id']??0);$s=Database::db()->prepare("SELECT ps.id,ps.name FROM orders o JOIN order_items i ON i.order_id=o.id JOIN photo_sets ps ON ps.id=i.set_id WHERE o.download_token=? AND ps.id=? AND i.item_type='set' AND o.status='paid' AND o.paid_at IS NOT NULL AND o.download_expires_at IS NOT NULL AND NOW()<=o.download_expires_at");$s->execute([$token,$id]);$set=$s->fetch();if(!$set)$this->denied();$photos=$this->setPhotos($id);$this->sendZip($photos,$set['name']);}
    public function pack():never
    {
        $token=(string)($_GET['token']??'');$item=(int)($_GET['item']??0);
        $s=Database::db()->prepare("SELECT i.selected_photo_ids,i.item_title FROM orders o JOIN order_items i ON i.order_id=o.id WHERE o.download_token=? AND i.id=? AND i.item_type='pack' AND o.status='paid' AND o.paid_at IS NOT NULL AND o.download_expires_at IS NOT NULL AND NOW()<=o.download_expires_at");
        $s->execute([$token,$item]);$pack=$s->fetch();if(!$pack)$this->denied();
        $this->sendZip($this->selectedPhotos($pack['selected_photo_ids']),$pack['item_title']?:'pack-ultra');
    }
    public function customerFile():never
    {
        require_customer();$item=(int)($_GET['item']??0);
        $s=Database::db()->prepare("SELECT i.*,o.status,p.original_path,p.download_enabled,ps.name set_name FROM order_items i JOIN orders o ON o.id=i.order_id LEFT JOIN photos p ON p.id=i.photo_id LEFT JOIN photo_sets ps ON ps.id=i.set_id WHERE i.id=? AND o.customer_id=? AND o.status='paid' AND o.paid_at IS NOT NULL AND o.download_expires_at IS NOT NULL AND NOW()<=o.download_expires_at");
        $s->execute([$item,customer_user()['id']]);$i=$s->fetch();if(!$i)$this->denied();
        if($i['item_type']==='set')$this->sendZip($this->setPhotos((int)$i['set_id']),$i['set_name']);
        if($i['item_type']==='pack')$this->sendZip($this->selectedPhotos($i['selected_photo_ids']),$i['item_title']?:'pack-ultra');
        $this->sendPhoto($i,(int)$i['photo_id']);
    }
    private function selectedPhotos(?string $json):array
    {
        $ids=array_values(array_unique(array_filter(array_map('intval',(array)json_decode((string)$json,true)))));
        if(!$ids)$this->denied();$marks=implode(',',array_fill(0,count($ids),'?'));
        $s=Database::db()->prepare("SELECT id,original_path FROM photos WHERE id IN ($marks) AND download_enabled=1");$s->execute($ids);$photos=$s->fetchAll();if(count($photos)!==count($ids))$this->denied();return $photos;
    }
    private function setPhotos(int $setId):array{$s=Database::db()->prepare("SELECT id,original_path FROM photos WHERE set_id=? AND status='active' AND download_enabled=1");$s->execute([$setId]);$photos=$s->fetchAll();if(!$photos)$this->denied();return $photos;}
    private function sendPhoto(array|false $p,int $id):never{$root=realpath(ROOT.'/storage/originals');$file=$p?realpath(ROOT.'/'.ltrim($p['original_path'],'/')):false;if(!$p||!$p['download_enabled']||!$root||!$file||!str_starts_with($file,$root.DIRECTORY_SEPARATOR)||!is_file($file))$this->denied();header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="ultra-fotografia-'.$id.'.'.pathinfo($file,PATHINFO_EXTENSION).'"');header('Content-Length: '.filesize($file));$this->headers();readfile($file);exit;}
    private function sendZip(array $photos,string $name):never
    {
        if(!class_exists(ZipArchive::class)){http_response_code(503);exit('El servidor debe habilitar ZipArchive para descargar packs.');}
        if(!$photos)$this->denied();$tmp=tempnam(sys_get_temp_dir(),'ultra-pack-');$zip=new ZipArchive();if($zip->open($tmp,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true)$this->denied();$root=realpath(ROOT.'/storage/originals');
        foreach($photos as $p){$file=realpath(ROOT.'/'.ltrim($p['original_path'],'/'));if($root&&$file&&str_starts_with($file,$root.DIRECTORY_SEPARATOR))$zip->addFile($file,'foto-'.$p['id'].'.'.pathinfo($file,PATHINFO_EXTENSION));}
        $zip->close();$safe=trim(preg_replace('/[^a-z0-9-]+/i','-',strtolower($name)),'-')?:'pack-ultra';header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="'.$safe.'.zip"');header('Content-Length: '.filesize($tmp));$this->headers();readfile($tmp);@unlink($tmp);exit;
    }
    private function headers():void{header('Cache-Control: private, no-store, no-cache, must-revalidate');header('Pragma: no-cache');header('X-Content-Type-Options: nosniff');}
    private function denied():never{http_response_code(403);exit('Descarga no autorizada');}
}
