<?php
namespace App\Models;
use App\Core\Database;
final class User{
 public static function all():array{return Database::db()->query("SELECT u.id,u.role_id,u.name,u.email,u.status,u.last_login_at,u.created_at,r.name role_name,r.slug role_slug FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC")->fetchAll();}
 public static function roles():array{return Database::db()->query("SELECT r.*,COUNT(u.id) users_count FROM roles r LEFT JOIN users u ON u.role_id=r.id GROUP BY r.id ORDER BY r.name")->fetchAll();}
 public static function save(array $data):void{$db=Database::db();$id=(int)($data['id']??0);if($id){$sql='UPDATE users SET name=?,email=?,role_id=?,status=?';$args=[$data['name'],$data['email'],(int)$data['role_id'],$data['status']];if(!empty($data['password'])){$sql.=',password_hash=?';$args[]=password_hash($data['password'],PASSWORD_DEFAULT);}$sql.=' WHERE id=?';$args[]=$id;$db->prepare($sql)->execute($args);return;}$db->prepare('INSERT INTO users(role_id,name,email,password_hash,status) VALUES(?,?,?,?,?)')->execute([(int)$data['role_id'],$data['name'],$data['email'],password_hash($data['password'],PASSWORD_DEFAULT),$data['status']]);}
 public static function delete(int $id):void{Database::db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);}
 public static function saveRole(array $data):void{$permissions=json_encode(array_values($data['permissions']??[]),JSON_UNESCAPED_UNICODE);$id=(int)($data['id']??0);if($id){Database::db()->prepare('UPDATE roles SET name=?,permissions=? WHERE id=? AND is_system=0')->execute([$data['name'],$permissions,$id]);return;}$slug=strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$data['name']),'-'));Database::db()->prepare('INSERT INTO roles(name,slug,permissions) VALUES(?,?,?)')->execute([$data['name'],$slug,$permissions]);}
}
