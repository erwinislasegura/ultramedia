<?php
namespace App\Controllers;

use App\Core\Database;

final class HeroController
{
    public function index(): void
    {
        $hero = Database::db()->query('SELECT * FROM homepage_hero WHERE id=1')->fetch() ?: [];
        admin_view('admin/hero', [
            'hero' => $hero,
            'pageTitle' => 'Hero de portada',
            'adminSection' => 'hero',
        ]);
    }

    public function save(): never
    {
        verify_csrf();
        $title = trim($_POST['title'] ?? '');
        $highlight = trim($_POST['highlight'] ?? '');
        $background = trim($_POST['background_url'] ?? '');
        $upload = $_FILES['background_image'] ?? null;
        if ($upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name']) || !@getimagesize($upload['tmp_name'])) {
                $_SESSION['error'] = 'La imagen seleccionada no es válida.';
                redirect('/admin/hero');
            }
            $ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $_SESSION['error'] = 'La imagen debe estar en formato JPG, PNG o WebP.';
                redirect('/admin/hero');
            }
            $directory = ROOT.'/storage/hero';
            if ((!is_dir($directory) && !@mkdir($directory, 0775, true)) || !is_writable($directory)) {
                $_SESSION['error'] = 'No fue posible preparar la carpeta para la imagen del hero.';
                redirect('/admin/hero');
            }
            $background = 'storage/hero/'.bin2hex(random_bytes(10)).'.'.$ext;
            if (!@move_uploaded_file($upload['tmp_name'], ROOT.'/'.$background)) {
                $_SESSION['error'] = 'No fue posible guardar la imagen del hero.';
                redirect('/admin/hero');
            }
        }
        if ($title === '' || $highlight === '' || $background === '') {
            $_SESSION['error'] = 'El título, el texto destacado y la imagen de fondo son obligatorios.';
            redirect('/admin/hero');
        }
        if (!filter_var($background, FILTER_VALIDATE_URL) && !preg_match('~^[a-zA-Z0-9/_\-.]+$~', $background)) {
            $_SESSION['error'] = 'Ingresa una URL o ruta de imagen válida.';
            redirect('/admin/hero');
        }
        $positions = ['center center', 'center top', 'center bottom', 'left center', 'right center'];
        $position = in_array($_POST['background_position'] ?? '', $positions, true) ? $_POST['background_position'] : 'center center';
        $opacity = max(20, min(95, (int)($_POST['overlay_opacity'] ?? 75)));
        $sql = "INSERT INTO homepage_hero(id,eyebrow,title,highlight,description,search_placeholder,button_text,background_url,background_position,overlay_opacity,trust_one,trust_two,trust_three) VALUES(1,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE eyebrow=VALUES(eyebrow),title=VALUES(title),highlight=VALUES(highlight),description=VALUES(description),search_placeholder=VALUES(search_placeholder),button_text=VALUES(button_text),background_url=VALUES(background_url),background_position=VALUES(background_position),overlay_opacity=VALUES(overlay_opacity),trust_one=VALUES(trust_one),trust_two=VALUES(trust_two),trust_three=VALUES(trust_three)";
        Database::db()->prepare($sql)->execute([
            trim($_POST['eyebrow'] ?? ''), $title, $highlight,
            trim($_POST['description'] ?? ''), trim($_POST['search_placeholder'] ?? ''),
            trim($_POST['button_text'] ?? ''), $background, $position, $opacity,
            trim($_POST['trust_one'] ?? ''), trim($_POST['trust_two'] ?? ''), trim($_POST['trust_three'] ?? ''),
        ]);
        $_SESSION['success'] = 'Hero actualizado correctamente.';
        redirect('/admin/hero');
    }
}
