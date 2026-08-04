<?php
namespace App\Core;
final class Router{private array $routes=[];public function get(string $p,string $a):void{$this->routes['GET'][$p]=$a;}public function post(string $p,string $a):void{$this->routes['POST'][$p]=$a;}public function dispatch(string $method,string $uri):void{$base=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));if($base!=='/'&&str_starts_with($uri,$base))$uri=substr($uri,strlen($base));$uri='/'.trim($uri,'/');if($uri==='//')$uri='/';$action=$this->routes[$method][$uri]??null;if(!$action){http_response_code(404);echo'Página no encontrada';return;}[$class,$fn]=explode('@',$action);$class='App\\Controllers\\'.$class;(new $class)->$fn();}}

