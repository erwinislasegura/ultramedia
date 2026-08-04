<?php
namespace App\Models;use App\Core\Database;
final class Customer{
 public static function authenticate(string $email,string $password):?array{$s=Database::db()->prepare("SELECT * FROM customers WHERE email=? AND status='active'");$s->execute([strtolower($email)]);$c=$s->fetch();if(!$c||!password_verify($password,$c['password_hash']))return null;Database::db()->prepare('UPDATE customers SET last_login_at=NOW() WHERE id=?')->execute([$c['id']]);unset($c['password_hash']);return $c;}
 public static function email(string $email):?array{$s=Database::db()->prepare('SELECT * FROM customers WHERE email=?');$s->execute([strtolower($email)]);return $s->fetch()?:null;}
 public static function create(array $d):array{$db=Database::db();$db->prepare("INSERT INTO customers(name,email,password_hash,phone,rut) VALUES(?,?,?,?,?)")->execute([$d['name'],strtolower($d['email']),password_hash($d['password'],PASSWORD_DEFAULT),$d['phone']??'',$d['rut']??'']);$id=(int)$db->lastInsertId();$s=$db->prepare('SELECT * FROM customers WHERE id=?');$s->execute([$id]);$c=$s->fetch();unset($c['password_hash']);return $c;}
}
