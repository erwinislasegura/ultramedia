<?php
$selectedCover = $selected['cover_path'] ?? '';
if (!$selectedCover && $selectedSetIds) {
    foreach ($sets as $candidate) {
        if (in_array((int)$candidate['id'], $selectedSetIds, true) && !empty($candidate['cover_id'])) {
            $selectedCover = preview_url(['id' => $candidate['cover_id'], 'preview_path' => $candidate['preview_path']]);
            break;
        }
    }
} elseif ($selectedCover) {
    $selectedCover = media($selectedCover);
}
?>
<div class="content events-content">
 <section class="title">
  <div>
   <span class="eyebrow">CATÁLOGO</span>
   <h1>MÓDULO DE <em>EVENTOS.</em></h1>
   <p>Configura la portada y selecciona los sets que componen cada evento público.</p>
  </div>
  <a class="btn btn-primary" href="<?=url('admin/eventos')?>">+ NUEVO EVENTO</a>
 </section>

 <?php if(!empty($_SESSION['success'])):?><div class="flash success"><?=htmlspecialchars($_SESSION['success']);unset($_SESSION['success']);?></div><?php endif;?>
 <?php if(!empty($_SESSION['error'])):?><div class="flash error"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div><?php endif;?>

 <div class="events-admin-grid">
  <form class="panel event-form" method="post" action="<?=url('admin/eventos/guardar')?>" enctype="multipart/form-data">
   <input type="hidden" name="_token" value="<?=csrf()?>">
   <input type="hidden" name="id" value="<?=$selected['id']??''?>">

   <div class="panel-head event-editor-head">
    <div><span class="eyebrow"><?=$selected?'EDITAR EVENTO':'NUEVO EVENTO'?></span><h2><?=$selected?'ACTUALIZAR CONTENIDO':'CREAR EVENTO'?></h2></div>
    <?php if($selected&&$selected['status']==='published'):?><a href="<?=url('evento?slug='.$selected['slug'])?>" target="_blank">VER PÚBLICO ↗</a><?php endif;?>
   </div>

   <section class="event-cover-editor">
    <div class="event-cover-preview <?=$selectedCover?'has-image':''?>" id="eventCoverPreview" <?=$selectedCover?'style="background-image:url(\''.htmlspecialchars($selectedCover).'\')"':''?>>
     <span id="eventCoverEmpty">PORTADA DEL EVENTO</span>
    </div>
    <div>
     <b>IMAGEN DE PORTADA</b>
     <p>Recomendado: 1600 × 900 px, formato JPG, PNG o WEBP. Máximo 12 MB.</p>
     <label class="event-file-button">SELECCIONAR IMAGEN<input id="eventCoverInput" name="cover" type="file" accept="image/jpeg,image/png,image/webp"></label>
     <?php if(!empty($selected['cover_path'])):?><label class="event-remove-cover"><input type="checkbox" name="remove_cover" value="1"> Quitar portada actual</label><?php endif;?>
    </div>
   </section>

   <div class="event-fields">
    <label>NOMBRE DEL EVENTO<input name="name" required value="<?=htmlspecialchars($selected['name']??'')?>" placeholder="Ej. Trail Volcán Antuco"></label>
    <div class="two-fields">
     <label>DISCIPLINA<input name="sport" required value="<?=htmlspecialchars($selected['sport']??'')?>" placeholder="Running, fútbol, ciclismo..."></label>
     <label>FECHA<input name="event_date" type="date" required value="<?=htmlspecialchars($selected['event_date']??date('Y-m-d'))?>"></label>
    </div>
    <label>UBICACIÓN<input name="location" value="<?=htmlspecialchars($selected['location']??'')?>" placeholder="Ciudad o recinto"></label>
    <label>DESCRIPCIÓN PÚBLICA<textarea name="description" maxlength="700" placeholder="Describe brevemente el evento y su colección fotográfica."><?=htmlspecialchars($selected['description']??'')?></textarea></label>
    <label>ESTADO<select name="status"><option value="draft" <?=($selected['status']??'')==='draft'?'selected':''?>>Borrador · No visible</option><option value="published" <?=($selected['status']??'published')==='published'?'selected':''?>>Publicado · Visible en la web</option><option value="archived" <?=($selected['status']??'')==='archived'?'selected':''?>>Archivado</option></select></label>
   </div>

   <section class="event-set-picker">
    <div class="event-set-picker-head">
     <div><span class="eyebrow">COMPOSICIÓN</span><h3>SETS DEL EVENTO</h3><p>Marca los sets que se mostrarán dentro de este evento.</p></div>
     <b><span id="eventSelectedCount"><?=count($selectedSetIds)?></span> SELECCIONADOS</b>
    </div>
    <div class="event-set-search"><span>⌕</span><input id="eventSetSearch" type="search" placeholder="Buscar por nombre, dorsal o evento de origen"></div>
    <div class="event-set-options" id="eventSetOptions">
     <?php foreach($sets as $set):?>
     <label data-set-search="<?=htmlspecialchars(strtolower($set['name'].' '.$set['bib_number'].' '.$set['source_event_name']))?>">
      <input type="checkbox" name="set_ids[]" value="<?=$set['id']?>" <?=in_array((int)$set['id'],$selectedSetIds,true)?'checked':''?>>
      <span class="event-set-image"><?php if($set['cover_id']):?><img src="<?=preview_url(['id'=>$set['cover_id'],'preview_path'=>$set['preview_path']])?>" alt=""><?php else:?><i>SIN FOTO</i><?php endif;?></span>
      <span class="event-set-copy"><small><?=htmlspecialchars($set['source_event_name'])?> · <?=$set['photos_count']?> FOTO(S)</small><strong><?=htmlspecialchars($set['name'])?></strong><em><?=$set['bib_number']!==null&&$set['bib_number']!==''?'DORSAL #'.htmlspecialchars($set['bib_number']):'SIN DORSAL'?></em></span>
      <span class="event-set-state <?=$set['status']?>"><?=$set['status']==='active'?'PUBLICADO':'OCULTO'?></span>
      <i class="event-set-check">✓</i>
     </label>
     <?php endforeach;?>
     <?php if(!$sets):?><p class="event-no-sets">Todavía no hay sets. Créelos primero desde “Subir fotografías”.</p><?php endif;?>
    </div>
   </section>

   <div class="event-form-actions">
    <?php if($selected):?><a class="btn btn-secondary" href="<?=url('admin/eventos')?>">CANCELAR</a><?php endif;?>
    <button class="btn btn-primary">GUARDAR EVENTO →</button>
   </div>
  </form>

  <section class="panel events-list">
   <div class="panel-head"><div><span class="eyebrow">REGISTROS</span><h2>EVENTOS CREADOS</h2></div><span class="result-count"><?=count($events)?> EVENTOS</span></div>
   <div class="event-admin-cards">
    <?php foreach($events as $event):?>
    <?php $eventCover=!empty($event['cover_path'])?media($event['cover_path']):(!empty($event['cover_id'])?preview_url(['id'=>$event['cover_id'],'preview_path'=>$event['preview_path']]):'');?>
    <article>
     <div class="event-cover <?=$eventCover?'has-image':''?>" <?=$eventCover?'style="background-image:url(\''.htmlspecialchars($eventCover).'\')"':''?>><?php if(!$eventCover):?><span>SIN PORTADA</span><?php endif;?><em class="event-status <?=$event['status']?>"><?=strtoupper($event['status'])?></em></div>
     <div class="event-info"><small><?=htmlspecialchars(strtoupper($event['sport']))?> · <?=date('d/m/Y',strtotime($event['event_date']))?></small><h3><?=htmlspecialchars($event['name'])?></h3><p><?=htmlspecialchars($event['location']??'Sin ubicación')?></p><div><b><?=$event['sets_count']?> SETS</b><b><?=$event['photos_count']?> FOTOS</b></div></div>
     <div class="event-actions">
      <a class="btn btn-edit" href="<?=url('admin/eventos?id='.$event['id'])?>">EDITAR</a>
      <?php if($event['status']==='published'):?><a class="btn btn-secondary" href="<?=url('evento?slug='.$event['slug'])?>" target="_blank">VER ↗</a><?php endif;?>
      <form method="post" action="<?=url('admin/eventos/estado')?>"><input type="hidden" name="_token" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$event['id']?>"><input type="hidden" name="status" value="<?=$event['status']==='published'?'draft':'published'?>"><button class="btn btn-toggle"><?=$event['status']==='published'?'OCULTAR':'PUBLICAR'?></button></form>
      <form method="post" action="<?=url('admin/eventos/eliminar')?>" onsubmit="return confirm('¿Eliminar o archivar este evento?')"><input type="hidden" name="_token" value="<?=csrf()?>"><input type="hidden" name="id" value="<?=$event['id']?>"><button class="btn btn-danger">ELIMINAR</button></form>
     </div>
    </article>
    <?php endforeach;?>
    <?php if(!$events):?><p class="event-no-sets">No hay eventos creados.</p><?php endif;?>
   </div>
  </section>
 </div>
</div>
