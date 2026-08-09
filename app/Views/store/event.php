<?php
$eventCover=!empty($event['cover_path'])?media($event['cover_path']):preview_url(['id'=>$sets[0]['cover_id'],'preview_path'=>$sets[0]['preview_path']]);
$photoCount=array_sum(array_map(static fn($set)=>(int)$set['photos_count'],$sets));
?>
<section class="event-detail-hero" style="background-image:linear-gradient(90deg,rgba(6,9,10,.94),rgba(6,9,10,.62) 52%,rgba(6,9,10,.2)),url('<?=htmlspecialchars($eventCover)?>')">
 <div class="event-detail-copy">
  <a href="<?=url('eventos')?>">← TODOS LOS EVENTOS</a>
  <span><?=htmlspecialchars(strtoupper($event['sport']))?> · <?=date('d M Y',strtotime($event['event_date']))?></span>
  <h1><?=htmlspecialchars($event['name'])?></h1>
  <p><?=htmlspecialchars($event['description']?:'Encuentra y compra las fotografías oficiales de este evento.')?></p>
  <div><b><?=$sets?count($sets):0?> SETS</b><b><?=$photoCount?> FOTOGRAFÍAS</b><?php if(!empty($event['location'])):?><b><?=htmlspecialchars(strtoupper($event['location']))?></b><?php endif;?></div>
  <a class="event-hero-action" href="#sets-evento">VER SETS DISPONIBLES ↓</a>
 </div>
</section>

<section class="event-sets-section section" id="sets-evento">
 <div class="event-sets-head"><div><span class="eyebrow dark">COLECCIÓN DEL EVENTO</span><h2>ELIGE TU <em>SET.</em></h2><p>Cada set puede incluir compra individual, packs por cantidad o descarga completa.</p></div><b><?=count($sets)?> SETS PUBLICADOS</b></div>
 <div class="event-public-set-grid">
  <?php foreach($sets as $set):?>
  <article>
   <a class="event-set-cover" href="<?=url('foto?id='.$set['cover_id'])?>">
    <div class="product-slider" data-product-slider><?php foreach(($setPreviews[$set['id']]??[]) as $index=>$slide):?><img class="<?=$index===0?'active':''?>" src="<?=preview_url($slide)?>" alt="<?=htmlspecialchars($set['name'])?>"><?php endforeach;?></div>
    <span>ULTRA</span><b><?=$set['photos_count']?> FOTOS</b>
   </a>
   <div class="event-set-details">
    <small><?=$set['bib_number']!==null&&$set['bib_number']!==''?'COMPETIDOR #'.htmlspecialchars($set['bib_number']):htmlspecialchars(strtoupper($event['sport']))?></small>
    <h3><?=htmlspecialchars($set['name'])?></h3>
    <div class="event-sale-modes"><?php if($set['individual_enabled']):?><span>INDIVIDUAL</span><?php endif;?><?php if($set['pack_enabled']):?><span>PACKS</span><?php endif;?><?php if($set['set_enabled']):?><span>SET COMPLETO</span><?php endif;?></div>
    <div class="event-set-price"><span>DESDE <b><?=money((int)($set['individual_enabled']?$set['individual_price']:$set['set_price']))?></b></span><a href="<?=url('foto?id='.$set['cover_id'])?>">VER FOTOGRAFÍAS →</a></div>
   </div>
  </article>
  <?php endforeach;?>
 </div>
</section>
