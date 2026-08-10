<?php
namespace App\Controllers;
use App\Core\Database;
use App\Models\PhotoPack;
use App\Models\PhotoSet;
final class AdminController
{
    public function dashboard(): void
    {
        $db = Database::db();
        $sales = (int) $db
            ->query(
                "SELECT COALESCE(SUM(total),0) FROM orders WHERE status='paid'",
            )
            ->fetchColumn();
        $orders = (int) $db
            ->query("SELECT COUNT(*) FROM orders WHERE status='paid'")
            ->fetchColumn();
        $stats = [
            "sales" => $sales,
            "orders" => $orders,
            "pending" => (int) $db
                ->query("SELECT COUNT(*) FROM orders WHERE status='pending'")
                ->fetchColumn(),
            "sold" => (int) $db
                ->query(
                    "SELECT COUNT(*) FROM order_items i JOIN orders o ON o.id=i.order_id WHERE o.status='paid'",
                )
                ->fetchColumn(),
            "photos" => (int) $db
                ->query("SELECT COUNT(*) FROM photos")
                ->fetchColumn(),
            "storage" => (int) $db
                ->query("SELECT COALESCE(SUM(file_size),0) FROM photos")
                ->fetchColumn(),
            "average" => $orders ? (int) round($sales / $orders) : 0,
        ];
        $daily = $db
            ->query(
                "SELECT DATE(paid_at) day,SUM(total) total FROM orders WHERE paid_at>=DATE_SUB(CURDATE(),INTERVAL 13 DAY) AND status='paid' GROUP BY DATE(paid_at) ORDER BY day",
            )
            ->fetchAll();
        $topEvents = $db
            ->query(
                "SELECT e.name,e.sport,e.event_date,COALESCE(SUM(i.unit_price),0) sales,COUNT(DISTINCT o.id) orders_count,MIN(p.id) photo_id,MIN(p.preview_path) preview_path FROM events e JOIN photos p ON p.event_id=e.id LEFT JOIN order_items i ON i.photo_id=p.id LEFT JOIN orders o ON o.id=i.order_id AND o.status='paid' GROUP BY e.id ORDER BY sales DESC LIMIT 3",
            )
            ->fetchAll();
        $recent = $db
            ->query(
                "SELECT o.*,COUNT(i.id) items FROM orders o LEFT JOIN order_items i ON i.order_id=o.id GROUP BY o.id ORDER BY o.id DESC LIMIT 6",
            )
            ->fetchAll();
        admin_view("admin/dashboard", [
            "stats" => $stats,
            "daily" => $daily,
            "topEvents" => $topEvents,
            "recent" => $recent,
            "pageTitle" => "Resumen",
            "adminSection" => "dashboard",
        ]);
    }
    public function photos(): void
    {
        $db = Database::db();
        $this->ensureWatermarkControls();
        PhotoSet::ensureFeaturedHomeColumn();
        $events = $db
            ->query("SELECT * FROM events ORDER BY event_date DESC")
            ->fetchAll();
        $coverExpr = "MIN(p.id)";
        try {
            $db->query("SELECT cover_photo_id FROM photo_sets LIMIT 0");
            $coverExpr =
                "COALESCE(MAX(CASE WHEN p.id=ps.cover_photo_id THEN p.id END),MIN(p.id))";
        } catch (\Throwable $e) {
        }
        $sets = $db
            ->query(
                "SELECT ps.*,e.name event_name,COUNT(p.id) photos_count,SUM(p.status='active') published_count,$coverExpr cover_id,MIN(p.price) individual_price,MAX(p.download_enabled) download_enabled FROM photo_sets ps JOIN events e ON e.id=ps.event_id LEFT JOIN photos p ON p.set_id=ps.id GROUP BY ps.id HAVING photos_count>0 ORDER BY ps.id DESC LIMIT 100",
            )
            ->fetchAll();
        admin_view("admin/photos", [
            "events" => $events,
            "sets" => $sets,
            "pageTitle" => "Subir fotografías",
            "adminSection" => "photos",
        ]);
    }
    public function upload(): never
    {
        verify_csrf();
        $this->ensureWatermarkControls();
        $event = (int) ($_POST["event_id"] ?? 0);
        $watermark = isset($_POST["watermark"]);
        $wmType =
            ($_POST["watermark_type"] ?? "text") === "image" ? "image" : "text";
        $wmText = trim($_POST["watermark_text"] ?? "ULTRA MEDIA DIGITAL");
        $wmScale = $this->rangeValue(
            $_POST["watermark_scale"] ?? 90,
            20,
            100,
            90,
        );
        $wmOpacity = $this->rangeValue(
            $_POST["watermark_opacity"] ?? 65,
            10,
            100,
            65,
        );
        $wmImage = null;
        if (
            $watermark &&
            $wmType === "image" &&
            isset($_FILES["watermark_image"]) &&
            $_FILES["watermark_image"]["error"] === UPLOAD_ERR_OK &&
            $_FILES["watermark_image"]["tmp_name"] &&
            @getimagesize($_FILES["watermark_image"]["tmp_name"])
        ) {
            $wmImage = $_FILES["watermark_image"]["tmp_name"];
        }
        if ($watermark && $wmType === "image" && !$wmImage) {
            $_SESSION["error"] =
                "Selecciona una imagen válida para usar como marca de agua.";
            redirect("/admin/fotos");
        }
        $files = $_FILES["photos"] ?? null;
        if (!$event || !$files) {
            $_SESSION["error"] =
                "Selecciona un evento y al menos una fotografía.";
            redirect("/admin/fotos");
        }
        $individual = isset($_POST["individual_enabled"]);
        $setEnabled = isset($_POST["set_enabled"]);
        $featuredHome = $setEnabled && isset($_POST["featured_home"]);
        $packOptions = PhotoPack::fromPost();
        $activePacks = array_values(
            array_filter($packOptions, fn($o) => !empty($o["active"])),
        );
        $packEnabled = !empty($activePacks);
        $packQuantity = (int) ($activePacks[0]["quantity"] ?? 5);
        $packPrice = (int) ($activePacks[0]["price"] ?? 14990);
        $packCounts = [];
        foreach ($activePacks as $option) {
            if (
                $option["quantity"] >
                    count((array) ($files["tmp_name"] ?? [])) ||
                in_array($option["quantity"], $packCounts, true)
            ) {
                $_SESSION["error"] =
                    "Cada pack debe tener una cantidad distinta y no superar las fotografías cargadas.";
                redirect("/admin/fotos");
            }
            $packCounts[] = (int) $option["quantity"];
        }
        if (!$individual && !$setEnabled && !$packEnabled) {
            $_SESSION["error"] =
                "Habilita la venta individual, el set completo o el pack de fotografías.";
            redirect("/admin/fotos");
        }
        $setName = trim($_POST["set_name"] ?? "");
        if ($setName === "") {
            $setName = "Set " . date("d-m-Y H:i");
        }
        $db = Database::db();
        $featuredReady = PhotoSet::ensureFeaturedHomeColumn();
        if ($featuredReady) {
            $set = $db->prepare(
                "INSERT INTO photo_sets(event_id,name,bib_number,individual_enabled,set_enabled,featured_home,pack_enabled,pack_quantity,pack_price,set_price) VALUES(?,?,?,?,?,?,?,?,?,?)",
            );
            $set->execute([
                $event,
                $setName,
                trim($_POST["bib_number"] ?? ""),
                $individual ? 1 : 0,
                $setEnabled ? 1 : 0,
                $featuredHome ? 1 : 0,
                $packEnabled ? 1 : 0,
                $packQuantity,
                $packPrice,
                max(0, (int) ($_POST["set_price"] ?? 19990)),
            ]);
        } else {
            $set = $db->prepare(
                "INSERT INTO photo_sets(event_id,name,bib_number,individual_enabled,set_enabled,pack_enabled,pack_quantity,pack_price,set_price) VALUES(?,?,?,?,?,?,?,?,?)",
            );
            $set->execute([
                $event,
                $setName,
                trim($_POST["bib_number"] ?? ""),
                $individual ? 1 : 0,
                $setEnabled ? 1 : 0,
                $packEnabled ? 1 : 0,
                $packQuantity,
                $packPrice,
                max(0, (int) ($_POST["set_price"] ?? 19990)),
            ]);
        }
        $setId = (int) $db->lastInsertId();
        PhotoPack::saveOptions($setId, $packOptions);
        $dirs = [ROOT . "/storage/originals", ROOT . "/storage/previews"];
        foreach ($dirs as $dir) {
            if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
                $_SESSION["error"] = "No fue posible crear la carpeta: " . $dir;
                redirect("/admin/fotos");
            }
            @chmod($dir, 0775);
            if (!is_writable($dir)) {
                $_SESSION["error"] =
                    "La carpeta storage no tiene permisos de escritura.";
                redirect("/admin/fotos");
            }
        }
        $uploaded = 0;
        $failed = [];
        foreach ($files["tmp_name"] as $k => $tmp) {
            $name = $files["name"][$k] ?? "archivo";
            if (($files["error"][$k] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $failed[] = $name . " (error de carga)";
                continue;
            }
            if (!is_uploaded_file($tmp) || !@getimagesize($tmp)) {
                $failed[] = $name . " (imagen inválida)";
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"], true)) {
                $failed[] = $name . " (formato no permitido)";
                continue;
            }
            $token = bin2hex(random_bytes(10));
            $orig = "storage/originals/" . $token . "." . $ext;
            $preview = "storage/previews/" . $token . ".jpg";
            $origAbs = ROOT . "/" . $orig;
            $previewAbs = ROOT . "/" . $preview;
            if (!@move_uploaded_file($tmp, $origAbs) || !is_file($origAbs)) {
                $failed[] = $name . " (sin permiso para guardar)";
                continue;
            }
            if (
                !$this->preview(
                    $origAbs,
                    $previewAbs,
                    $watermark,
                    $wmText,
                    $wmImage,
                    $wmScale,
                    $wmOpacity,
                )
            ) {
                @unlink($origAbs);
                $failed[] = $name . " (no se pudo generar la vista previa)";
                continue;
            }
            $size = (int) @filesize($origAbs);
            $s = Database::db()->prepare(
                "INSERT INTO photos(event_id,set_id,title,bib_number,price,original_path,preview_path,file_size,watermark_enabled,watermark_type,watermark_text,watermark_scale,watermark_opacity,download_enabled,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            );
            $s->execute([
                $event,
                $setId,
                pathinfo($name, PATHINFO_FILENAME),
                trim($_POST["bib_number"] ?? ""),
                max(0, (int) ($_POST["price"] ?? 4990)),
                $orig,
                $preview,
                $size,
                $watermark ? 1 : 0,
                $wmType,
                $wmText,
                $wmScale,
                $wmOpacity,
                isset($_POST["download_enabled"]) ? 1 : 0,
                "active",
            ]);
            $uploaded++;
        }
        if ($uploaded) {
            $_SESSION["success"] =
                $uploaded . " fotografía(s) publicada(s) correctamente.";
        }
        if ($failed) {
            $_SESSION["error"] = "No se procesaron: " . implode(", ", $failed);
        }
        redirect("/admin/fotos");
    }
    public function toggleDownload(): never
    {
        verify_csrf();
        [$id, $enabled] = array_pad(
            explode(":", $_POST["toggle"] ?? "0:0"),
            2,
            0,
        );
        $s = Database::db()->prepare(
            "UPDATE photos SET download_enabled=? WHERE id=?",
        );
        $s->execute([(int) $enabled, (int) $id]);
        redirect("/admin/fotos");
    }
    public function editPhoto(): void
    {
        $db = Database::db();
        $this->ensureWatermarkControls();
        $featuredSelect = PhotoSet::ensureFeaturedHomeColumn()
            ? "COALESCE(ps.featured_home,0) featured_home"
            : "0 featured_home";
        $coverSelect = "NULL cover_photo_id";
        try {
            $db->query("SELECT cover_photo_id FROM photo_sets LIMIT 0");
            $coverSelect = "ps.cover_photo_id";
        } catch (\Throwable $e) {
        }
        $s = $db->prepare(
            "SELECT p.*,e.name event_name,COALESCE(ps.pack_enabled,0) pack_enabled,ps.pack_quantity,ps.pack_price,COALESCE(ps.status,'active') set_status,ps.name set_name,ps.bib_number set_bib_number,$coverSelect,$featuredSelect,COALESCE(ps.individual_enabled,1) individual_enabled,COALESCE(ps.set_enabled,1) set_enabled,ps.set_price FROM photos p JOIN events e ON e.id=p.event_id LEFT JOIN photo_sets ps ON ps.id=p.set_id WHERE p.id=?",
        );
        $s->execute([(int) ($_GET["id"] ?? 0)]);
        $photo = $s->fetch();
        if (!$photo) {
            http_response_code(404);
            exit("Fotografía no encontrada");
        }
        $events = $db
            ->query("SELECT * FROM events ORDER BY event_date DESC")
            ->fetchAll();
        $setPhotos = [];
        if (!empty($photo["set_id"])) {
            $g = $db->prepare(
                "SELECT p.*,EXISTS(SELECT 1 FROM order_items oi WHERE oi.photo_id=p.id) has_sales FROM photos p WHERE p.set_id=? ORDER BY p.id",
            );
            $g->execute([(int) $photo["set_id"]]);
            $setPhotos = $g->fetchAll();
        }
        admin_view("admin/photo-edit", [
            "photo" => $photo,
            "setPhotos" => $setPhotos,
            "events" => $events,
            "pageTitle" => "Editar fotografía",
            "adminSection" => "photos",
        ]);
    }
    public function updatePhoto(): never
    {
        verify_csrf();
        $db = Database::db();
        $this->ensureWatermarkControls();
        $featuredReady = PhotoSet::ensureFeaturedHomeColumn();
        $id = (int) ($_POST["id"] ?? 0);
        $find = $db->prepare("SELECT * FROM photos WHERE id=?");
        $find->execute([$id]);
        $photo = $find->fetch();
        if (!$photo) {
            $_SESSION["error"] = "Fotografía no encontrada.";
            redirect("/admin/fotos");
        }
        $status = $photo["status"];
        $setStatus =
            ($_POST["set_status"] ?? "active") === "hidden"
                ? "hidden"
                : "active";
        $event = (int) ($_POST["event_id"] ?? $photo["event_id"]);
        $setName = trim($_POST["set_name"] ?? "");
        $bib = trim($_POST["bib_number"] ?? "");
        $price = max(0, (int) ($_POST["price"] ?? $photo["price"]));
        $individual = isset($_POST["individual_enabled"]);
        $setEnabled = isset($_POST["set_enabled"]);
        $featuredHome = $setEnabled && isset($_POST["featured_home"]);
        $setPrice = max(0, (int) ($_POST["set_price"] ?? 19990));
        $download = isset($_POST["download_enabled"]);
        $coverPhotoId = (int) ($_POST["cover_photo_id"] ?? 0);
        $removePhotoIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        "intval",
                        (array) ($_POST["remove_photo_ids"] ?? []),
                    ),
                ),
            ),
        );
        $watermark = isset($_POST["watermark_enabled"]);
        $wmType =
            ($_POST["watermark_type"] ?? "text") === "image" ? "image" : "text";
        $wmText = trim($_POST["watermark_text"] ?? "ULTRA MEDIA DIGITAL");
        $wmScale = $this->rangeValue(
            $_POST["watermark_scale"] ?? ($photo["watermark_scale"] ?? 90),
            20,
            100,
            90,
        );
        $wmOpacity = $this->rangeValue(
            $_POST["watermark_opacity"] ??
                ($photo["watermark_opacity"] ?? 65),
            10,
            100,
            65,
        );
        $wmLogo = null;
        $new = $_FILES["photo"] ?? null;
        $hasNew =
            $new &&
            ($new["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        $additional = $_FILES["additional_photos"] ?? null;
        $hasAdditional =
            $additional &&
            !empty(array_filter((array) ($additional["name"] ?? [])));
        $watermarkChanged =
            (int) $photo["watermark_enabled"] !== ($watermark ? 1 : 0) ||
            ($photo["watermark_type"] ?? "text") !== $wmType ||
            ($photo["watermark_text"] ?? "") !== $wmText ||
            (int) ($photo["watermark_scale"] ?? 90) !== $wmScale ||
            (int) ($photo["watermark_opacity"] ?? 65) !== $wmOpacity;
        $previewWarning = "";
        if (
            $watermark &&
            $wmType === "image" &&
            isset($_FILES["watermark_image"]) &&
            ($_FILES["watermark_image"]["error"] ?? UPLOAD_ERR_NO_FILE) ===
                UPLOAD_ERR_OK &&
            @getimagesize($_FILES["watermark_image"]["tmp_name"])
        ) {
            $wmLogo = $_FILES["watermark_image"]["tmp_name"];
        }
        if (
            $watermark &&
            $wmType === "image" &&
            !$wmLogo &&
            ($hasNew || $hasAdditional || $watermarkChanged)
        ) {
            $_SESSION["error"] =
                "Selecciona nuevamente la imagen que usarás como marca de agua.";
            redirect("/admin/fotos/editar?id=" . $id);
        }
        $original = $photo["original_path"];
        $preview = $photo["preview_path"];
        $size = (int) $photo["file_size"];
        if ($hasNew) {
            if (
                $new["error"] !== UPLOAD_ERR_OK ||
                !is_uploaded_file($new["tmp_name"]) ||
                !@getimagesize($new["tmp_name"])
            ) {
                $_SESSION["error"] =
                    "El archivo de reemplazo no es una imagen válida.";
                redirect("/admin/fotos/editar?id=" . $id);
            }
            $ext = strtolower(pathinfo($new["name"], PATHINFO_EXTENSION));
            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"], true)) {
                $_SESSION["error"] =
                    "El formato de reemplazo no está permitido.";
                redirect("/admin/fotos/editar?id=" . $id);
            }
            $token = bin2hex(random_bytes(10));
            $newOriginal = "storage/originals/" . $token . "." . $ext;
            $newPreview = "storage/previews/" . $token . ".jpg";
            if (
                !@move_uploaded_file(
                    $new["tmp_name"],
                    ROOT . "/" . $newOriginal,
                ) ||
                !$this->preview(
                    ROOT . "/" . $newOriginal,
                    ROOT . "/" . $newPreview,
                    $watermark,
                    $wmText,
                    $wmLogo,
                    $wmScale,
                    $wmOpacity,
                )
            ) {
                @unlink(ROOT . "/" . $newOriginal);
                $_SESSION["error"] =
                    "No fue posible guardar o procesar la nueva imagen.";
                redirect("/admin/fotos/editar?id=" . $id);
            }
            $oldOriginal = ROOT . "/" . ltrim($original, "/");
            $oldPreview = ROOT . "/" . ltrim($preview, "/");
            $original = $newOriginal;
            $preview = $newPreview;
            $size = (int) @filesize(ROOT . "/" . $newOriginal);
            if (is_file($oldOriginal)) {
                @unlink($oldOriginal);
            }
            if (is_file($oldPreview)) {
                @unlink($oldPreview);
            }
        } elseif ($watermarkChanged) {
            if (
                !$this->preview(
                    ROOT . "/" . ltrim($original, "/"),
                    ROOT . "/" . ltrim($preview, "/"),
                    $watermark,
                    $wmText,
                    $wmLogo,
                    $wmScale,
                    $wmOpacity,
                )
            ) {
                $watermark = (bool) $photo["watermark_enabled"];
                $wmType = $photo["watermark_type"] ?? "text";
                $wmText = $photo["watermark_text"] ?? "";
                $wmScale = (int) ($photo["watermark_scale"] ?? 90);
                $wmOpacity = (int) ($photo["watermark_opacity"] ?? 65);
                $watermarkChanged = false;
                $previewWarning =
                    " La vista previa anterior se conservó porque el archivo original no está disponible.";
            }
        }
        $s = $db->prepare(
            "UPDATE photos SET event_id=?,title=?,bib_number=?,price=?,original_path=?,preview_path=?,file_size=?,watermark_enabled=?,watermark_type=?,watermark_text=?,watermark_scale=?,watermark_opacity=?,download_enabled=?,status=? WHERE id=?",
        );
        $s->execute([
            $event,
            $photo["title"],
            $bib,
            $price,
            $original,
            $preview,
            $size,
            $watermark ? 1 : 0,
            $wmType,
            $wmText,
            $wmScale,
            $wmOpacity,
            $download ? 1 : 0,
            $status,
            $id,
        ]);
        if (!empty($photo["set_id"])) {
            $packOptions = PhotoPack::fromPost();
            $activePacks = array_values(
                array_filter($packOptions, fn($o) => !empty($o["active"])),
            );
            $packEnabled = !empty($activePacks);
            $packQuantity = (int) ($activePacks[0]["quantity"] ?? 5);
            $packPrice = (int) ($activePacks[0]["price"] ?? 14990);
            $available =
                (int) $db
                    ->query(
                        "SELECT COUNT(*) FROM photos WHERE set_id=" .
                            (int) $photo["set_id"],
                    )
                    ->fetchColumn() +
                ($hasAdditional
                    ? count(array_filter((array) ($additional["name"] ?? [])))
                    : 0);
            $counts = [];
            foreach ($activePacks as $option) {
                if (
                    $option["quantity"] > $available ||
                    in_array($option["quantity"], $counts, true)
                ) {
                    $_SESSION["error"] =
                        "Cada pack debe tener una cantidad distinta y no superar las fotografías del set.";
                    redirect("/admin/fotos/editar?id=" . $id);
                }
                $counts[] = (int) $option["quantity"];
            }
            PhotoPack::saveOptions((int) $photo["set_id"], $packOptions);
            if ($featuredReady) {
                $db->prepare(
                    "UPDATE photo_sets SET event_id=?,name=?,bib_number=?,individual_enabled=?,set_enabled=?,featured_home=?,set_price=?,pack_enabled=?,pack_quantity=?,pack_price=?,status=? WHERE id=?",
                )->execute([
                    $event,
                    $setName,
                    $bib,
                    $individual ? 1 : 0,
                    $setEnabled ? 1 : 0,
                    $featuredHome ? 1 : 0,
                    $setPrice,
                    $packEnabled ? 1 : 0,
                    $packQuantity,
                    $packPrice,
                    $setStatus,
                    (int) $photo["set_id"],
                ]);
            } else {
                $db->prepare(
                    "UPDATE photo_sets SET event_id=?,name=?,bib_number=?,individual_enabled=?,set_enabled=?,set_price=?,pack_enabled=?,pack_quantity=?,pack_price=?,status=? WHERE id=?",
                )->execute([
                    $event,
                    $setName,
                    $bib,
                    $individual ? 1 : 0,
                    $setEnabled ? 1 : 0,
                    $setPrice,
                    $packEnabled ? 1 : 0,
                    $packQuantity,
                    $packPrice,
                    $setStatus,
                    (int) $photo["set_id"],
                ]);
            }
            $db->prepare(
                "UPDATE photos SET event_id=?,bib_number=?,price=?,watermark_enabled=?,watermark_type=?,watermark_text=?,watermark_scale=?,watermark_opacity=?,download_enabled=? WHERE set_id=?",
            )->execute([
                $event,
                $bib,
                $price,
                $watermark ? 1 : 0,
                $wmType,
                $wmText,
                $wmScale,
                $wmOpacity,
                $download ? 1 : 0,
                (int) $photo["set_id"],
            ]);
        }
        [$removed, $removeProtected] = !empty($photo["set_id"])
            ? $this->removePhotosFromSet(
                (int) $photo["set_id"],
                $removePhotoIds,
            )
            : [0, []];
        $regenerateFailed = [];
        if (!empty($photo["set_id"]) && $watermarkChanged) {
            $regenerateFailed = $this->regenerateSetPreviews(
                (int) $photo["set_id"],
                $id,
                $watermark,
                $wmText,
                $wmLogo,
                $wmScale,
                $wmOpacity,
            );
        }
        [$added, $addFailed] =
            $hasAdditional && $photo["set_id"]
                ? $this->appendPhotos(
                    $additional,
                    $event,
                    (int) $photo["set_id"],
                    $bib,
                    $price,
                    $watermark,
                    $wmType,
                    $wmText,
                    $wmLogo,
                    $wmScale,
                    $wmOpacity,
                    $download,
                    "active",
                )
                : [0, []];
        if (!empty($photo["set_id"])) {
            $coverCheck = $db->prepare(
                "SELECT id FROM photos WHERE id=? AND set_id=?",
            );
            $coverCheck->execute([$coverPhotoId, (int) $photo["set_id"]]);
            if (!$coverCheck->fetchColumn()) {
                $coverCheck = $db->prepare(
                    "SELECT MIN(id) FROM photos WHERE set_id=?",
                );
                $coverCheck->execute([(int) $photo["set_id"]]);
                $coverPhotoId = (int) $coverCheck->fetchColumn();
            }
            try {
                $db->prepare(
                    "UPDATE photo_sets SET cover_photo_id=? WHERE id=?",
                )->execute([$coverPhotoId ?: null, (int) $photo["set_id"]]);
            } catch (\Throwable $e) {
            }
        }
        $_SESSION["success"] =
            "Fotografía actualizada correctamente." .
            ($added
                ? " " . $added . " fotografía(s) agregada(s) al set."
                : "") .
            ($removed ? " " . $removed . " fotografía(s) eliminada(s)." : "") .
            $previewWarning;
        if ($addFailed || $removeProtected || $regenerateFailed) {
            $_SESSION["error"] = trim(
                ($addFailed
                    ? "No se agregaron: " . implode(", ", $addFailed) . ". "
                    : "") .
                    ($removeProtected
                        ? "No se eliminaron por ventas asociadas o para conservar al menos una foto: " .
                            implode(", ", $removeProtected) .
                            "."
                        : "") .
                    ($regenerateFailed
                        ? " No se regeneraron las vistas previas de: " .
                            implode(", ", $regenerateFailed) .
                            "."
                        : ""),
            );
        }
        redirect("/admin/fotos");
    }
    public function togglePhotoStatus(): never
    {
        verify_csrf();
        $status = ($_POST["status"] ?? "") === "active" ? "active" : "hidden";
        $setId = (int) ($_POST["set_id"] ?? 0);
        if ($setId) {
            Database::db()
                ->prepare("UPDATE photo_sets SET status=? WHERE id=?")
                ->execute([$status, $setId]);
            $_SESSION["success"] =
                $status === "active" ? "Set publicado." : "Set deshabilitado.";
        } else {
            Database::db()
                ->prepare("UPDATE photos SET status=? WHERE id=?")
                ->execute([$status, (int) $_POST["id"]]);
            $_SESSION["success"] =
                $status === "active"
                    ? "Fotografía habilitada."
                    : "Fotografía deshabilitada.";
        }
        redirect("/admin/fotos");
    }
    public function deletePhoto(): never
    {
        verify_csrf();
        $db = Database::db();
        $id = (int) $_POST["id"];
        $sold = $db->prepare(
            "SELECT COUNT(*) FROM order_items WHERE photo_id=?",
        );
        $sold->execute([$id]);
        if ((int) $sold->fetchColumn() > 0) {
            $db->prepare(
                "UPDATE photos SET status='hidden' WHERE id=?",
            )->execute([$id]);
            $_SESSION["success"] =
                "La fotografía posee ventas asociadas y fue deshabilitada para conservar el historial.";
            redirect("/admin/fotos");
        }
        $s = $db->prepare(
            "SELECT original_path,preview_path FROM photos WHERE id=?",
        );
        $s->execute([$id]);
        $photo = $s->fetch();
        if ($photo) {
            foreach (["original_path", "preview_path"] as $key) {
                $candidate = ROOT . "/" . ltrim($photo[$key], "/");
                $storage = realpath(ROOT . "/storage");
                $parent = realpath(dirname($candidate));
                if (
                    $storage &&
                    $parent &&
                    str_starts_with($parent, $storage) &&
                    is_file($candidate)
                ) {
                    unlink($candidate);
                }
            }
            $db->prepare("DELETE FROM photos WHERE id=?")->execute([$id]);
            $_SESSION["success"] = "Fotografía eliminada definitivamente.";
        }
        redirect("/admin/fotos");
    }
    private function removePhotosFromSet(int $setId, array $ids): array
    {
        if (!$ids) {
            return [0, []];
        }
        $db = Database::db();
        $marks = implode(",", array_fill(0, count($ids), "?"));
        $s = $db->prepare(
            "SELECT p.*,EXISTS(SELECT 1 FROM order_items oi WHERE oi.photo_id=p.id OR (oi.set_id=p.set_id AND oi.item_type IN ('set','pack'))) has_sales FROM photos p WHERE p.set_id=? AND p.id IN ($marks)",
        );
        $s->execute(array_merge([$setId], $ids));
        $rows = $s->fetchAll();
        $total = (int) $db
            ->query("SELECT COUNT(*) FROM photos WHERE set_id=" . $setId)
            ->fetchColumn();
        $deletable = [];
        $protected = [];
        foreach ($rows as $row) {
            if ($row["has_sales"]) {
                $protected[] = $row["title"];
            } else {
                $deletable[] = $row;
            }
        }
        while ($deletable && $total - count($deletable) < 1) {
            $keep = array_pop($deletable);
            $protected[] = $keep["title"];
        }
        foreach ($deletable as $row) {
            $db->prepare("DELETE FROM photos WHERE id=? AND set_id=?")->execute(
                [$row["id"], $setId],
            );
            foreach (["original_path", "preview_path"] as $key) {
                $candidate = ROOT . "/" . ltrim($row[$key], "/");
                $storage = realpath(ROOT . "/storage");
                $parent = realpath(dirname($candidate));
                if (
                    $storage &&
                    $parent &&
                    str_starts_with($parent, $storage) &&
                    is_file($candidate)
                ) {
                    @unlink($candidate);
                }
            }
        }
        return [count($deletable), $protected];
    }
    private function appendPhotos(
        array $files,
        int $event,
        int $setId,
        string $bib,
        int $price,
        bool $watermark,
        string $wmType,
        string $wmText,
        ?string $wmLogo,
        int $wmScale,
        int $wmOpacity,
        bool $download,
        string $status,
    ): array {
        $added = 0;
        $failed = [];
        foreach ((array) ($files["tmp_name"] ?? []) as $k => $tmp) {
            $name = $files["name"][$k] ?? "archivo";
            if (
                ($files["error"][$k] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
                !is_uploaded_file($tmp) ||
                !@getimagesize($tmp)
            ) {
                $failed[] = $name . " (imagen inválida)";
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ["jpg", "jpeg", "png", "webp"], true)) {
                $failed[] = $name . " (formato no permitido)";
                continue;
            }
            $token = bin2hex(random_bytes(10));
            $orig = "storage/originals/" . $token . "." . $ext;
            $preview = "storage/previews/" . $token . ".jpg";
            if (
                !@move_uploaded_file($tmp, ROOT . "/" . $orig) ||
                !$this->preview(
                    ROOT . "/" . $orig,
                    ROOT . "/" . $preview,
                    $watermark,
                    $wmText,
                    $wmLogo,
                    $wmScale,
                    $wmOpacity,
                )
            ) {
                @unlink(ROOT . "/" . $orig);
                $failed[] = $name . " (no se pudo procesar)";
                continue;
            }
            $s = Database::db()->prepare(
                "INSERT INTO photos(event_id,set_id,title,bib_number,price,original_path,preview_path,file_size,watermark_enabled,watermark_type,watermark_text,watermark_scale,watermark_opacity,download_enabled,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            );
            $s->execute([
                $event,
                $setId,
                pathinfo($name, PATHINFO_FILENAME),
                $bib,
                $price,
                $orig,
                $preview,
                (int) @filesize(ROOT . "/" . $orig),
                $watermark ? 1 : 0,
                $wmType,
                $wmText,
                $wmScale,
                $wmOpacity,
                $download ? 1 : 0,
                $status,
            ]);
            $added++;
        }
        return [$added, $failed];
    }
    private function preview(
        string $src,
        string $dest,
        bool $wm,
        string $text = "ULTRA MEDIA DIGITAL",
        ?string $logoPath = null,
        int $logoSize = 90,
        int $logoOpacity = 65,
    ): bool {
        $info = @getimagesize($src);
        if (!$info || !function_exists("imagecreatetruecolor")) {
            return false;
        }
        $create = match ($info[2]) {
            IMAGETYPE_JPEG => "imagecreatefromjpeg",
            IMAGETYPE_PNG => "imagecreatefrompng",
            IMAGETYPE_WEBP => "imagecreatefromwebp",
            default => null,
        };
        if (!$create || !function_exists($create)) {
            return false;
        }
        $im = @$create($src);
        if (!$im) {
            return false;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        if ($w < 1 || $h < 1) {
            imagedestroy($im);
            return false;
        }
        $nw = min($w, 1600);
        $nh = max(1, (int) round(($h * $nw) / $w));
        $out = imagecreatetruecolor($nw, $nh);
        $bg = imagecolorallocate($out, 255, 255, 255);
        imagefill($out, 0, 0, $bg);
        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        if ($wm && $logoPath) {
            $li = @getimagesize($logoPath);
            $loader = $li
                ? match ($li[2]) {
                    IMAGETYPE_JPEG => "imagecreatefromjpeg",
                    IMAGETYPE_PNG => "imagecreatefrompng",
                    IMAGETYPE_WEBP => "imagecreatefromwebp",
                    default => null,
                }
                : null;
            $logo =
                $loader && function_exists($loader)
                    ? @$loader($logoPath)
                    : false;
            if ($logo) {
                $lw = imagesx($logo);
                $lh = imagesy($logo);
                $logoSize = max(20, min(100, $logoSize));
                $logoOpacity = max(10, min(100, $logoOpacity));
                $maxLogoW = max(1, (int) round($nw * ($logoSize / 100)));
                $maxLogoH = max(1, (int) round($nh * ($logoSize / 100)));
                $logoScale = min($maxLogoW / $lw, $maxLogoH / $lh);
                $targetW = max(1, (int) round($lw * $logoScale));
                $targetH = max(1, (int) round($lh * $logoScale));
                $scaled = imagecreatetruecolor($targetW, $targetH);
                imagesavealpha($scaled, true);
                imagefill(
                    $scaled,
                    0,
                    0,
                    imagecolorallocatealpha($scaled, 0, 0, 0, 127),
                );
                imagecopyresampled(
                    $scaled,
                    $logo,
                    0,
                    0,
                    0,
                    0,
                    $targetW,
                    $targetH,
                    $lw,
                    $lh,
                );
                $this->applyLogoOpacity($scaled, $logoOpacity);
                imagecopy(
                    $out,
                    $scaled,
                    (int) (($nw - $targetW) / 2),
                    (int) (($nh - $targetH) / 2),
                    0,
                    0,
                    $targetW,
                    $targetH,
                );
                imagedestroy($scaled);
                imagedestroy($logo);
            }
        } elseif ($wm) {
            $label = $text !== "" ? $text : "ULTRA MEDIA DIGITAL";
            $white = imagecolorallocatealpha($out, 255, 255, 255, 55);
            for ($y = 100; $y < $nh; $y += 220) {
                for ($x = 20; $x < $nw; $x += 360) {
                    imagestring($out, 5, $x, $y, $label, $white);
                };
            }
        }
        $saved = @imagejpeg($out, $dest, 78);
        imagedestroy($im);
        imagedestroy($out);
        return $saved && is_file($dest);
    }

    private function regenerateSetPreviews(
        int $setId,
        int $exceptId,
        bool $watermark,
        string $wmText,
        ?string $wmLogo,
        int $wmScale,
        int $wmOpacity,
    ): array {
        $statement = Database::db()->prepare(
            "SELECT id,title,original_path,preview_path FROM photos WHERE set_id=? AND id<>? ORDER BY id",
        );
        $statement->execute([$setId, $exceptId]);
        $failed = [];
        foreach ($statement->fetchAll() as $photo) {
            if (
                !$this->preview(
                    ROOT . "/" . ltrim($photo["original_path"], "/"),
                    ROOT . "/" . ltrim($photo["preview_path"], "/"),
                    $watermark,
                    $wmText,
                    $wmLogo,
                    $wmScale,
                    $wmOpacity,
                )
            ) {
                $failed[] = $photo["title"] ?: "Foto #" . $photo["id"];
            }
        }
        return $failed;
    }

    private function applyLogoOpacity($image, int $opacity): void
    {
        if ($opacity >= 100) {
            return;
        }
        $factor = max(0.1, min(1, $opacity / 100));
        $width = imagesx($image);
        $height = imagesy($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7f;
                if ($alpha === 127) {
                    continue;
                }
                $adjusted = 127 - (int) round((127 - $alpha) * $factor);
                imagesetpixel(
                    $image,
                    $x,
                    $y,
                    ($rgba & 0x00ffffff) | ($adjusted << 24),
                );
            }
        }
    }

    private function rangeValue(
        mixed $value,
        int $minimum,
        int $maximum,
        int $default,
    ): int {
        if (!is_numeric($value)) {
            return $default;
        }
        return max($minimum, min($maximum, (int) $value));
    }

    private function ensureWatermarkControls(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }
        $db = Database::db();
        $columns = [
            "watermark_scale" =>
                "ALTER TABLE photos ADD COLUMN watermark_scale TINYINT UNSIGNED NOT NULL DEFAULT 90 AFTER watermark_text",
            "watermark_opacity" =>
                "ALTER TABLE photos ADD COLUMN watermark_opacity TINYINT UNSIGNED NOT NULL DEFAULT 65 AFTER watermark_scale",
        ];
        foreach ($columns as $column => $sql) {
            $check = $db->prepare(
                "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='photos' AND COLUMN_NAME=?",
            );
            $check->execute([$column]);
            if (!(int) $check->fetchColumn()) {
                $db->exec($sql);
            }
        }
        $ready = true;
    }
}
