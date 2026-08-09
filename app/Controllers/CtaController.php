<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use RuntimeException;
use Throwable;

final class CtaController
{
    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

    public function index(): void
    {
        $db = Database::db();
        $cta = $db->query('SELECT * FROM homepage_cta WHERE id=1')->fetch() ?: [];
        $events = $db->query('SELECT id,name,event_date FROM events ORDER BY event_date DESC')->fetchAll();

        admin_view('admin/cta', [
            'cta' => $cta,
            'events' => $events,
            'pageTitle' => 'CTA de portada',
            'adminSection' => 'cta',
        ]);
    }

    public function save(): never
    {
        verify_csrf();
        $db = Database::db();
        $current = $db->query('SELECT image_url FROM homepage_cta WHERE id=1')->fetch();
        $currentImage = trim((string)($current['image_url'] ?? ''));
        $newImage = null;

        try {
            [$imagePath, $newImage] = $this->storeImage($_FILES['cta_image'] ?? [], $currentImage);

            $statement = $db->prepare(
                'INSERT INTO homepage_cta(id,event_id,eyebrow,title,description,button_text,button_url,image_url,active)
                 VALUES(1,?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                    event_id=VALUES(event_id),eyebrow=VALUES(eyebrow),title=VALUES(title),
                    description=VALUES(description),button_text=VALUES(button_text),
                    button_url=VALUES(button_url),image_url=VALUES(image_url),active=VALUES(active)'
            );
            $statement->execute([
                (int)($_POST['event_id'] ?? 0) ?: null,
                trim((string)($_POST['eyebrow'] ?? '')),
                trim((string)($_POST['title'] ?? '')),
                trim((string)($_POST['description'] ?? '')),
                trim((string)($_POST['button_text'] ?? '')),
                trim((string)($_POST['button_url'] ?? '')),
                $imagePath,
                isset($_POST['active']) ? 1 : 0,
            ]);

            if ($newImage !== null && $currentImage !== $newImage) {
                $this->removeManagedImage($currentImage);
            }

            $_SESSION['success'] = 'CTA actualizado correctamente.';
        } catch (RuntimeException $exception) {
            if ($newImage !== null) {
                $this->removeManagedImage($newImage);
            }
            $_SESSION['error'] = $exception->getMessage();
        } catch (Throwable) {
            if ($newImage !== null) {
                $this->removeManagedImage($newImage);
            }
            $_SESSION['error'] = 'No fue posible guardar el CTA. Inténtalo nuevamente.';
        }

        redirect('/admin/cta');
    }

    /** @return array{0:string,1:?string} */
    private function storeImage(array $file, string $currentImage): array
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return [$currentImage, null];
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No fue posible subir la imagen. Revisa su tamaño e inténtalo nuevamente.');
        }

        $size = (int)($file['size'] ?? 0);
        $temporaryPath = (string)($file['tmp_name'] ?? '');
        if ($size < 1 || $size > self::MAX_IMAGE_BYTES || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('La imagen debe pesar menos de 10 MB.');
        }

        $imageInfo = @getimagesize($temporaryPath);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $mime = is_array($imageInfo) ? (string)($imageInfo['mime'] ?? '') : '';
        if (!isset($extensions[$mime])) {
            throw new RuntimeException('Formato no permitido. Usa una imagen JPG, PNG o WebP.');
        }

        $directory = ROOT . '/uploads/cta';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('No fue posible preparar la carpeta para guardar la imagen.');
        }

        $relativePath = 'uploads/cta/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($temporaryPath, ROOT . '/' . $relativePath)) {
            throw new RuntimeException('No fue posible guardar la imagen en el servidor.');
        }

        return [$relativePath, $relativePath];
    }

    private function removeManagedImage(string $path): void
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if (!str_starts_with($normalized, 'uploads/cta/')) {
            return;
        }

        $filename = basename($normalized);
        $file = ROOT . '/uploads/cta/' . $filename;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
