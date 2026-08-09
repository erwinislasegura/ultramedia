<?php require ROOT.'/app/Views/partials/home_hero.php'; ?>

<?php if ($events): ?>
<section class="section" id="eventos">
    <div class="section-title">
        <div>
            <span class="eyebrow dark">COLECCIONES RECIENTES</span>
            <h2>ÚLTIMOS <em>EVENTOS</em></h2>
        </div>
        <a href="<?= url('eventos') ?>">VER TODOS →</a>
    </div>
    <div class="event-grid">
        <?php foreach ($events as $event): ?>
            <?php $eventCover = !empty($event['cover_path']) ? media($event['cover_path']) : preview_url(['id' => $event['cover_id'], 'preview_path' => $event['preview_path']]); ?>
            <article>
                <a href="<?= url('evento?slug='.$event['slug']) ?>">
                    <img src="<?= $eventCover ?>" alt="<?= htmlspecialchars($event['name']) ?>">
                    <span><?= htmlspecialchars(strtoupper($event['sport'])) ?></span>
                    <div>
                        <small><?= date('d M Y', strtotime($event['event_date'])) ?></small>
                        <h3><?= htmlspecialchars($event['name']) ?></h3>
                        <b><?= (int)$event['photos_count'] ?> FOTOS · <?= (int)$event['sets_count'] ?> SETS <i>VER EVENTO →</i></b>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require ROOT.'/app/Views/partials/home_cta.php'; ?>

<?php if ($sets): ?>
<section class="featured-sets section">
    <div class="section-title">
        <div>
            <span class="eyebrow dark">AHORRA CON EL PACK</span>
            <h2>SETS <em>COMPLETOS.</em></h2>
            <p>Todas las fotografías del lote en una sola compra.</p>
        </div>
    </div>
    <div class="set-grid">
        <?php foreach ($sets as $set): ?>
            <article class="featured-set-card">
                <div class="product-slider featured-set-media" data-product-slider>
                    <?php foreach (($setPreviews[$set['id']] ?? []) as $index => $slide): ?>
                        <img class="<?= $index === 0 ? 'active' : '' ?>" src="<?= preview_url($slide) ?>" alt="<?= htmlspecialchars($set['name']) ?>">
                    <?php endforeach; ?>
                </div>
                <div class="set-card-copy">
                    <small><?= htmlspecialchars($set['event_name']) ?></small>
                    <h3><?= htmlspecialchars($set['name']) ?></h3>
                    <p><?= (int)$set['photos_count'] ?> fotografías</p>
                    <strong><?= money((int)$set['set_price']) ?></strong>
                    <form method="post" action="<?= url('carrito/agregar') ?>">
                        <input type="hidden" name="_token" value="<?= csrf() ?>">
                        <input type="hidden" name="type" value="set">
                        <input type="hidden" name="id" value="<?= (int)$set['id'] ?>">
                        <button>COMPRAR SET →</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="shop" id="fotos">
    <div class="section">
        <div class="section-title light">
            <div>
                <span class="eyebrow">SETS PUBLICADOS</span>
                <h2>TUS FOTOS, <em>LISTAS.</em></h2>
                <p>Abre tu set para revisar las fotografías y elegir compra individual, pack o set completo.</p>
            </div>
            <form>
                <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Buscar Nº, set o evento">
            </form>
        </div>

        <div class="product-grid">
            <?php foreach ($catalogSets as $set): ?>
                <?php $bibNumber = trim((string)($set['bib_number'] ?? '')); ?>
                <article class="product home-product-card">
                    <a href="<?= url('foto?id='.$set['cover_id']) ?>" class="photo home-product-media">
                        <div class="product-slider" data-product-slider>
                            <?php foreach (($setPreviews[$set['id']] ?? []) as $index => $slide): ?>
                                <img class="<?= $index === 0 ? 'active' : '' ?>" src="<?= preview_url($slide) ?>" alt="<?= htmlspecialchars($set['name']) ?>">
                            <?php endforeach; ?>
                        </div>
                        <span class="wm" aria-hidden="true">ULTRA</span>
                        <?php if ($bibNumber !== ''): ?><b class="product-bib">#<?= htmlspecialchars($bibNumber) ?></b><?php endif; ?>
                        <em class="set-badge"><?= (int)$set['photos_count'] ?> FOTOS</em>
                    </a>
                    <div class="product-card-info">
                        <span class="product-card-copy">
                            <small><?= htmlspecialchars($set['event_name']) ?></small>
                            <strong title="<?= htmlspecialchars($set['name']) ?>"><?= htmlspecialchars($set['name']) ?></strong>
                        </span>
                        <a class="product-card-action" href="<?= url('foto?id='.$set['cover_id']) ?>">VER FOTOS <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!$catalogSets): ?>
            <p class="empty-catalog">No hay sets publicados que coincidan con tu búsqueda.</p>
        <?php endif; ?>
    </div>
</section>

<?php require ROOT.'/app/Views/partials/home_process.php'; ?>
