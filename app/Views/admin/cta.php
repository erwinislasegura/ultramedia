<?php
$storedImage = trim((string)($cta['image_url'] ?? ''));
$previewImage = $storedImage !== ''
    ? media($storedImage)
    : 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1200&q=85';
?>
<div class="content cta-admin-content">
    <section class="title">
        <div>
            <span class="eyebrow">PORTADA</span>
            <h1>CTA DE <em>EVENTO.</em></h1>
            <p>Configura el llamado destacado que aparecerá en la página principal.</p>
        </div>
    </section>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="cta-admin-grid">
        <form class="panel cta-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= csrf() ?>">

            <label class="switch-row">
                <span>
                    <b>MOSTRAR CTA EN PORTADA</b>
                    <small>Permite activar o desactivar la sección.</small>
                </span>
                <input type="checkbox" name="active" <?= !empty($cta['active']) ? 'checked' : '' ?>>
            </label>

            <label>EVENTO RELACIONADO
                <select name="event_id">
                    <option value="">Sin evento</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?= (int)$event['id'] ?>" <?= ($cta['event_id'] ?? '') == $event['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($event['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>ETIQUETA
                <input name="eyebrow" value="<?= htmlspecialchars($cta['eyebrow'] ?? 'EVENTO DESTACADO') ?>">
            </label>
            <label>TÍTULO
                <input name="title" value="<?= htmlspecialchars($cta['title'] ?? 'TRAIL VOLCÁN ANTUCO 2026') ?>">
            </label>
            <label>DESCRIPCIÓN
                <textarea name="description"><?= htmlspecialchars($cta['description'] ?? 'Encuentra todas las fotografías oficiales del evento.') ?></textarea>
            </label>

            <div class="two-fields">
                <label>TEXTO DEL BOTÓN
                    <input name="button_text" value="<?= htmlspecialchars($cta['button_text'] ?? 'VER FOTOGRAFÍAS') ?>">
                </label>
                <label>ENLACE DEL BOTÓN
                    <input name="button_url" value="<?= htmlspecialchars($cta['button_url'] ?? '#fotos') ?>">
                </label>
            </div>

            <label class="cta-image-field" for="ctaImageInput">
                <span>IMAGEN DE FONDO</span>
                <input id="ctaImageInput" name="cta_image" type="file" accept="image/jpeg,image/png,image/webp">
                <small id="ctaImageName">
                    <?= $storedImage !== '' ? 'Imagen actual cargada. Selecciona otra para reemplazarla.' : 'Selecciona una imagen desde tu dispositivo.' ?>
                </small>
                <em>JPG, PNG o WebP · máximo 10 MB · recomendado 1800 × 900 px</em>
            </label>

            <button type="submit">GUARDAR CTA →</button>
        </form>

        <aside
            class="cta-preview"
            id="ctaPreview"
            style="background-image:linear-gradient(90deg,#080b0ce8,#080b0c55),url('<?= htmlspecialchars($previewImage) ?>')"
        >
            <small class="cta-preview-label">VISTA PREVIA</small>
            <span><?= htmlspecialchars($cta['eyebrow'] ?? 'EVENTO DESTACADO') ?></span>
            <h2><?= htmlspecialchars($cta['title'] ?? 'TRAIL VOLCÁN ANTUCO 2026') ?></h2>
            <p><?= htmlspecialchars($cta['description'] ?? 'Encuentra todas las fotografías oficiales del evento.') ?></p>
            <b><?= htmlspecialchars($cta['button_text'] ?? 'VER FOTOGRAFÍAS') ?> →</b>
        </aside>
    </div>
</div>
