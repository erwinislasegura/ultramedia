<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\Event;
use Throwable;

final class EventController
{
    public function index(): void
    {
        $events = Event::adminEvents();
        $selected = !empty($_GET['id']) ? Event::findAdmin((int)$_GET['id']) : null;
        $selectedSetIds = $selected ? Event::selectedSetIds((int)$selected['id']) : [];
        admin_view('admin/events', [
            'events' => $events,
            'selected' => $selected,
            'sets' => Event::allSets(),
            'selectedSetIds' => $selectedSetIds,
            'pageTitle' => 'Eventos',
            'adminSection' => 'events',
        ]);
    }

    public function save(): never
    {
        verify_csrf();
        $db = Database::db();
        $id = (int)($_POST['id'] ?? 0);
        $current = $id ? Event::findAdmin($id) : null;
        $name = trim($_POST['name'] ?? '');
        $sport = trim($_POST['sport'] ?? '');
        $date = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';
        $setIds = (array)($_POST['set_ids'] ?? []);

        if ($name === '' || $sport === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['error'] = 'Completa nombre, disciplina y fecha.';
            redirect('/admin/eventos'.($id ? '?id='.$id : ''));
        }
        if (!Event::ensureModule()) {
            $_SESSION['error'] = 'Primero importa database/update_events_module.sql en la base de datos.';
            redirect('/admin/eventos'.($id ? '?id='.$id : ''));
        }

        $coverPath = $current['cover_path'] ?? null;
        $oldCover = $coverPath;
        if (!empty($_POST['remove_cover'])) {
            $coverPath = null;
        }
        $newUploadedPath = null;
        $cover = $_FILES['cover'] ?? null;
        if ($cover && ($cover['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($cover['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($cover['tmp_name']) || ($cover['size'] ?? 0) > 12 * 1024 * 1024) {
                $_SESSION['error'] = 'La portada no pudo cargarse o supera los 12 MB.';
                redirect('/admin/eventos'.($id ? '?id='.$id : ''));
            }
            $imageInfo = @getimagesize($cover['tmp_name']);
            $imageType = $imageInfo[2] ?? null;
            $extensions = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
            if (!isset($extensions[$imageType])) {
                $_SESSION['error'] = 'La portada debe ser JPG, PNG o WEBP.';
                redirect('/admin/eventos'.($id ? '?id='.$id : ''));
            }
            $directory = ROOT.'/uploads/events';
            if (!is_dir($directory) && !@mkdir($directory, 0775, true)) {
                $_SESSION['error'] = 'No fue posible crear el directorio para portadas.';
                redirect('/admin/eventos'.($id ? '?id='.$id : ''));
            }
            $newUploadedPath = 'uploads/events/'.bin2hex(random_bytes(12)).'.'.$extensions[$imageType];
            if (!@move_uploaded_file($cover['tmp_name'], ROOT.'/'.$newUploadedPath)) {
                $_SESSION['error'] = 'No fue posible guardar la portada del evento.';
                redirect('/admin/eventos'.($id ? '?id='.$id : ''));
            }
            $coverPath = $newUploadedPath;
        }

        $slugBase = iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name;
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $slugBase), '-'));
        if ($slug === '') {
            $slug = 'evento';
        }
        $check = $db->prepare('SELECT id FROM events WHERE slug=? AND id<>?');
        $check->execute([$slug, $id]);
        if ($check->fetch()) {
            $slug .= '-'.substr(md5($name.$date.($id ?: microtime(true))), 0, 6);
        }

        try {
            $db->beginTransaction();
            if ($id) {
                if (!$current) {
                    throw new \RuntimeException('Evento no encontrado.');
                }
                $db->prepare('UPDATE events SET name=?,slug=?,sport=?,event_date=?,location=?,status=? WHERE id=?')
                    ->execute([$name, $slug, $sport, $date, $location, $status, $id]);
            } else {
                $db->prepare('INSERT INTO events(name,slug,sport,event_date,location,status) VALUES(?,?,?,?,?,?)')
                    ->execute([$name, $slug, $sport, $date, $location, $status]);
                $id = (int)$db->lastInsertId();
            }
            Event::saveComposition($id, $description, $coverPath, $setIds);
            $db->commit();

            if ($oldCover && $oldCover !== $coverPath && !preg_match('~^https?://~', $oldCover)) {
                $oldFile = ROOT.'/'.ltrim($oldCover, '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $_SESSION['success'] = 'Evento guardado con '.count(array_unique(array_map('intval', $setIds))).' set(s) seleccionado(s).';
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($newUploadedPath && is_file(ROOT.'/'.$newUploadedPath)) {
                @unlink(ROOT.'/'.$newUploadedPath);
            }
            error_log('Event save: '.$e->getMessage());
            $_SESSION['error'] = $e instanceof \RuntimeException ? $e->getMessage() : 'No fue posible guardar el evento.';
        }
        redirect('/admin/eventos?id='.$id);
    }

    public function status(): never
    {
        verify_csrf();
        $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';
        Database::db()->prepare('UPDATE events SET status=? WHERE id=?')->execute([$status, (int)$_POST['id']]);
        $_SESSION['success'] = 'Estado del evento actualizado.';
        redirect('/admin/eventos');
    }

    public function delete(): never
    {
        verify_csrf();
        $db = Database::db();
        $id = (int)$_POST['id'];
        $event = Event::findAdmin($id);
        $statement = $db->prepare('SELECT (SELECT COUNT(*) FROM photos WHERE event_id=?)+(SELECT COUNT(*) FROM photo_sets WHERE event_id=?)');
        $statement->execute([$id, $id]);
        if ((int)$statement->fetchColumn() > 0) {
            $db->prepare("UPDATE events SET status='archived' WHERE id=?")->execute([$id]);
            $_SESSION['success'] = 'El evento tiene sets o fotografías y fue archivado para conservar su historial.';
        } else {
            $db->prepare('DELETE FROM events WHERE id=?')->execute([$id]);
            if (!empty($event['cover_path']) && !preg_match('~^https?://~', $event['cover_path'])) {
                $file = ROOT.'/'.ltrim($event['cover_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $_SESSION['success'] = 'Evento eliminado.';
        }
        redirect('/admin/eventos');
    }
}
