<section class="events-page-head">
 <div><span>COLECCIONES FOTOGRÁFICAS</span><h1>EVENTOS <em>PUBLICADOS.</em></h1><p>Explora cada evento y encuentra los sets fotográficos disponibles para compra individual, por pack o como colección completa.</p></div>
</section>

<section class="public-events section">
 <div class="public-events-head"><div><span class="eyebrow dark">TODOS LOS EVENTOS</span><h2>ENCUENTRA TU <em>COLECCIÓN.</em></h2></div><b><?=count($events)?> EVENTOS</b></div>
 <div class="public-event-grid">
  <?php foreach($events as $event):?>
  <?php $cover=!empty($event['cover_path'])?media($event['cover_path']):preview_url(['id'=>$event['cover_id'],'preview_path'=>$event['preview_path']]);?>
  <a class="public-event-card" href="<?=url('evento?slug='.$event['slug'])?>">
   <div class="public-event-cover" style="background-image:url('<?=htmlspecialchars($cover)?>')"><span><?=htmlspecialchars(strtoupper($event['sport']))?></span><i>VER EVENTO ↗</i></div>
   <div class="public-event-copy"><small><?=date('d M Y',strtotime($event['event_date']))?> · <?=htmlspecialchars($event['location']??'')?></small><h2><?=htmlspecialchars($event['name'])?></h2><p><?=htmlspecialchars($event['description']?:'Revisa todos los sets fotográficos disponibles de este evento.')?></p><div><b><?=$event['sets_count']?> SETS</b><b><?=$event['photos_count']?> FOTOS</b><strong>EXPLORAR →</strong></div></div>
  </a>
  <?php endforeach;?>
 </div>
 <?php if(!$events):?><div class="public-events-empty"><h2>PRÓXIMAMENTE</h2><p>Todavía no hay eventos publicados con sets disponibles.</p></div><?php endif;?>
</section>
