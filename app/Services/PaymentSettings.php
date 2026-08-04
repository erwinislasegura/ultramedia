<?php
namespace App\Services;
use App\Core\Database;use Throwable;
final class PaymentSettings{
 public static function flow():?array{try{$s=Database::db()->query("SELECT * FROM payment_settings WHERE provider='flow' LIMIT 1");$row=$s->fetch();if(!$row)return null;$row['secret_key']=self::decrypt((string)$row['secret_key_encrypted']);return $row;}catch(Throwable){return null;}}
 public static function encrypt(string $plain):string{if($plain==='')return '';$iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($plain,'aes-256-gcm',self::key(),OPENSSL_RAW_DATA,$iv,$tag);if($cipher===false)throw new \RuntimeException('No fue posible proteger la clave secreta.');return base64_encode($iv.$tag.$cipher);}
 private static function decrypt(string $encoded):string{$raw=base64_decode($encoded,true);if($raw===false||strlen($raw)<29)return '';$plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',self::key(),OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16));return $plain===false?'':$plain;}
 private static function key():string{$appKey=(string)(getenv('APP_KEY')?:'');if($appKey===''){$db=(array)config('db');$appKey=implode('|',[$db['host']??'',$db['name']??'',$db['user']??'',$db['pass']??'']);}return hash('sha256',$appKey,true);}
}
