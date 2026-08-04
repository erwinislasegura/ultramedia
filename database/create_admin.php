<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit("Solo disponible desde consola.\n");}
require dirname(__DIR__).'/app/Core/bootstrap.php';
use App\Core\Database;
$email=trim((string)(getenv('ADMIN_EMAIL')?:'admin@ultramedia.cl'));
$name=trim((string)(getenv('ADMIN_NAME')?:'Administrador Ultra'));
$password=(string)(getenv('ADMIN_PASSWORD')?:'');
if($password===''){fwrite(STDOUT,"Nueva contraseña para {$email} (mínimo 8 caracteres): ");$password=trim((string)fgets(STDIN));}
if(strlen($password)<8){fwrite(STDERR,"La contraseña debe tener al menos 8 caracteres.\n");exit(1);}
$db=Database::db();
$role=$db->query("SELECT id FROM roles WHERE slug='administrador' LIMIT 1")->fetchColumn();
if(!$role){fwrite(STDERR,"Primero importa database/update_usuarios_roles.sql\n");exit(1);}
$hash=password_hash($password,PASSWORD_DEFAULT);
$s=$db->prepare("INSERT INTO users(role_id,name,email,password_hash,status) VALUES(?,?,?,?, 'active') ON DUPLICATE KEY UPDATE role_id=VALUES(role_id),name=VALUES(name),password_hash=VALUES(password_hash),status='active'");
$s->execute([(int)$role,$name,$email,$hash]);
fwrite(STDOUT,"Administrador creado o actualizado correctamente: {$email}\n");

