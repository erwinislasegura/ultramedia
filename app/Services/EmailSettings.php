<?php
namespace App\Services;

use App\Core\Database;
use Throwable;

final class EmailSettings
{
    public static function smtp():?array
    {
        try{$row=Database::db()->query('SELECT * FROM email_settings WHERE id=1')->fetch();if(!$row)return null;$row['password']=PaymentSettings::decrypt((string)$row['password_encrypted']);return $row;}catch(Throwable){return null;}
    }
}
