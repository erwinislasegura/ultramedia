<?php
declare(strict_types=1);
define('ROOT',dirname(__DIR__,2));
spl_autoload_register(function(string $class):void{$prefix='App\\';if(!str_starts_with($class,$prefix))return;$file=ROOT.'/app/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php';if(is_file($file))require $file;});
function config(?string $key=null):mixed{static $c;$c??=require ROOT.'/config/config.php';return $key?($c[$key]??null):$c;}
function view(string $name,array $data=[]):void{extract($data);$view=ROOT.'/app/Views/'.$name.'.php';require ROOT.'/app/Views/layouts/main.php';}
function url(string $path=''):string{
 if(preg_match('~^https?://~',$path))return $path;
 $configured=rtrim((string)config('app_url'),'/');
 if($configured!=='')$base=$configured;
 else{$https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';$host=$_SERVER['HTTP_HOST']??'localhost';$folder=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/'));$base=($https?'https':'http').'://'.$host.($folder==='/'?'':rtrim($folder,'/'));}
 return rtrim($base,'/').'/'.ltrim($path,'/');
}
function media(string $path):string{return preg_match('~^https?://~',$path)?$path:url($path);}
function redirect(string $path):never{header('Location: '.url($path));exit;}
function money(int $n):string{return '$'.number_format($n,0,',','.');}
function csrf():string{$_SESSION['csrf']??=bin2hex(random_bytes(24));return $_SESSION['csrf'];}
function verify_csrf():void{if(!hash_equals($_SESSION['csrf']??'',$_POST['_token']??'')){http_response_code(419);exit('Sesión expirada');}}
